<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Warehouse;
use Exception;
use Illuminate\Support\Facades\DB;

class OrderService {
    /**
     * Создает новый заказ и обновляет остатки на складе
     *
     * @param array $data Данные заказа (customer, warehouse_id, items)
     * @return Order Созданный заказ с привязанными товарами
     * @throws Exception Если недостаточно товара на складе
     */
    public function createOrder(array $data): Order {
        return DB::transaction(function () use ($data) {
            // Создаем основную запись заказа
            $order = Order::create([
                'customer' => $data['customer'],
                'warehouse_id' => $data['warehouse_id'],
                'created_at' => now(),
            ]);

            // Получаем склад, на котором размещается заказ
            $warehouse = Warehouse::findOrFail($data['warehouse_id']);

            // Обрабатываем каждый товар в заказе
            foreach ($data['items'] as $item) {
                $product = Product::findOrFail($item['product_id']);
                // Получаем текущий остаток товара на складе
                $currentStock = $this->getCurrentStock($warehouse, $item['product_id']);

                // Проверяем, достаточно ли товара на складе
                if ($currentStock < $item['count']) {
                    throw new Exception("Недостаточно товара {$product->name} (ID: {$item['product_id']}) на складе. Доступно: {$currentStock}, запрошено: {$item['count']}");
                }

                // Обновляем остатки на складе
                $this->updateStock($warehouse, $item['product_id'], $currentStock - $item['count']);
                // Добавляем товар к заказу
                $order->items()->create($item);
                // Записываем движение товара
                $this->recordMovement('order_created', $order, $item['product_id'], -$item['count']);
            }

            return $order->load('items');
        });
    }

    /**
     * Обновляет существующий заказ и корректирует остатки на складе
     *
     * @param Order $order Заказ для обновления
     * @param array $data Новые данные заказа
     * @return Order Обновленный заказ
     * @throws Exception Если заказ не активен или недостаточно товара
     */
    public function updateOrder(Order $order, array $data): Order {
        // Проверяем, что заказ можно обновлять (только активные)
        if ($order->status !== 'active') {
            throw new Exception('Можно обновлять только активные заказы');
        }

        return DB::transaction(function () use ($order, $data) {
            // Обновляем данные заказчика, если они изменились
            if (isset($data['customer'])) {
                $order->update(['customer' => $data['customer']]);
            }

            // Если обновляются товары в заказе
            if (isset($data['items'])) {
                // Получаем текущие товары в заказе для сравнения
                $currentItems = $order->items->keyBy('product_id');

                // Удаляем все текущие товары (добавим новые ниже)
                $order->items()->delete();

                // Обрабатываем каждый новый товар в заказе
                foreach ($data['items'] as $item) {
                    $currentStock = $this->getCurrentStock($order->warehouse, $item['product_id']);
                    // Считаем разницу между старым и новым количеством
                    $originalCount = $currentItems[$item['product_id']]->count ?? 0;
                    $countDifference = $originalCount - $item['count'];

                    if ($countDifference < 0) {
                        // Если нового товара больше - проверяем наличие
                        if ($currentStock < abs($countDifference)) {
                            throw new Exception("Недостаточно товара: {$item['product_id']}");
                        }
                        $stockChange = -abs($countDifference); // отрицательное значение для уменьшения остатка
                    } else {
                        // Если товара стало меньше - возвращаем разницу на склад
                        $stockChange = $countDifference; // положительное значение для увеличения остатка
                    }

                    // Обновляем остатки на складе
                    $newStock = $currentStock + $stockChange;
                    $this->updateStock($order->warehouse, $item['product_id'], $newStock);
                    // Добавляем товар к заказу
                    $order->items()->create($item);
                    // Записываем движение товара
                    $this->recordMovement('order_updated', $order, $item['product_id'], $stockChange);
                }
            }

            return $order->load('items');
        });
    }

    /**
     * Отменяет заказ и возвращает товары на склад
     *
     * @param Order $order Заказ для отмены
     * @return Order Отмененный заказ
     * @throws Exception Если заказ уже не активен
     */
    public function cancelOrder(Order $order): Order {
        // Проверяем, что заказ активен
        if ($order->status !== 'active') {
            throw new Exception('Заказ не активен');
        }

        return DB::transaction(function () use ($order) {
            $order->load('items');
            $warehouse = $order->warehouse;

            // Возвращаем каждый товар из заказа на склад
            foreach ($order->items as $item) {
                $currentStock = $this->getCurrentStock($warehouse, $item->product_id);
                $this->updateStock($warehouse, $item->product_id, $currentStock + $item->count);
                $this->recordMovement('order_canceled', $order, $item->product_id, $item->count);
            }

            // Меняем статус заказа
            $order->update(['status' => 'canceled']);
            return $order;
        });
    }

    /**
     * Возобновляет отмененный заказ и снова резервирует товары
     *
     * @param Order $order Заказ для возобновления
     * @return Order Возобновленный заказ
     * @throws Exception Если заказ не отменен или недостаточно товара
     */
    public function resumeOrder(Order $order): Order {
        // Проверяем, что заказ отменен
        if ($order->status !== 'canceled') {
            throw new Exception('Заказ не отменен');
        }

        return DB::transaction(function () use ($order) {
            $order->load('items');
            $warehouse = $order->warehouse;

            // Снова резервируем товары для заказа
            foreach ($order->items as $item) {
                $currentStock = $this->getCurrentStock($warehouse, $item->product_id);

                // Проверяем наличие товара перед резервированием
                if ($currentStock < $item->count) {
                    throw new Exception("Недостаточно товара {$item->product->name} для возобновления");
                }

                $this->updateStock($warehouse, $item->product_id, $currentStock - $item->count);
                $this->recordMovement('order_resumed', $order, $item->product_id, -$item->count);
            }

            // Меняем статус заказа обратно на активный
            $order->update(['status' => 'active']);
            return $order;
        });
    }

    /**
     * Получает текущий остаток товара на складе
     *
     * @param Warehouse $warehouse Склад
     * @param int $productId ID товара
     * @return int Количество товара на складе
     */
    private function getCurrentStock(Warehouse $warehouse, int $productId): int {
        $product = $warehouse->products()->find($productId);
        return $product ? $product->pivot->stock : 0;
    }

    /**
     * Обновляет остаток товара на складе
     *
     * @param Warehouse $warehouse Склад
     * @param int $productId ID товара
     * @param int $newStock Новое количество товара
     */
    private function updateStock(Warehouse $warehouse, int $productId, int $newStock): void {
        $warehouse->products()->updateExistingPivot($productId, ['stock' => $newStock]);
    }

    /**
     * Записывает движение товара на складе
     *
     * @param string $type Тип операции
     * @param Order $order Заказ
     * @param int $productId ID товара
     * @param int $change Изменение количества (положительное или отрицательное)
     */
    private function recordMovement(string $type, Order $order, int $productId, int $change): void {
        StockMovement::create([
            'product_id' => $productId,
            'warehouse_id' => $order->warehouse_id,
            'change' => $change,
            'reason' => $type,
            'order_id' => $order->id
        ]);
    }
}
