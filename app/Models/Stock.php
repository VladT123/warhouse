<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;

class Stock extends Pivot
{
    protected $table = 'stocks';

    // If you need timestamps (unlikely for stock tracking)
    public $timestamps = false;

    // The pivot attributes
    protected $fillable = [
        'product_id',
        'warehouse_id',
        'stock'
    ];

}
