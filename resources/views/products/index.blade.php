@extends('layouts.app')

@section('title', 'Товары с остатками')
@section('content')
    <h2>Товары</h2>
    <table>
        <thead>
        <tr>
            <th>ID</th>
            <th>Название</th>
            <th>Цена</th>
            <th>Остатки по складам</th>
        </tr>
        </thead>
        <tbody>
        @foreach($products as $product)
            <tr>
                <td>{{ $product->id }}</td>
                <td>{{ $product->name }}</td>
                <td>{{ number_format($product->price, 2) }}</td>
                <td>
                    <ul>
                        @foreach($product->warehouses as $warehouse)
                            <li>
                                Склад {{ $warehouse->name }}: <!-- Access warehouse name -->
                                {{ $warehouse->pivot->stock }} шт. <!-- Access pivot stock -->
                            </li>                        @endforeach
                    </ul>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endsection
