<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Модель склада/хранилища товаров
 *
 * @property int $id Идентификатор склада
 * @property string $name Наименование склада
 * @property string|null $address Адрес склада
 * @property string|null $contact_info Контактная информация
 * @property \Illuminate\Support\Carbon $created_at Дата создания
 * @property \Illuminate\Support\Carbon $updated_at Дата обновления
 * @property-read \Illuminate\Database\Eloquent\Collection|Product[] $products Товары на складе
 * @property-read \Illuminate\Database\Eloquent\Collection|Stock[] $stocks Информация о количестве товаров
 */
class Warehouse extends Model
{
    use HasFactory;

    /**
     * Разрешение массового назначения для всех атрибутов
     * @var array
     */
    protected $guarded = [];

    /**
     * Связь "многие ко многим" с товарами через промежуточную таблицу stocks
     *
     * Позволяет получить все товары, имеющиеся на складе, с указанием их количества
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function products()
    {
        return $this->belongsToMany(
            Product::class,
            'stocks'
        )->withPivot(
            'stock'
        )->using(
            Stock::class
        );
    }
}
