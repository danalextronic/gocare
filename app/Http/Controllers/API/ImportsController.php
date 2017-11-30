<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Import;
use App\Order;

use App\Jobs\ProcessNewOrder;

class ImportsController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // figure out request filename, we only accept one file at a time right now, so, just get the first
        // of the $_FILES
        $key = key($_FILES);
        $file = $request->{$key};

        if ($file->getClientMimeType() != 'text/csv') {
            return $this->errorResponse('You have provided an invalid file type, only CSV files are allowed at this time.');
            die();
        }

        $destinationPath = 'uploads/imports/' . $this->user->id;

        $upload_success = $file->move($destinationPath, $file->getClientOriginalName());

        if ($upload_success) {
            $import = new Import();

            $import->file_path = $destinationPath . '/' . $file->getClientOriginalName();
            $import->file_name = $file->getClientOriginalName();
            $import->file_directory = $destinationPath;
            $import->user_id = $this->user->id;
            $import->mime = $file->getClientMimeType();
            $import->save();


            // todo: this should be a queue job, putting it here to flesh it out but should be moved to a job
            $row = 1;
            if (($handle = fopen($import->file_path, "r")) !== FALSE) {
                while (($data = fgetcsv($handle, 3000, ",")) !== FALSE) {
                    $num = count($data);

                    if ($row == 1) {
                        for ($c = 0; $c < $num; $c++) {
                            switch (strtolower($data[$c])) {
                                case "administrator email": // Leopard and O'Rourke
                                    $email_col = $c;
                                    break;
                                case "sku":
                                    $sku_col = $c;
                                    break;
                                case "warranty sku": // Leopard and O'Rourke
                                    $warranty_sku_col = $c;
                                    break;
                                case "serial # (imei)": // Leopard
                                case "serial #": // O'Rourke
                                    $serial_number_col = $c;
                                    break;
                                case "date of sale": // Leopard
                                case "inv date": // O'Rourke
                                    $activation_date_col = $c;
                                    break;
                                case "cust name": // O'Rourke
                                case "customer name": // Leopard
                                    $name_col = $c;
                                    break;
                                case "address": // O'Rourke
                                    $address_1_col = $c;
                                    break;
                                case "address 2": // O'Rourke
                                    $address_2_col = $c;
                                    break;
                                case "city": // O'Rourke
                                    $city_col = $c;
                                    break;
                                case "st": // O'Rourke
                                case "state": // Leopard
                                    $state_col = $c;
                                    break;
                                case "zip": // O'Rourke
                                case "zipcode": // Leopard
                                    $zip_col = $c;
                                    break;
                                case "administrator phone": // O'Rourke and Leopard
                                    $phone_col = $c;
                                    break;

                            }
                        }
                    } else {
                        $order = new Order();
                        $order->import_id = $import->id;
                        $order->user_id = $this->user->id;
                        $order->email = $data[$email_col];
                        $order->start_date = date("Y-m-d 00:00:00", strtotime($data[$activation_date_col]));
                        $order->sku = $data[$sku_col];
                        $order->warranty_sku = $data[$warranty_sku_col];
                        $order->serial_number = $data[$serial_number_col];
                        $order->name = $data[$name_col];
                        $order->address_1 = $data[$address_1_col];
                        $order->address_2 = $data[$address_2_col];
                        $order->city = $data[$city_col];
                        $order->state = $data[$state_col];
                        $order->zip = $data[$zip_col];
                        $order->phone = $data[$phone_col];
                        // todo: add device model to orders
                        $order->save();

                        $this->dispatch(new ProcessNewOrder($order));
                    }

                    $row++;

                }
                fclose($handle);
            }

            return $this->successResponse(['status' => 'success', 'data' => $import]);


        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $import = Import::find($id);
        if ($import) {
            return $this->successResponse(['status' => 'success', 'data' => $import]);
        }
        return $this->errorResponse('Invalid ID!');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
