<?php

namespace App\Http\Controllers\API;

use App\Jobs\ProcessNewOrder;

use App\Apikey;
use App\Http\Controllers\API\ApiController;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

use App\Http\Requests;

use App\User;
use App\Order;

use Auth;

class OrdersController extends ApiController
{
    private $_user;
    private $_apikey;
    private $_key;

    public function __construct(Request $request)
    {
        parent::__construct($request);

        if (Auth::check()) {
            $this->_key = Apikey::where('user_id', '=', Auth::user()->id)->first();
        } else {
            $this->_key = $request->header('X-Api-Key');
        }
        $this->_apikey = Apikey::where('secret', '=', $this->_key)->first();
        if (!$this->_apikey) {
            $this->_key = $this->_decrypt($this->_key);
            $this->_apikey = Apikey::where('secret', '=', $this->_key)->first();
        }
        $this->_user = User::find($this->_apikey->user_id);

    }



    public function store(Request $request)
    {
        try {
            $order = new Order();
            $order->user_id = $this->_user->id;
            $order->email = $request->get('email');
            $order->start_date = $request->get('start_date');
            $order->sku = $request->get('sku');
            $order->warranty_sku = $request->get('warranty_sku');
            $order->serial_number = $request->get('serial_number');
            $order->save();

            $this->dispatch(new ProcessNewOrder($order));

            return $this->successResponse([$order]);

        } catch (QueryException $e) {

            return $this->errorResponse($e->getMessage());

        }

    }


}
