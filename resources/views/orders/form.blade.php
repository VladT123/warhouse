@php
    $isEdit = isset($order);
    $route = $isEdit ? route('orders.update', $order) : route('orders.store');
    $title = $isEdit ? "Редактирование заказа #{$order->id}" : 'Создание заказа';
@endphp

@extends('layouts.app')

@section('title', $title)
@section('content')
    <h2>{{ $title }}</h2>

    <form method="POST" action="{{ $route }}">
        @csrf
        @if($isEdit) @method('PUT') @endif

        <div class="form-group">
            <label>Клиент:</label>
            <input type="text" name="customer" value="{{ old('customer', $order->customer ?? '') }}" required>
        </div>

        <div class="form-group">
            <label>Склад:</label>
            <select name="warehouse_id" required>
                @foreach($warehouses as $warehouse)
                    <option value="{{ $warehouse->id }}"
                        {{ ($order->warehouse_id ?? old('warehouse_id')) == $warehouse->id ? 'selected' : '' }}>
                        {{ $warehouse->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <h3>Позиции заказа:</h3>
        <div id="order-items">
            @if(old('items', isset($order) ? $order->items : []))
                @foreach(old('items', $order->items ?? []) as $index => $item)
                    <div class="order-item">
                        <div class="form-group">
                            <label>Товар:</label>
                            <select name="items[{{ $index }}][product_id]" required>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}"
                                        {{ $item['product_id'] == $product->id ? 'selected' : '' }}>
                                        {{ $product->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Количество:</label>
                            <input type="number" name="items[{{ $index }}][count]"
                                   value="{{ $item['count'] }}" min="1" required>
                        </div>
                        <button type="button" class="remove-item">Удалить</button>
                    </div>
                @endforeach
            @endif
        </div>

        <button type="button" id="add-item">Добавить позицию</button>
        <br><br>
        <button type="submit">{{ $isEdit ? 'Обновить' : 'Создать' }}</button>
    </form>

    <script>
        document.getElementById('add-item').addEventListener('click', function() {
            const container = document.getElementById('order-items');
            const index = container.children.length;
            const itemHTML = `
                <div class="order-item">
                    <div class="form-group">
                        <label>Товар:</label>
                        <select name="items[${index}][product_id]" required>
                            @foreach($products as $product)
            <option value="{{ $product->id }}">{{ $product->name }}</option>
                            @endforeach
            </select>
        </div>

        <div class="form-group">
            <label>Количество:</label>
            <input type="number" name="items[${index}][count]" min="1" required>
                    </div>
                    <button type="button" class="remove-item">Удалить</button>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', itemHTML);
        });

        document.addEventListener('click', function(e) {
            if(e.target.classList.contains('remove-item')) {
                e.target.closest('.order-item').remove();
            }
        });
    </script>
@endsection
