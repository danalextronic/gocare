<?php

namespace App\Http\Controllers;

use App\Import;
use App\Jobs\ProcessImportOrders;
use App\Jobs\ProcessNewOrder;
use App\Order;
use Illuminate\Http\Request;
use Redirect;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class OrdersController extends GocareController
{

    public function index(Request $request)
    {
        $orders = Order::all();

        return view('orders.index', [
            'orders' => $orders
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $order = new Order();

        return view('orders.form', [
            'order' => $order
        ]);
    }

    public function store(Request $request)
    {
        $order = new Order();
        $order->email = $request->email;
        $order->start_date = $request->start_date;
        $order->serial_number = $request->serial_number;
        $order->sku = $request->sku;
        $order->warranty_sku = $request->warranty_sku;
        $order->user_id = $request->user()->id;
        $order->save();
        $this->dispatch(new ProcessNewOrder($order));

        return redirect('/orders')->with('info',
                                         'New order has been added to the queue. You will be notified when the order has processed.');
    }

    public function edit(Request $request, $id)
    {
        $order = Order::find($id);
        return view('orders.form', [
            'order' => $order
        ]);
    }

    public function update(Request $request, $id)
    {
        $order = Order::find($id);
        $order->status = 'pending';
        $order->failed_reason = NULL;
        $order->failed_code = NULL;
        $order->email = $request->email;
        $order->start_date = $request->start_date;
        $order->sku = $request->sku;
        $order->warranty_sku = $request->warranty_sku;
        $order->serial_number = $request->serial_number;
        $order->save();

        $this->dispatch(new ProcessNewOrder($order));

        return redirect('/orders')->with('info',
                                         'Order has been resubmitted to the queue. You will be notified when the order has processed.');

    }

    public function destroy(Request $request, $id)
    {
        $order = Order::where('id', '=', $id)->where('user_id', '=', $request->user()->id)->first();
        if ($order) {
            $order->delete();
            $message = "Order has been deleted";
        } else {
            $message = "Order has already been deleted";
        }

        return Redirect::back()->with('info', $message);
    }

    public function getFailedOrders(Request $request, $code = null)
    {
        if ($code) {
            $orders = Order::where('status', '=', 'failed')->where('failed_code', '=', $code)->get();
        } else {
            $orders = Order::where('status', '=', 'failed')->get();
        }

        return view('orders.index', [
            'orders' => $orders
        ]);
    }

    public function getImports()
    {
        $imports = Import::all();
        return view('orders.imports.index', [
            'imports' => $imports
        ]);
    }

    public function getImport()
    {
        return view('orders.imports.form', [

        ]);
    }

    public function postImport(Request $request)
    {
        // first, save the file and update the database
        $file = $request->file;

        // this can be extended to allow other file types (e.g. excel) as we move forward
        if (!$this->_isValidFile($file)) {
            return redirect('/orders/imports')->with('error',
                                                     'You have provided an invalid file type, only CSV files are allowed at this time.');
        }
        $destinationPath = 'uploads/imports/' . $request->user()->id;

        $upload_success = $file->move($destinationPath, $file->getClientOriginalName());

        if ($upload_success) {
            $import = new Import();
            $import->file_path = $destinationPath . '/' . $file->getClientOriginalName();
            $import->file_name = $file->getClientOriginalName();
            $import->file_directory = $destinationPath;
            $import->user_id = $request->user()->id;
            $import->mime = $file->getClientMimeType();
            $import->save();

            $this->dispatch(new ProcessImportOrders($import, $request->user()));

            return redirect('/orders/imports')->with('info', 'CSV Uploaded');

        }
    }

    public function downloadCsv(Request $request, $status = null)
    {
        // get the orders by status
        if ($status) {
            $orders = Order::where('user_id', '=', $request->user()->id)->where('status', '=',
                                                                                $status)->get()->toArray();
        } else {
            $orders = Order::where('user_id', '=', $request->user()->id)->get()->toArray();
        }

        $headers = [
            'Content-type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="failed_orders.csv"'
        ];

        # add headers for each column in the CSV download
        array_unshift($orders, array_keys($orders[0]));

        $callback = function () use ($orders) {
            $FH = fopen('php://output', 'w');
            foreach ($orders as $row) {
                fputcsv($FH, $row);
            }
            fclose($FH);
        };

        return response()->stream($callback, 200, $headers);

    }

    /**
     * @param UploadedFile $file
     * @return bool
     */
    protected function _isValidFile(UploadedFile $file)
    {
        return $file->getClientMimeType() === 'text/csv';
    }

}
