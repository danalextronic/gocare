<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Apikey;

use Crypt;

use Auth;

use App\User;

class ApiController extends Controller
{
    private $_key;
    public $user;

    /**
     * ApiController constructor. Grabs the API key, assigns it to the private class value, and fires the api_key_exists
     * function.
     *
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->_key = $request->header('X-Api-Key');
        $this->user = $this->getUserByApiKey($this->_key);
        if (!$this->_api_key_exists()) {
            echo $this->errorResponse('Invalid API Key');
            die();
        };
    }

    /**
     * A quick and dirty way to make sure, at least, the API key exists. If so, we'll let the API scripts continue,
     * otherwise, we just echo some JSON and die.
     */
    private function _api_key_exists()
    {
        $apikey = Apikey::where('secret', '=', $this->_key)->first();
        if (!$apikey) {
            return false;
        }
        return true;
    }

    public function getUserByApiKey($key)
    {
        $apikey = Apikey::where('secret', '=', $this->_key)->first();
        $user = User::find($apikey->user_id);
        return $user;
    }

    protected function jsonResponse(array $json, $code = 200) {
        return response()->make(json_encode($json, JSON_NUMERIC_CHECK), $code, ['Content-Type' => 'application/json']);
    }

    protected function successResponse(array $data = [], $code = 200) {
        return $this->jsonResponse(['result' => 'success'] + (!empty($data) ? ['data' => $data] : []), $code);
    }

    protected function errorResponse($error, $code = 200, array $data = []) {
        return $this->jsonResponse(['result' => 'error', 'error' => $error] + $data, $code);
    }

    protected function customResponse($data, $code = 200, $contentType = 'text/html') {
        return response()->make($data, $code, ['Content-Type' => $contentType]);
    }
}
