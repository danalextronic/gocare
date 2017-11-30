<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class ClaimsController extends ApiController
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


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $claim = new Claim();
            $claim->email = $request->get('email');
            $claim->phone = $request->get('phone');
            $claim->serial_number = $request->get('autoSerial');
            $claim->full_name = $request->get('full_name');
            $claim->address = $request->get('address');
            $claim->address2 = $request->get('address2');
            $claim->city = $request->get('city');
            $claim->state = $request->get('state');
            $claim->zipcode = $request->get('zipcode');
            $claim->questions = json_encode($request->get('question'));

            $claim->save();

            $this->dispatch(new ProcessClaims($claim));

            return $this->successResponse(['claim' => $claim]);

        } catch (QueryException $e) {

            return $this->errorResponse($e->getMessage());
        }
    }


}
