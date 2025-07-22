@extends('layouts.app')

@section('title', 'Список заказов')
@section('content')
    <h2>Заказы</h2>

    <form method="GET">
        <div class="form-group">
            <label>Статус:</label>
            <select name="status">
                <option value="">Все</option>
                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Активные</option>
                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Завершенные</option>
                <option value="canceled" {{ request('status') == 'canceled' ? 'selected' : '' }}>Отмененные</option>
            </select>
        </div>
        <button type="submit">Фильтровать</button>
    </form>

    <table>
        <thead>
        <tr>
            <th>ID</th>
            <th>Клиент</th>
            <th>Статус</th>
            <th>Дата создания</th>
            <th>Действия</th>
        </tr>
        </thead>
        <tbody>
        @foreach($orders as $order)
            <tr>
                <td>{{ $order->id }}</td>
                <td>{{ $order->customer }}</td>
                <td>{{ $order->status }}</td>
                <td>{{ $order->created_at->format('d.m.Y H:i') }}</td>
                <td>
                    <a href="{{ route('orders.show', $order) }}">Просмотр</a>
                    @if($order->status == 'active')
                        <a href="{{ route('orders.edit', $order) }}">Редактировать</a>
                        <form action="{{ route('orders.complete', $order) }}" method="POST" style="display:inline;">
                            @csrf
                            <button type="submit">Завершить</button>
                        </form>
                        <form action="{{ route('orders.cancel', $order) }}" method="POST" style="display:inline;">
                            @csrf
                            <button type="submit">Отменить</button>
                        </form>
                    @endif
                    @if($order->status == 'canceled')
                        <form action="{{ route('orders.resume', $order) }}" method="POST" style="display:inline;">
                            @csrf
                            <button type="submit">Возобновить</button>
                        </form>
                    @endif
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

    {{ $orders->appends(request()->query())->links() }}

    <a href="{{ route('orders.create') }}">Создать новый заказ</a>
@endsection
