<?php

namespace App\Http\Controllers;

use App\Models\Product;

class ProductController extends Controller {
    public function index() {
        return view('products.index', [
            'products' => Product::with('warehouses')->get()
        ]);
    }
}
