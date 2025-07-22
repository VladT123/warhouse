<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Модель движения товарных запасов (инвентаризационные операции)
 *
 * @property int $id Идентификатор движения
 * @property int $product_id Идентификатор товара
 * @property int $warehouse_id Идентификатор склада
 * @property int|null $order_id Идентификатор заказа (если связано с заказом)
 * @property int $change Изменение количества (положительное или отрицательное)
 * @property string $reason Причина движения (например: 'order_created', 'inventory_adjustment')
 * @property \Illuminate\Support\Carbon $created_at Дата создания записи
 * @property-read Product $product Связанный товар
 * @property-read Warehouse|null $warehouse Связанный склад
 * @property-read Order|null $order Связанный заказ
 */
class StockMovement extends Model
{
    use HasFactory;

    /**
     * Отключение временных меток updated_at
     * @var bool
     */
    public $timestamps = false;

    /**
     * Разрешение массового назначения для всех атрибутов
     * @var array
     */
    protected $guarded = [];

    /**
     * Связь с моделью Product (товар)
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Связь с моделью Warehouse (склад)
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Связь с моделью Order (заказ)
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
