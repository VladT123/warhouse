<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Warehouse extends Model
{
    use HasFactory;
    protected $guarded = [];
    public function products()
    {
        return $this->belongsToMany(Product::class, 'stocks')
            ->withPivot('stock')
            ->using(Stock::class); // Optional: if you want a custom pivot model
    }
}
