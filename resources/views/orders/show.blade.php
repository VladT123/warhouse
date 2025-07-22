@extends('layouts.app')

@section('title', "Заказ #{$order->id}")
@section('content')
    <h2>Заказ #{{ $order->id }}</h2>

    <p><strong>Клиент:</strong> {{ $order->customer }}</p>
    <p><strong>Склад:</strong> {{ $order->warehouse->name }}</p>
    <p><strong>Статус:</strong> {{ $order->status }}</p>
    <p><strong>Дата создания:</strong> {{ $order->created_at->format('d.m.Y H:i') }}</p>

    @if($order->completed_at)
        <p><strong>Дата завершения:</strong> {{ $order->completed_at->format('d.m.Y H:i') }}</p>
    @endif

    <h3>Позиции:</h3>
    <table>
        <thead>
        <tr>
            <th>Товар</th>
            <th>Количество</th>
            <th>Цена</th>
            <th>Сумма</th>
        </tr>
        </thead>
        <tbody>
        @foreach($order->items as $item)
            <tr>
                <td>{{ $item->product->name }}</td>
                <td>{{ $item->count }}</td>
                <td>{{ number_format($item->product->price, 2) }}</td>
                <td>{{ number_format($item->count * $item->product->price, 2) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <a href="{{ route('orders.index') }}">Назад к списку</a>
@endsection
