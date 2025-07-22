<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Модель для промежуточной таблицы stocks (остатки товаров на складах)
 *
 * @property int $product_id Идентификатор товара
 * @property int $warehouse_id Идентификатор склада
 * @property int $stock Количество товара на складе
 * @property-read Product $product Связанный товар
 * @property-read Warehouse $warehouse Связанный склад
 */
class Stock extends Pivot
{
    /**
     * Название таблицы в базе данных
     * @var string
     */
    protected $table = 'stocks';

    /**
     * Отключение временных меток
     * Промежуточная таблица не нуждается в created_at/updated_at
     * @var bool
     */
    public $timestamps = false;

    /**
     * Разрешенные для массового заполнения атрибуты
     * @var array
     */
    protected $fillable = [
        'product_id',    // Внешний ключ на товар
        'warehouse_id', // Внешний ключ на склад
        'stock'         // Количество товара на складе
    ];

    /**
     * При необходимости можно добавить:
     * - Каст атрибутов (casts)
     * - Валидацию
     * - Дополнительные методы
     * - События (observers)
     */
}
