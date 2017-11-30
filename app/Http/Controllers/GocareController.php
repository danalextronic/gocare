<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class GocareController extends Controller
{
    public function __construct()
    {

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
