<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\Warehouse;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;


class OrderController extends Controller {
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

    public function store(Request $request)
    {
        $data = $request->validate([
            'customer' => 'required|string',
            'warehouse_id' => 'required|exists:warehouses,id',
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.count' => 'required|integer|min:1'
        ]);

        return DB::transaction(function () use ($data) {
            // Create the order
            $order = Order::create([
                'customer' => $data['customer'],
                'warehouse_id' => $data['warehouse_id'],
                'created_at' => now(),
            ]);

            $warehouse = Warehouse::findOrFail($data['warehouse_id']);

            foreach ($data['items'] as $item) {
                // Get the product with current stock
                $product = Product::findOrFail($item['product_id']);

                // Check stock availability using the many-to-many relationship
                $currentStock = $warehouse->products()
                    ->where('product_id', $item['product_id'])
                    ->first()
                    ->pivot
                    ->stock ?? 0;

                if ($currentStock < $item['count']) {
                    throw new \Exception("Недостаточно товара {$product->name} (ID: {$item['product_id']}) на складе. Доступно: {$currentStock}, запрошено: {$item['count']}");
                }

                // Update stock through the relationship
                $warehouse->products()->updateExistingPivot($item['product_id'], [
                    'stock' => $currentStock - $item['count']
                ]);

                // Create order item
                $order->items()->create([
                    'product_id' => $item['product_id'],
                    'count' => $item['count'],
                ]);

                $this->recordMovement('order_created', $order, $item, -$item['count']);
            }

            return $order->load('items');
        });
    }

    public function update(Request $request, Order $order) {
        if ($order->status !== 'active') {
            abort(400, 'Можно обновлять только активные заказы');
        }

        $data = $request->validate([
            'customer' => 'string',
            'items' => 'array',
            'items.*.product_id' => 'exists:products,id',
            'items.*.count' => 'integer|min:1'
        ]);

        return DB::transaction(function () use ($order, $data) {
            // Обновление покупателя
            if (isset($data['customer'])) {
                $order->update(['customer' => $data['customer']]);
            }

            // Обновление позиций
            if (isset($data['items'])) {
                $order->items()->delete();
                foreach ($data['items'] as $item) {
                    $stock = Stock::where([
                        'product_id' => $item['product_id'],
                        'warehouse_id' => $order->warehouse_id
                    ])->lockForUpdate()->first();

                    if (!$stock || $stock->stock < $item['count']) {
                        throw new Exception("Недостаточно товара: {$item['product_id']}");
                    }

                    $stock->decrement('stock', $item['count']);
                    $order->items()->create($item);
                }
            }

            return $order->load('items');
        });
    }

    public function complete(Order $order) {
        if ($order->status !== 'active') {
            abort(400, 'Заказ не активен');
        }

        $order->update([
            'status' => 'completed',
            'completed_at' => now()
        ]);

        return response()->json(['message' => 'Заказ завершен']);
    }

    public function cancel(Order $order)
    {
        if ($order->status !== 'active') {
            return response()->json(['error' => 'Заказ не активен'], 400);
        }

        return DB::transaction(function () use ($order) {
            // Eager load items with product information
            $order->load('items.product');

            $warehouse = Warehouse::findOrFail($order->warehouse_id);

            foreach ($order->items as $item) {
                try {
                    // Get current stock through the many-to-many relationship
                    $currentStock = $warehouse->products()
                        ->where('product_id', $item->product_id)
                        ->first();

                    if (!$currentStock) {
                        // If no stock record exists, create one
                        $warehouse->products()->attach($item->product_id, [
                            'stock' => $item->count
                        ]);
                    } else {
                        // Update existing stock
                        $warehouse->products()->updateExistingPivot($item->product_id, [
                            'stock' => $currentStock->pivot->stock + $item->count
                        ]);
                    }

                    // Record movement (assuming this method exists)
                    $this->recordMovement('order_canceled', $order, $item, $item->count);

                } catch (\Exception $e) {
                    throw new \Exception("Ошибка при возврате товара {$item->product->name}: " . $e->getMessage());
                }
            }

            $order->update(['status' => 'canceled']);

            return response()->json([
                'message' => 'Заказ успешно отменен',
                'order' => $order->fresh('items.product')
            ]);
        });
    }

    public function resume(Order $order)
    {
        if ($order->status !== 'canceled') {
            return response()->json(['error' => 'Заказ не отменен'], 400);
        }

        return DB::transaction(function () use ($order) {
            // Eager load items with product information
            $order->load('items.product');

            foreach ($order->items as $item) {
                try {
                    $currentStock = DB::table('stocks')
                        ->where('warehouse_id', $order->warehouse_id)
                        ->where('product_id', $item->product_id)
                        ->lockForUpdate()
                        ->first();

                    if (!$currentStock) {
                        throw new \Exception("Товар {$item->product->name} отсутствует на складе");
                    }

                    if ($currentStock->stock < $item->count) {
                        throw new \Exception(
                            "Недостаточно товара {$item->product->name} для возобновления. " .
                            "Доступно: {$currentStock->stock}, требуется: {$item->count}"
                        );
                    }

                    // Update stock through direct query for better performance
                    DB::table('stocks')
                        ->where('warehouse_id', $order->warehouse_id)
                        ->where('product_id', $item->product_id)
                        ->decrement('stock', $item->count);

                    // Record movement (assuming this method exists)
                    $this->recordMovement('order_resumed', $order, $item, -$item->count);

                } catch (\Exception $e) {
                    throw new \Exception("Ошибка при возобновлении заказа: " . $e->getMessage());
                }
            }

            $order->update(['status' => 'active']);

            return response()->json([
                'message' => 'Заказ успешно возобновлен',
                'order' => $order->fresh('items.product')
            ]);
        });
    }

    private function recordMovement($type, $order, $item, $change) {
        StockMovement::create([
            'product_id' => $item['product_id'] ?? $item->product_id,
            'warehouse_id' => $order->warehouse_id,
            'change' => $change,
            'reason' => $type,
            'order_id' => $order->id
        ]);
    }
}
