@extends('layouts.app')

@section('title', 'Движения товаров')
@section('content')
    <h2>Движения товаров</h2>

    <form method="GET">
        <div class="form-group">
            <label>Склад:</label>
            <select name="warehouse_id">
                <option value="">Все</option>
                @foreach($warehouses as $warehouse)
                    <option value="{{ $warehouse->id }}"
                        {{ request('warehouse_id') == $warehouse->id ? 'selected' : '' }}>
                        {{ $warehouse->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label>Товар:</label>
            <select name="product_id">
                <option value="">Все</option>
                @foreach($products as $product)
                    <option value="{{ $product->id }}"
                        {{ request('product_id') == $product->id ? 'selected' : '' }}>
                        {{ $product->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label>Дата с:</label>
            <input type="date" name="date_from" value="{{ request('date_from') }}">
        </div>

        <div class="form-group">
            <label>Дата по:</label>
            <input type="date" name="date_to" value="{{ request('date_to') }}">
        </div>

        <button type="submit">Фильтровать</button>
    </form>

    <table>
        <thead>
        <tr>
            <th>Товар</th>
            <th>Склад</th>
            <th>Изменение</th>
            <th>Причина</th>
            <th>Заказ</th>
        </tr>
        </thead>
        <tbody>
        @foreach($movements as $movement)
            <tr>
                <td>{{ $movement->product->name }}</td>
                <td>{{ $movement->warehouse->name }}</td>
                <td>{{ $movement->change > 0 ? '+' : '' }}{{ $movement->change }}</td>
                <td>{{ $movement->reason }}</td>
                <td>
                    @if($movement->order_id)
                        <a href="{{ route('orders.show', $movement->order_id) }}">
                            #{{ $movement->order_id }}
                        </a>
                    @endif
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

    {{ $movements->appends(request()->query())->links() }}
@endsection
