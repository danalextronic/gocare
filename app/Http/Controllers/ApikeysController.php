<?php

namespace App\Http\Controllers;

use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Apikey;

class ApikeysController extends GocareController
{

    public function index(Request $request)
    {
        $apikey = Apikey::where('user_id', '=', $request->user()->id)->first();
        return view('apikeys.index', [
            'apikey' => $apikey
        ]);
    }

    /**
     * Deletes the old api keys and creates new api keys for the user
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $apikey = Apikey::where('user_id', '=', $request->user()->id)->first();
            if ($apikey) {
                $this->destroy($apikey->id);
            }

            $apikey = new Apikey();
            $apikey->user_id = $request->user()->id;
            $apikey->public = 'pk_' . md5(base64_encode(md5(rand(0, 100000) . rand(0, 100000) . md5(date('YmdHis')))));
            $apikey->secret = 'sk_' . md5(base64_encode(md5(date('YmdHis') . rand(0, 100000) . rand(0, 100000))));

            // double check the secret doesn't exist, it shouldn't but, just in case
            if (!$this->_findBySecret($apikey->secret, true)) {
                $apikey->save();
                return redirect('/apikeys')->with('success', 'Your new API key has been generated');
            }

            return redirect('/apikeys')->with('error', 'Your API Key could not be generated, please try again.');

        } catch (QueryException $e) {
            return $this->errorResponse($e->getMessage());

        }
    }

    /**
     * Display the specified resource.
     *
     * @param  string api secret encrypted string
     * @return \Illuminate\Http\Response
     */
    public function show($secret_encrypted)
    {
        try {
            $apikey = $this->_findBySecret($secret_encrypted);
            if ($apikey) {
                return $this->successResponse($apikey);
            } else {
                return $this->errorResponse('API key does not exist!');
            }

        } catch (QueryException $e) {
            return $this->errorResponse($e->getMessage());
        }

    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $apikey = Apikey::find($id);
            $apikey->delete();
            return $this->successResponse([]);

        } catch (QueryException $e) {
            return $this->errorResponse($e->getMessage());

        }
    }


    private function _findBySecret($secret, $bool = null)
    {
        try {
            $apikey = Apikey::where('secret', '=', $secret)->first();
            if ($apikey) {
                return $apikey;
            } else {
                if ($bool) {
                    return false;
                }
                return $this->errorResponse('Invalid API Secret!');
            }

        } catch (QueryException $e) {
            if ($bool) {
                return false;
            }
            return $this->errorResponse($e->getMessage());
        }


    }
}
