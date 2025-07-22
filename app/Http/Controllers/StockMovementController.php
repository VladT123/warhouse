<?php

namespace App\Http\Controllers;

use App\Repositories\StockMovementRepository;
use Illuminate\Http\Request;

class StockMovementController extends Controller {
    protected $repository;

    public function __construct(StockMovementRepository $repository) {
        $this->repository = $repository;
    }

    public function index(Request $request) {
        $filters = [
            'warehouse_id' => $request->warehouse_id,
            'product_id' => $request->product_id,
            'date_from' => $request->date_from,
            'date_to' => $request->date_to
        ];

        return view('stock-movements.index', [
            'movements' => $this->repository->getFilteredMovements($filters),
            'warehouses' => $this->repository->getAllWarehouses(),
            'products' => $this->repository->getAllProducts()
        ]);
    }
}
