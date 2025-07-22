<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Модель заказа
 *
 * @property int $id Идентификатор заказа
 * @property string $customer Имя заказчика
 * @property int $warehouse_id Идентификатор склада
 * @property \Illuminate\Support\Carbon $created_at Дата создания заказа
 * @property \Illuminate\Support\Carbon|null $completed_at Дата завершения заказа
 * @property string $status Статус заказа (active, completed, canceled)
 * @property-read \Illuminate\Database\Eloquent\Collection|OrderItem[] $items Товары в заказе
 * @property-read Warehouse|null $warehouse Склад заказа
 */
class Order extends Model
{
    use HasFactory;

    /**
     * Отключаем автоматическое управление временными метками
     * @var bool
     */
    public $timestamps = false;

    /**
     * Атрибуты, которые должны быть преобразованы
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime',    // Преобразование в объект Carbon
        'completed_at' => 'datetime'   // Преобразование в объект Carbon
    ];

    /**
     * Атрибуты, которые разрешены для массового назначения
     * @var array
     */
    protected $guarded = [];

    /**
     * Связь "один ко многим" с моделью OrderItem (товары заказа)
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Связь "принадлежит" с моделью Warehouse (склад)
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }
}
