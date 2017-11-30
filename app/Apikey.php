<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Apikey extends Model
{
    public function __construct(array $attributes = [])
    {
        $this->table = 'apikeys';
        $this->fillable = ['secret', 'public', 'user_id'];
        parent::__construct($attributes);
    }

}
