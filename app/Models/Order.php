<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $casts = ['created_at' => 'datetime', 'completed_at' => 'datetime'];
    protected $guarded = [];

    public function items() {
        return $this->hasMany(OrderItem::class);
    }

    public function warehouse() {
        return $this->belongsTo(Warehouse::class);
    }
}
