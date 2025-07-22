<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Модель элемента заказа (позиция в заказе)
 *
 * @property int $id Идентификатор позиции
 * @property int $order_id Идентификатор заказа
 * @property int $product_id Идентификатор товара
 * @property int $count Количество товара
 * @property float $price Цена товара на момент заказа
 * @property-read Order $order Родительский заказ
 * @property-read Product $product Связанный товар
 */
class OrderItem extends Model
{
    use HasFactory;

    /**
     * Отключение автоматического управления временными метками
     * Не используем поля created_at и updated_at
     * @var bool
     */
    public $timestamps = false;

    /**
     * Разрешение массового назначения для всех атрибутов
     * @var array
     */
    protected $guarded = [];

    /**
     * Связь "принадлежит" с моделью Order (заказ)
     * Каждая позиция принадлежит одному заказу
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Связь "принадлежит" с моделью Product (товар)
     * Каждая позиция ссылается на конкретный товар
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
