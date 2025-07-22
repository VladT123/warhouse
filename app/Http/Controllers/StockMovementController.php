<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Warehouse;
use Illuminate\Http\Request;

class StockMovementController extends Controller {
    public function index(Request $request) {
        $query = StockMovement::with(['product', 'warehouse']);

        if ($request->warehouse_id) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        if ($request->product_id) {
            $query->where('product_id', $request->product_id);
        }

        if ($request->date_from) {
            $query->where('created_at', '>=', $request->date_from);
        }

        if ($request->date_to) {
            $query->where('created_at', '<=', $request->date_to);
        }

        return view('stock-movements.index', [
            'movements' => $query->paginate(10)->appends($request->query()),
            'warehouses' => Warehouse::all(),
            'products' => Product::all()
        ]);
    }
}
