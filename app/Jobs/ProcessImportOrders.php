<?php

namespace App\Jobs;

use App\Import;
use App\Order;
use App\User;
use Illuminate\Bus\Dispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessImportOrders extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    /**
     * @var Dispatcher
     */
    protected $dispatcher;

    /**
     * @var Import
     */
    protected $import;

    /**
     * @var User
     */
    protected $user;

    /**
     * Create a new job instance.
     * @param Import $import
     * @param User $user
     */
    public function __construct(Import $import, User $user)
    {
        $this->import = $import;
        $this->user = $user;
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws \Illuminate\Database\Eloquent\MassAssignmentException
     */
    public function handle()
    {
        $import = $this->import;
        $row = 1;
        $isGocareCsv = false;
        $email_col = 3;
        $activation_date_col = 4;
        $warranty_sku_col = 5;
        $sku_col = 6;
        $serial_number_col = 7;

        if (($handle = fopen($import->file_path, 'r')) !== FALSE) {
            while (($data = fgetcsv($handle, 3000, ',')) !== FALSE) {
                $num = count($data);
                if ($row == 1) {
                    if ($data[0] === 'id'
                        && $data[1] === 'user_id'
                        && $data[2] === 'import_id'
                        && $data[3] === 'email'
                        && $data[4] === 'start_date'
                        && $data[5] === 'warranty_sku'
                        && $data[6] === 'sku'
                        && $data[7] === 'serial_number'
                        && $data[8] === 'status'
                        && $data[9] === 'failed_reason'
                        && $data[10] === 'failed_code'
                        && $data[11] === 'created_at'
                        && $data[12] === 'updated_at'
                    ) {
                        $isGocareCsv = true;
                        $import->status = 'merged_with_original';
                        $import->save();
                    } else {
                        for ($c = 0; $c < $num; $c++) {
                            switch (strtolower($data[$c])) {
                                case 'administrator email': // Leopard and O'Rourke
                                    $email_col = $c;
                                    break;
                                case 'sku':
                                    $sku_col = $c;
                                    break;
                                case 'warranty sku': // Leopard and O'Rourke
                                    $warranty_sku_col = $c;
                                    break;
                                case 'serial # (imei)': // Leopard
                                case 'serial #': // O'Rourke
                                    $serial_number_col = $c;
                                    break;
                                case 'date of sale': // Leopard
                                case 'inv date': // O'Rourke
                                    $activation_date_col = $c;
                                    break;
                            }

                        }
                    }

                } else {
                    $orderData = [
                        'email' => $data[$email_col],
                        'start_date' => date('Y-m-d 00:00:00', strtotime($data[$activation_date_col])),
                        'sku' => $data[$sku_col],
                        'warranty_sku' => $data[$warranty_sku_col],
                        'serial_number' => $data[$serial_number_col]
                    ];
                    if ($isGocareCsv) {
                        /** @var Order $order */
                        $order = Order::where('id', '=', $data[0])->where('user_id', '=',
                                                                          $this->user->id)->first();
                        $orderData['status'] = 'pending';
                        $orderData['failed_reason'] = '';
                        $orderData['failed_code'] = '';

                        $order->fill($orderData);
//                        $order->email = $data[3];
//                        $order->start_date = date('Y-m-d 00:00:00', strtotime($data[4]));
//                        $order->sku = $data[6];
//                        $order->warranty_sku = $data[5];
//                        $order->serial_number = $data[7];
//                        $order->status = 'pending';
//                        $order->failed_reason = '';
//                        $order->failed_code = '';
                        $order->save();
                    } else {
                        $orderData['import_id'] = $import->id;
                        $orderData['user_id'] = $this->user->id;
                        $order = new Order(
                            $orderData
                        );
                        // todo: add device model to orders
                        $order->save();
                    }

                    $this->dispatchOrderProcessing($order);
                }

                $row++;

            }
            fclose($handle);
        }
    }

    /**
     * @param $job
     * @return mixed
     */
    protected function dispatch($job)
    {
        if (null === $this->dispatcher) {
            $this->dispatcher = app(Dispatcher::class);
        }
        return $this->dispatcher->dispatch($job);
    }

    private function dispatchOrderProcessing($order)
    {
        return $this->dispatch(new ProcessNewOrder($order));
    }
}
