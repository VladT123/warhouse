<?php

namespace App\Repositories;

use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Warehouse;
use Illuminate\Pagination\LengthAwarePaginator;

class StockMovementRepository {
    /**
     * Получает отфильтрованные движения товаров с пагинацией
     *
     * @param array $filters Массив параметров фильтрации:
     *                       - warehouse_id (int) - ID склада
     *                       - product_id (int) - ID товара
     *                       - date_from (string) - Начальная дата периода
     *                       - date_to (string) - Конечная дата периода
     * @param int $perPage Количество элементов на странице (по умолчанию 10)
     * @return LengthAwarePaginator Пагинированный список движений товаров
     */
    public function getFilteredMovements(array $filters, int $perPage = 10): LengthAwarePaginator {
        // Создаем базовый запрос с подгрузкой связанных моделей
        $query = StockMovement::with(['product', 'warehouse']);

        // Фильтр по складу
        if (!empty($filters['warehouse_id'])) {
            $query->where('warehouse_id', $filters['warehouse_id']);
        }

        // Фильтр по товару
        if (!empty($filters['product_id'])) {
            $query->where('product_id', $filters['product_id']);
        }

        // Фильтр по начальной дате периода
        if (!empty($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }

        // Фильтр по конечной дате периода
        if (!empty($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }

        // Возвращаем результат с пагинацией
        return $query->paginate($perPage);
    }

    /**
     * Получает список всех складов
     *
     * @return \Illuminate\Database\Eloquent\Collection Коллекция всех складов
     */
    public function getAllWarehouses() {
        return Warehouse::all();
    }

    /**
     * Получает список всех товаров
     *
     * @return \Illuminate\Database\Eloquent\Collection Коллекция всех товаров
     */
    public function getAllProducts() {
        return Product::all();
    }
}
