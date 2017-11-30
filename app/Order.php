<?php

namespace App;

class Order extends TenancyModel
{
    public function __construct(array $attributes = [])
    {
        $this->fillable = [
            'email',
            'start_date',
            'sku',
            'warranty_sku',
            'serial_number',
            'status',
            'failed_reason',
            'failed_code',
            'user_id',
            'import_id'
        ];
        parent::__construct($attributes);
    }

}
