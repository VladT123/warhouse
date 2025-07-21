<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    protected $primaryKey = ['product_id', 'warehouse_id'];
    public $incrementing = false;

    public function product() {
        return $this->belongsTo(Product::class);
    }

    public function warehouse() {
        return $this->belongsTo(Warehouse::class);
    }
}
