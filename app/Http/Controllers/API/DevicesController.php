<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;

use App\Http\Requests;
use Auth;

use App\Apikey;
use App\User;

use App\Library\Magento;

class DevicesController extends ApiController
{

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


    public function search(Request $request)
    {
        // we'll either have a query string of email or serial number
        if ($request->has('email')) {

            $magento = new Magento();
            $devices = $magento->getDevicesByEmail($request->get('email'));

            if ($devices) {
                return $this->successResponse([$devices]);
            } else {
                return $this->errorResponse('No results found');
            }

        } elseif ($request->has('serial_number')) {

        } else {
            return $this->errorResponse('Invalid Request');
        }
    }


}
