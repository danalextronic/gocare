<?php

namespace App;

use App\Http\Requests\Request;
use Illuminate\Database\Eloquent\Model;

use Auth;

class TenancyModel extends Model
{
    public static function all($columns = ['*'])
    {
        return self::where('user_id', '=', Auth::user()->id)->get();
    }
}
