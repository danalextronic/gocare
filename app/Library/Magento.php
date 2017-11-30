<?php

namespace App\Library;

use App\Order;
use App\User;
use Carbon\Carbon;
use DB;

class Magento
{
    protected $_magentoApiFunctions;

    private $_pdo;

    private $_magentoApiClient;

    private $_magentoApiSession;

    public function __construct()
    {
        try {
            $this->_pdo = DB::connection('magento')->getPdo();
        } catch (\PDOException $e) {
            throw new \DomainException(json_encode($e->getMessage()));
        }

        $apiHostUrl = env('MAGENTO_API_HOST');
        if(!$apiHostUrl){
            throw new \DomainException('Magento host missing');
        }
        $this->_magentoApiClient = new \SoapClient($apiHostUrl);
        $this->_magentoApiSession = $this->_magentoApiClient->login(env('MAGENTO_API_USERNAME'),
                                                                    env('MAGENTO_API_KEY'));
    }

    /**
     * @return array
     */
    public function listAvailableFunctions()
    {
        if ($this->_magentoApiFunctions === null) {
            $this->_magentoApiFunctions = $this->_magentoApiClient->__getFunctions();
        }

        return $this->_magentoApiFunctions;
    }

    /********************************************* ORDERS SECTION *****************************************************/

    /**
     * Takes an order object and returns true if everything goes well, false if something bad happens.
     * // todo: needs refactoring! 2016-01-16
     *
     * @param Order $order
     * @return bool
     */
    public function createOrder($order)
    {
        $user = User::find($order->user_id);

        if (!$this->_validateOrder($order)) {
            return false;
        }

        // check to make sure the customer exists using the email
        $customer_entity = $this->_customerExistsByEmail($order->email);

        if (!$customer_entity) {

            if (!$this->_attemptCreateCustomer($order, $user)) {
                return false;
            }

            $customer_entity = $this->_customerExistsByEmail($order->email);
        }

        if ($customer_entity) {
            // create a device
            $customer_id = $customer_entity->entity_id;

            // look up the product by sku, if it exists move forward
            $product_entity = $this->_productExistsBySku($this->_cleanSku($order->warranty_sku));

            if ($product_entity) {

                // todo: need to come up with a way to handle fails inside of this conditional block too

                $model_id = $this->_getModelIdByEntityId($product_entity->entity_id);
                $plan_type = "plan"; // todo: <-- this might need to be dynamic in the future, for now hard-coded "plan"
                $activation_date = $order->start_date;
                $theft_deductible = $this->_getTheftDeductibleByEntityId($product_entity->entity_id);
                $adew_deductible = $this->_getAdewDeductibleByEntityId($product_entity->entity_id);
                $term_length = $this->_getTermLengthByEntityId($product_entity->entity_id);
                $expiration_date = $this->_calculateExpirationDate($order->start_date, $term_length);
                $dr_deductible = $this->_getDrDeductibleByEntityId($product_entity->entity_id);
                $premium = $this->_getPremiumByEntityId($product_entity->entity_id);
                $insurance_deductible = $this->_getInsuranceDeductibleByEntityId($product_entity->entity_id);
                $serial_id = null;

                // see if the serial number is already in the system it's possible this is a duplicate
                // check
                $sql = 'SELECT * FROM gocare_device_serial_number gdsn
                        JOIN gocare_device_table gdt ON gdsn.serial_id = gdt.serial_id
                        WHERE gdsn.serial_number = :serial_number
                        AND gdt.activation_date = :activation_date
                        AND gdt.customer_id = :customer_id';

                $stmt = $this->_pdo->prepare($sql);
                $stmt->bindValue(':serial_number', $order->serial_number, \PDO::PARAM_STR);
                $stmt->bindValue(':activation_date', $activation_date, \PDO::PARAM_STR);
                $stmt->bindValue(':customer_id', $customer_id, \PDO::PARAM_STR);
                $stmt->execute();
                if ($stmt->rowCount() > 0) {
                    $this->_updateFailedReason($order, 'Possible duplicate entry.', 'possible_duplicate_entry');
                    return false;
                }

                // create the device
                $sql = "INSERT INTO gocare_device_table
                    (customer_id, product_id, model_id, plan_type, activation_date, expiration_date, adew_deductible, theft_deductible, dr_deductible, insurance_deductible, premium, term, registration_id, registration_status, status, device_nickname) VALUES (:customer_id, :product_id, :model_id, :plan_type, :activation_date, :expiration_date, :adew_deductible, :theft_deductible, :dr_deductible, :insurance_deductible, :premium, :term, 0, 1, 1, '')";
                $stmt = $this->_pdo->prepare($sql);
                $stmt->bindValue(':customer_id', $customer_id, \PDO::PARAM_INT);
                $stmt->bindValue(':product_id', $product_entity->entity_id, \PDO::PARAM_INT);
                $stmt->bindValue(':model_id', $model_id, \PDO::PARAM_STR);
                $stmt->bindValue(':plan_type', $plan_type, \PDO::PARAM_STR);
                $stmt->bindValue(':activation_date', $activation_date);
                $stmt->bindValue(':expiration_date', $expiration_date);
                $stmt->bindValue(':adew_deductible', $adew_deductible);
                $stmt->bindValue(':theft_deductible', $theft_deductible);
                $stmt->bindValue(':dr_deductible', $dr_deductible);
                $stmt->bindValue(':insurance_deductible', $insurance_deductible);
                $stmt->bindValue(':premium', $premium);
                $stmt->bindValue(':term', $term_length);
                $stmt->execute();
                $device_id = $this->_pdo->lastInsertId();

                $sql = "INSERT INTO gocare_device_serial_number (serial_number, device_id, is_active, admin_id, notes) VALUES (:serial_number, :device_id, 1, 0, '')";
                $stmt = $this->_pdo->prepare($sql);
                $stmt->bindValue(':serial_number', $order->serial_number);
                $stmt->bindValue('device_id', $device_id, \PDO::PARAM_INT);
                $stmt->execute();
                $serial_id = $this->_pdo->lastInsertId();

                $sql = 'UPDATE gocare_device_table SET serial_id = :serial_id, activation_date = :activation_date WHERE device_id = :device_id';
                $stmt = $this->_pdo->prepare($sql);

                $stmt->bindValue(':serial_id', $serial_id, \PDO::PARAM_INT);
                $stmt->bindValue(':device_id', $device_id, \PDO::PARAM_INT);
                $stmt->bindValue(':activation_date', $activation_date);
                $stmt->execute();

                // get plan id from gocare_policy_module_plan
                $plan_id = $this->_getPlanIdFromGocarePolicyModulePlan($customer_id);

                // if we don't have a plan id then we need to create one
                if (!isset($plan_id)) {
                    $profile_id = null;
                    // get the profile_id
                    $sql = 'SELECT * FROM sales_recurring_profile WHERE customer_id = :customer_id';
                    $stmt = $this->_pdo->prepare($sql);
                    $stmt->bindValue(':customer_id', $customer_id, \PDO::PARAM_INT);
                    $stmt->execute();
                    if ($stmt->rowCount() > 0) {
                        $result = $stmt->fetch(\PDO::FETCH_OBJ);
                        $profile_id = $result->profile_id;
                    }
                    $sql = 'INSERT INTO gocare_policy_module_plan (customer_id, policyholder_firstname, policyholder_lastname, profile_id) VALUES (:customer_id, :policyholder_firstname, :policyholder_lastname, :profile_id)';
                    $stmt = $this->_pdo->prepare($sql);
                    $stmt->bindValue(':customer_id', $customer_id);
                    $stmt->bindValue(':policyholder_firstname', $customer_entity->firstname);
                    $stmt->bindValue(':policyholder_lastname', $customer_entity->lastname);
                    $stmt->bindValue(':profile_id', $profile_id);
                    $stmt->execute();

                    // we have one now, let's get it again
                    $plan_id = $this->_getPlanIdFromGocarePolicyModulePlan($customer_id);
                }

                if (isset($plan_id)) { // should have this no matter what, but, just in case we'll cond it
                    $sql = 'INSERT INTO gocare_device_plan_table (device_id, plan_id) VALUES (:device_id, :plan_id)';
                    $stmt = $this->_pdo->prepare($sql);
                    $stmt->bindValue(':device_id', $device_id, \PDO::PARAM_INT);
                    $stmt->bindValue(':plan_id', $plan_id, \PDO::PARAM_INT);
                    $stmt->execute();

                    return true;
                } else {
                    $this->_updateFailedReason($order,
                                               'Plan could not be found in gocare_policy_module_plan table using customer_id = ' . $customer_id . ".",
                                               'plan_not_found');
                    return false;
                }

            } else {
                $this->_updateFailedReason($order, 'Product entity not found (Bad Warranty SKU).', 'product_not_found');
                return false;
            }

        } else {
            $this->_updateFailedReason($order, 'Customer entity not found.', 'customer_not_found');
            return false;
        }
        $this->_updateFailedReason($order, 'Reach end of transaction and failed', 'transaction_error');
        return false;

    }

    /**
     * @param Order $order
     * @param User $user
     * @return bool
     */
    private function _attemptCreateCustomer(Order $order, User $user)
    {
        // prep the data to create the new customer entity
        // firstname and lastname will come from a split of the customer name on space
        $names = explode(' ', $order->name, 2);
        $firstname = $names[0];
        $lastname = array_key_exists(1, $names) ? $names[1] : '';

        // get the regions and try to figure out which region to use
        $regions = $this->_magentoApiClient->directoryRegionList($this->_magentoApiSession, 'US');
        $shortest = -1;
        foreach ($regions as $region) {
            $orderState = $order->state;
            $lev = levenshtein($orderState, $region->name);
            if ($lev == 0) {
                $state = $orderState;
                $region_id = $region->region_id;
                break;
            }

            if ($lev <= $shortest || $shortest < 0) {
                $state = $region->name;
                $region_id = $region->region_id;
                $shortest = $lev;
            }

        }

        try {
            $customer_id = $this->_magentoApiClient->customerCustomerCreate($this->_magentoApiSession, [
                'email' => $order->email,
                'firstname' => $firstname,
                'lastname' => $lastname,
                'password' => md5(rand(0, 10000) . date('YmdHis')),
                'website_id' => 1,
                'store_id' => 1,
                'group_id' => $user->magento_group_id
            ]);

            try {
                $this->_magentoApiClient->customerAddressCreate(
                    $this->_magentoApiSession,
                    $customer_id, [
                        'firstname' => $firstname,
                        'lastname' => $lastname,
                        'street' => [
                            $order->address_1,
                            $order->address_2
                        ],
                        'city' => $order->city,
                        'country_id' => 'US',
                        'region' => $state,
                        'region_id' => $region_id,
                        'postcode' => $order->zip,
                        'telephone' => $order->phone,
                        'is_default_billing' => TRUE,
                        'is_default_shipping' => TRUE
                    ]
                );
            } catch (\SoapFault $e) {
                // todo: what to do if it fails?
                \Log::debug($e->getMessage());
                \Log::debug($e->getTraceAsString());
                $this->_updateFailedReason($order, 'Could not create new customer address.',
                                           'could_not_create_customer_address');
                return false;
            }

        } catch (\SoapFault $e) {
            \Log::debug($e->getMessage());
            \Log::debug($e->getTraceAsString());
            $this->_updateFailedReason($order, 'Could not create new customer. ', 'could_not_create_customer');
            return false;
        }
        return true;
    }

    /**
     * high level view of order, are all fields present??
     * @param $order
     * @return bool
     */
    private function _validateOrder($order)
    {
        if ($order->email === '' || (!$order->email)) {
            $this->_updateFailedReason($order, 'Email address not provided.', 'missing_required_fields');
            return false;
        }
        if ($order->start_date === '' || $order->start_date === '0000-00-00 00:00:00' || (!$order->start_date)) {
            $this->_updateFailedReason($order, 'Activation date not provided.', 'missing_required_fields');
            return false;
        }
        if ($order->sku === '' || (!$order->sku)) {
            $this->_updateFailedReason($order, 'SKU not provided.', 'missing_required_fields');
            return false;
        }
        if ($order->warranty_sku === '' || (!$order->warranty_sku)) {
            $this->_updateFailedReason($order, 'Warranty SKU not provided.', 'missing_required_fields');
            return false;
        }
        if ($order->serial_number === '' || (!$order->serial_number)) {
            $this->_updateFailedReason($order, 'Serial Number not provided.', 'missing_required_fields');
            return false;
        }

        // if the activation date is too old (greater than 45 days), fail and return false
        $today = Carbon::now();
        $day_to_compare = Carbon::createFromTimestamp(strtotime($order->start_date));
        if ($today->diffInDays($day_to_compare) > 90) {
            $this->_updateFailedReason($order, 'Activation date is older than allowed.', 'invalid_activation_date');
            return false;
        }
        return true;
    }

    private function _updateFailedReason($order, $value, $code)
    {
        $order->failed_code = $code;
        $order->failed_reason = $value;
        $order->save();
    }

    /********************************************* END ORDERS SECTION *************************************************/

    /********************************************* CLAIMS SECTION *****************************************************/
    // todo: probably should refactor the claims section out at some point

    /**
     * Takes a claim object (retrieved by a query of the claims table), processes the claim data and pushes
     * the new claim to Magento.
     *
     * @param $claim object
     *
     * @return bool
     */
    public function createClaim($claim)
    {
        // parse the name if needed
        if (strstr($claim->full_name, ' ')) {
            list($first_name, $last_name) = explode(" ", $claim->full_name);
        } else {
            $first_name = $claim->full_name;
            $last_name = '';
        }

        // insert into the ahs_claim table so we can get the claim id
        $device = $this->serialActive($claim->serial_number);

        if ($device) {
            $customer_id = $this->_getCustomerIdFromDevice($device);

            //////////////// todo: vvv
            //////////////// this is a fake claim number generator, remove this after
            ////////////////       the claim number issue has been resolved
            $claim_number = '999' . date('His');
            ///////////////////////////////////////////////////////////////////

            $sql = "INSERT INTO ahs_claim (
                      claim_number, device_id, first_name, last_name, serial_number, claim_address,
                      customer_id, claim_type_id, claim_status_id, created_at, incurred_date
                      ) VALUES (
                      :claim_number, :device_id, :first_name, :last_name, :serial_number, :claim_address,
                      :customer_id, 7, 1, now(), :incurred_date
                      )";

            $stmt = $this->_pdo->prepare($sql);
            $stmt->bindValue(':claim_number', $claim_number, \PDO::PARAM_INT);
            $stmt->bindValue(':device_id', $device->device_id, \PDO::PARAM_INT);
            $stmt->bindValue(':first_name', $first_name, \PDO::PARAM_STR);
            $stmt->bindValue(':last_name', $last_name, \PDO::PARAM_STR);
            $stmt->bindValue(':serial_number', $claim->serial_number, \PDO::PARAM_STR);
            $stmt->bindValue(':claim_address', $this->_serializeClaimAddress($claim), \PDO::PARAM_STR);
            $stmt->bindValue(':customer_id', $customer_id, \PDO::PARAM_INT);
            $stmt->bindValue(':incurred_date', $this->_findIncurredDateFromClaim($claim));
            $stmt->execute();
            $claim_id = $this->_pdo->lastInsertId();

            // build answers to questions
            $sql = "INSERT INTO ahs_claim_answer (claim_id, question_id, customer_id, answer) VALUES(:claim_id, :question_id, :customer_id, :answer)";
            $stmt = $this->_pdo->prepare($sql);
            $stmt->bindValue(':claim_id', $claim_id, \PDO::PARAM_INT);
            $stmt->bindValue(':customer_id', $customer_id, \PDO::PARAM_INT);
            foreach (json_decode($claim->questions) as $question => $answer) {
                $stmt->bindValue(':question_id', $question);
                $stmt->bindValue(':answer', $answer);
                $stmt->execute();
            }
            return true;
        }
        return false;
    }

    private function _serializeClaimAddress($claim)
    {
        list($region_id, $region, $country_id) = $this->_findRegionIdRegionAndCountryIdByClaim($claim);
        $address = [
            'firstname' => $claim->first_name,
            'lastname' => $claim->last_name,
            'street' => $claim->address1 . ($claim->address2) ? ' ' . $claim->address2 : '',
            'city' => $claim->city,
            'region_id' => $region_id,
            'region' => $region,
            'postcode' => $claim->zipcode,
            'country_id' => $country_id
        ];
        return serialize($address);
    }

    private function _findRegionIdRegionAndCountryIdByClaim($claim)
    {
        $region_id = '';
        $region = '';
        $country_id = '';
        $sql = "SELECT * FROM directory_country_region WHERE code = :state1 OR default_name = :state2 LIMIT 1";
        $stmt = $this->_pdo->prepare($sql);
        $stmt->bindValue(':state1', $claim->state, \PDO::PARAM_STR);
        $stmt->bindValue(':state2', $claim->state, \PDO::PARAM_STR);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            $result = $stmt->fetch(\PDO::FETCH_OBJ);
            $region_id = $result->region_id;
            $region = $result->region_id;
            $country_id = $result->country_id;
        }
        return [$region_id, $region, $country_id];
    }

    private function _findIncurredDateFromClaim($claim)
    {
        // the incurred date will either be in question 6 or question 14, let's look in one if it's not there
        // then look in the other, if it's still not there, return now
        $questions = json_decode($claim->questions, true);
        if (isset($questions[6])) {
            return $questions[6];
        } elseif (isset($questions[14])) {
            return $questions[14];
        } else {
            return date('Y-m-d H:i:s');
        }
    }
    /********************************************* END CLAIMS SECTION *************************************************/

    /********************************************* START DEVICES SECTION **********************************************/

    public function getDevicesByEmail($email)
    {
        $sql = "SELECT * FROM customer_entity ce
                JOIN gocare_device_table gdt ON gdt.customer_id = ce.entity_id
                JOIN catalog_product_entity cpe ON cpe.entity_id = gdt.product_id
                JOIN gocare_device_serial_number gdsn ON gdsn.serial_id = gdt.serial_id
                WHERE ce.email = :email";

        $stmt = $this->_pdo->prepare($sql);
        $stmt->bindValue(':email', $email, \PDO::PARAM_STR);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            return $stmt->fetchAll(\PDO::FETCH_OBJ);
        }
        return false;
    }


    /********************************************* END DEVICES SECTION ************************************************/

    /********************************************* HELPERS SECTION ****************************************************/

    // todo: some of the code in this section is quite redundant and could probably be refactored

    /**
     * Takes a serial number and queries Magento. Returns the device object if the serial is exists and
     * is active, false if it doesn't.
     *
     * @param $serial_number string
     * @return object | bool
     */
    public function serialActive($serial_number)
    {
        $sql = "SELECT * FROM gocare_device_serial_number WHERE serial_number = :serial_number AND is_active = 1";
        $stmt = $this->_pdo->prepare($sql);
        $stmt->bindValue(':serial_number', $serial_number, \PDO::PARAM_STR);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            return $stmt->fetch(\PDO::FETCH_OBJ);
        }
        return false;
    }

    private function _getCustomerIdFromDevice($device)
    {
        $sql = "SELECT customer_id FROM gocare_device_table WHERE device_id = :device_id LIMIT 1";
        $stmt = $this->_pdo->prepare($sql);
        $stmt->bindValue(':device_id', $device->device_id, \PDO::PARAM_INT);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            $result = $stmt->fetch(\PDO::FETCH_OBJ);
            return $result->customer_id;
        }
        return false;
    }

    /**
     * Takes an email address and returns the customer_entity object if there is a match, false if not.
     *
     * @param $email string
     * @return \stdClass|bool
     */
    private function _customerExistsByEmail($email)
    {
        $sql = 'SELECT * FROM customer_entity WHERE email = :email AND is_active = 1 LIMIT 1';
        $stmt = $this->_pdo->prepare($sql);
        $stmt->bindValue(':email', $email, \PDO::PARAM_STR);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            return $stmt->fetch(\PDO::FETCH_OBJ);
        }
        return false;
    }

    /**
     * Takes a sku and returns the product entity if there is a match, false if not.
     *
     * @param $warranty_sku string
     * @return object|bool
     */
    private function _productExistsBySku($warranty_sku)
    {
        $sql = "SELECT * FROM catalog_product_entity WHERE sku = :warranty_sku LIMIT 1";
        $stmt = $this->_pdo->prepare($sql);
        $stmt->bindValue(':warranty_sku', $warranty_sku, \PDO::PARAM_STR);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            return $stmt->fetch(\PDO::FETCH_OBJ);
        }
        return false;
    }

    /**
     * Takes an entity id and returns the associated model id or false if not found. The model attribute_id is
     * hard coded to 213.
     *
     * @param $entity_id int
     * @return object|bool
     */
    private function _getModelIdByEntityId($entity_id)
    {
        $sql = "SELECT cpev.value AS model_id FROM catalog_product_entity_varchar cpev
                JOIN eav_attribute_label eal ON eal.attribute_id = cpev.attribute_id
                WHERE cpev.entity_id = :entity_id
                AND cpev.attribute_id = 213
                LIMIT 1";
        $stmt = $this->_pdo->prepare($sql);
        $stmt->bindValue(':entity_id', $entity_id, \PDO::PARAM_INT);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            $result = $stmt->fetch(\PDO::FETCH_OBJ);
            return $result->model_id;
        }
        return false;
    }

    /**
     * Takes an entity id and returns the associated term length or false if not found. The term length attribute_id is
     * hard coded to 191.
     *
     * @param $entity_id int
     * @return object|bool
     */
    private function _getTermLengthByEntityId($entity_id)
    {
        $sql = "SELECT value FROM catalog_product_entity_varchar
                WHERE entity_id = :entity_id
                AND attribute_id = 191
                LIMIT 1";
        $stmt = $this->_pdo->prepare($sql);
        $stmt->bindValue(':entity_id', $entity_id, \PDO::PARAM_INT);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            $result = $stmt->fetch(\PDO::FETCH_OBJ);
            return $result->value;
        }
    }

    /**
     * Takes an entity id and returns the theft deductible value (as a currency formatted string) or false if not found.
     *
     * @param $entity_id int
     * @return string|null
     */
    private function _getTheftDeductibleByEntityId($entity_id)
    {
        $sql = "SELECT eaov.value AS theft_ded FROM catalog_product_index_eav cpie
                JOIN eav_attribute ea ON ea.attribute_id = cpie.attribute_id
                JOIN eav_attribute_option_value eaov ON eaov.option_id = cpie.value
                WHERE cpie.entity_id = :entity_id
                AND cpie.attribute_id = 189
                LIMIT 1";
        $stmt = $this->_pdo->prepare($sql);
        $stmt->bindValue(':entity_id', $entity_id, \PDO::PARAM_INT);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            $result = $stmt->fetch(\PDO::FETCH_OBJ);
            return $result->theft_ded;
        }
        return null;
    }

    /**
     * Takes an entity id and returns the associated adew deductible or false if not found. The adew attribute_id is
     * hard coded to 153.
     *
     * @param $entity_id int
     * @return object|null
     */
    private function _getAdewDeductibleByEntityId($entity_id)
    {
        $sql = "SELECT eaov.value AS value FROM catalog_product_entity_int cpei
                JOIN eav_attribute_option_value eaov ON eaov.option_id = cpei.value
                WHERE entity_id = :entity_id
                AND cpei.attribute_id = 153
                LIMIT 1";
        $stmt = $this->_pdo->prepare($sql);
        $stmt->bindValue(':entity_id', $entity_id, \PDO::PARAM_INT);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            $result = $stmt->fetch(\PDO::FETCH_OBJ);
            return $result->value;
        }
        return null;
    }

    /**
     * Takes an entity id and returns the associated dr deductible or false if not found. The dr ded attribute_id is
     * hard coded to 210.
     *
     * @param $entity_id int
     * @return object|null
     */
    private function _getDrDeductibleByEntityId($entity_id)
    {
        $sql = "SELECT eaov.value AS value FROM catalog_product_entity_int cpei
                JOIN eav_attribute_option_value eaov ON eaov.option_id = cpei.value
                WHERE entity_id = :entity_id
                AND cpei.attribute_id = 210
                LIMIT 1";
        $stmt = $this->_pdo->prepare($sql);
        $stmt->bindValue(':entity_id', $entity_id, \PDO::PARAM_INT);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            $result = $stmt->fetch(\PDO::FETCH_OBJ);
            return $result->value;
        }
        return null;
    }

    /**
     * Takes an entity id and returns the associated ins ded or false if not found. The ins ded attribute_id is
     * hard coded to 224.
     *
     * @param $entity_id int
     * @return object|null
     */
    private function _getInsuranceDeductibleByEntityId($entity_id)
    {
        $sql = "SELECT eaov.value AS value FROM catalog_product_entity_int cpei
                JOIN eav_attribute_option_value eaov ON eaov.option_id = cpei.value
                WHERE entity_id = :entity_id
                AND cpei.attribute_id = 224
                LIMIT 1";
        $stmt = $this->_pdo->prepare($sql);
        $stmt->bindValue(':entity_id', $entity_id, \PDO::PARAM_INT);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            $result = $stmt->fetch(\PDO::FETCH_OBJ);
            return $result->value;
        }
        return null;
    }

    /**
     * Takes an entity id and returns the associated premium or false if not found. The premium is the product price
     * and for now the customer group is hard coded to 0.
     *
     * @param $entity_id int
     * @return object|bool
     */
    private function _getPremiumByEntityId($entity_id)
    {
        $sql = "SELECT price FROM catalog_product_index_price
                WHERE customer_group_id = 0
                AND entity_id = :entity_id
                LIMIT 1";
        $stmt = $this->_pdo->prepare($sql);
        $stmt->bindValue(':entity_id', $entity_id, \PDO::PARAM_INT);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            $result = $stmt->fetch(\PDO::FETCH_OBJ);
            return $result->price;
        }
        return false;
    }

    /**
     * Takes a start date and a term length in months and returns the expiration date of the policy.
     *
     * @param $start_date
     * @param $term_length_in_months
     * @return static
     */
    private function _calculateExpirationDate($start_date, $term_length_in_months)
    {
        $dt = Carbon::createFromTimestamp(strtotime($start_date));
        return $dt->addMonths($term_length_in_months)->toDateString();
    }

    // todo: switch back to private function
    public function _cleanSku($sku)
    {

        $is_orourke = false;
        $is_leopard = false;

        ///////////////////// O'Rourke Specific SKU troubles //////////////////////////////////
        // possible sku: GOCP200T12FWX --> should be: CP200T12F-WX

        // removes the "GO" from the front of the sku
        if (substr($sku, 0, 2) == 'GO') {
            $sku = substr($sku, 2, strlen($sku) - 2);
            $is_orourke = true;
        }
        // adds the dash before the WX
        if (substr($sku, -3, 1) != "-") {
            $sku = str_replace("WX", "-WX", $sku);
            $is_orourke = true;
        }

        if (!$is_orourke) {
            ////////////////// Leopard Specific SKU troubles /////////////////////////////////////////
            // possible sku: SMPNT3T12F0-WX --> should be: SMPNT3T12F0-WX
            // possible sku: SMPS5T12F0-WX --> should be: SMPS5T12FA0-WX

            // adds a "A"
            $sku = str_replace("F0-WX", "FA0-WX", $sku);
        }

        return $sku;

    }

    /**
     * @param $customer_id
     * @return array
     */
    private function _getPlanIdFromGocarePolicyModulePlan($customer_id)
    {
        $sql = "SELECT plan_id FROM gocare_policy_module_plan WHERE customer_id = :customer_id LIMIT 1";
        $stmt = $this->_pdo->prepare($sql);
        $stmt->bindValue(':customer_id', $customer_id, \PDO::PARAM_INT);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            $result = $stmt->fetch(\PDO::FETCH_OBJ);
            $plan_id = $result->plan_id;
            return $plan_id;
        }
        return false;
    }

    /********************************************* END HELPERS SECTION ************************************************/

}
