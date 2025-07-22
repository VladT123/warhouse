<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Models\Order;
use App\Models\Product;
use App\Models\Warehouse;
use App\Services\OrderService;
use Exception;
use Illuminate\Http\Request;

class OrderController extends Controller {
    protected $orderService;

    public function __construct(OrderService $orderService) {
        $this->orderService = $orderService;
    }

    public function index(Request $request) {
        $query = Order::query();

        if ($request->status) {
            $query->where('status', $request->status);
        }

        return view('orders.index', [
            'orders' => $query->paginate(10)->appends($request->query())
        ]);
    }

    public function create() {
        return view('orders.form', [
            'warehouses' => Warehouse::all(),
            'products' => Product::all()
        ]);
    }

    public function edit(Order $order) {
        return view('orders.form', [
            'order' => $order->load('items'),
            'warehouses' => Warehouse::all(),
            'products' => Product::all()
        ]);
    }

    public function show(Order $order) {
        return view('orders.show', [
            'order' => $order->load(['items.product', 'warehouse'])
        ]);
    }

    public function store(StoreOrderRequest $request) {
        try {
            $order = $this->orderService->createOrder($request->validated());
            return redirect()->route('orders.show', $order);
        } catch (Exception $e) {
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function update(UpdateOrderRequest $request, Order $order) {
        try {
            $order = $this->orderService->updateOrder($order, $request->validated());
            return redirect()->route('orders.show', $order);
        } catch (Exception $e) {
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function complete(Order $order) {
        try {
            $order->update([
                'status' => 'completed',
                'completed_at' => now()
            ]);
            return redirect(route('orders.index', absolute: false));
        } catch (Exception $e) {
            return back()
                ->withInput()
                ->withErrors(['error' => $e->getMessage()]);        }
    }

    public function cancel(Order $order) {
        try {
            $this->orderService->cancelOrder($order);
            return redirect(route('orders.index', absolute: false));
        } catch (Exception $e) {
            return back()
                ->withInput()
                ->withErrors(['error' => $e->getMessage()]);        }
    }

    public function resume(Order $order) {
        try {
            $this->orderService->resumeOrder($order);
            return redirect(route('orders.index', absolute: false));
        } catch (Exception $e) {
            return back()
                ->withInput()
                ->withErrors(['error' => $e->getMessage()]);        }
    }
}
