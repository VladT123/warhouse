<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $casts = ['created_at' => 'datetime', 'completed_at' => 'datetime'];

    public function items() {
        return $this->hasMany(OrderItem::class);
    }

    public function warehouse() {
        return $this->belongsTo(Warehouse::class);
    }
}
