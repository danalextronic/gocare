<?php

namespace App\Http\Controllers;

use Hamcrest\MatcherAssert;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Claim;
use App\Order;
use App\Library\Magento;
use SoapClient;

class MagentoController extends GocareController
{
    public function testMagentoCustomerCreate()
    {
//        $this->_magentoApiClient = new SoapClient(env('MAGENTO_API_HOST'));
//        $this->_magentoApiSession = $this->_magentoApiClient->login(env('MAGENTO_API_USERNAME'), env('MAGENTO_API_KEY'));
//
//        $client = new SoapClient(env('MAGENTO_API_HOST'));
//
//
//        $session = $client->login(env('MAGENTO_API_USERNAME'), env('MAGENTO_API_KEY'));

        $order = new \stdClass();
        $order->state = 'Nebarssska';

//        $regions = $client->directoryRegionList($session,'US');
//        $shortest = -1;
//        foreach ($regions as $region) {
//            $lev = levenshtein($order->state, $region->name);
//            if ($lev == 0) {
//                $state = $order->state;
//                $region_id = $region->region_id;
//                break;
//            }
//
//            if ($lev <= $shortest || $shortest < 0) {
//                $state = $region->name;
//                $region_id = $region->region_id;
//                $shortest = $lev;
//            }
//
//        }

//        echo($state);
//        echo($region_id);
//        die();

        $order = Order::find(33);


        dd($order);

        $firstname = substr($order->name, 0, strrpos($order->name,' '));
        $lastname = substr($order->name, (strrpos($order->name, '   ') + 1));



        echo($firstname . ' ' . $lastname);
        die();

        try {
            $customer_id = $client->customerCustomerCreate($session, [
                'email' => date('YmdHis') . 'customer-mail@example.org',
                'firstname' => 'Dough',
                'lastname' => 'Deeks',
                'password' => 'password',
                'website_id' => 1,
                'store_id' => 1,
                'group_id' => 1
            ]);

            try {
                $address_id = $client->customerAddressCreate($session, $customer_id, [
                    'firstname' => 'John',
                    'lastname' => 'Doe',
                    'street' => [
                        'Street line 1',
                        'Streer line 2'
                    ],
                    'city' => 'Weaverville',
                    'country_id' => 'US',
                    'region' => 'Texas',
                    'region_id' => 15,
                    'postcode' => '96093',
                    'telephone' => '(530) 623-2513 Ext 12345',
                    'is_default_billing' => FALSE,
                    'is_default_shipping' => FALSE
                ]);
            } catch (\SoapFault $e) {
                // todo: what to do if it fails?
                dd($e);
                die();
            }

        } catch (\SoapFault $e) {
            // todo: what to do if it fails?
            print_r($e);

        }





    }

    public function testClaim()
    {
        $claim = Claim::first();

        $magento = new Magento();
        $magento->createClaim($claim);

    }

    public function testOrder()
    {
        $order = Order::find(2);

        $magento = new Magento();
        var_dump($magento->createOrder($order));
    }

    public function testCleanSku()
    {
        $magento = new Magento();

        $orskus = [
            "GOCP200T12FWX",
            "GOCP250T12FWX",
            "GOCP400T12FWX",
            "GOCP600T12FWX"
        ];

        $lskus = [
            "SMPNT3T12F0-WX",
            "SMPS5T12F0-WX"
        ];

        foreach ($orskus as $orsku) {
            echo("O'Rourke incoming sku: " . $orsku . PHP_EOL);
            echo("O'Rourke altered sku: " . $magento->_cleanSku($orsku) . PHP_EOL);
            echo(str_repeat("-", 80) . PHP_EOL);
        }

        foreach ($lskus as $lsku) {
            echo("Leopard incoming sku: " . $lsku . PHP_EOL);
            echo("Leopard altered sku: " . $magento->_cleanSku($lsku) . PHP_EOL);
            echo(str_repeat("-", 80) . PHP_EOL);
        }

        die();
    }
}
