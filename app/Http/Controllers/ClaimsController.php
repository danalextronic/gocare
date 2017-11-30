<?php

namespace App\Http\Controllers;

use App\Claim;
use App\User;
use Crypt;
use Illuminate\Http\Request;

class ClaimsController extends GocareController
{

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Contracts\Encryption\EncryptException
     */
    public function create(Request $request)
    {
        /** @var User $user */
        $user = $request->user();
        $apikey = $user->Apikey;
        $encrypted_api_key = Crypt::encrypt($apikey->secret);

        return view('claims.form', [
            'encrypted_api_key' => $encrypted_api_key

        ]);
    }

    public function index(Request $request)
    {
        $claims = Claim::all();

        return view('claims.index', [
            'claims' => $claims
        ]);
    }

}
