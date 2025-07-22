<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Модель товара/продукта
 *
 * @property int $id Идентификатор товара
 * @property string $name Наименование товара
 * @property string $article Артикул товара
 * @property string|null $description Описание товара
 * @property \Illuminate\Support\Carbon $created_at Дата создания
 * @property \Illuminate\Support\Carbon $updated_at Дата обновления
 * @property-read \Illuminate\Database\Eloquent\Collection|Warehouse[] $warehouses Склады с наличием товара
 * @property-read \Illuminate\Database\Eloquent\Collection|Stock[] $stocks Информация о наличии на складах
 */
class Product extends Model
{
    use HasFactory;

    /**
     * Разрешение массового назначения для всех атрибутов
     * @var array
     */
    protected $guarded = [];

    /**
     * Связь "многие ко многим" со складами через промежуточную таблицу stocks
     *
     * Определяет на каких складах присутствует данный товар и его количество
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function warehouses()
    {
        return $this->belongsToMany(
            Warehouse::class,
            'stocks'
        )->withPivot(
            'stock'
        )->using(
            Stock::class
        );
    }
}
