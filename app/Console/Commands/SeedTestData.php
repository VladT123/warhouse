<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Stock;
use App\Models\Warehouse;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;

class SeedTestData extends Command
{
    /**
     * Название и сигнатура консольной команды
     *
     * @var string
     */
    protected $signature = 'db:seed-test-data';

    /**
     * Описание консольной команды
     *
     * @var string
     */
    protected $description = 'Fill database with test data';

    /**
     * Основной метод выполнения команды
     * Создает тестовые данные: склады, товары и остатки на складах
     *
     * @return void
     */
    public function handle()
    {
        // 1. Создаем тестовые склады (5 штук)
        $warehouses = Warehouse::factory()->count(5)->create();

        // 2. Создаем тестовые товары (20 штук)
        $products = Product::factory()->count(20)->create();

        // 3. Создаем остатки товаров на каждом складе
        foreach ($warehouses as $warehouse) {
            $stockData = []; // Массив для хранения данных об остатках

            // Для каждого товара генерируем случайный остаток (от 50 до 200)
            foreach ($products as $product) {
                $stockData[$product->id] = ['stock' => rand(50, 200)];
            }

            // Синхронизируем остатки товаров для текущего склада
            $warehouse->products()->sync($stockData);
        }

        // Выводим сообщение об успешном завершении
        $this->info('Тестовые данные успешно созданы!');
    }
}
