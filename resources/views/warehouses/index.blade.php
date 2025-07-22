@extends('layouts.app')

@section('title', 'Список складов')
@section('content')
    <h2>Склады</h2>
    <table>
        <thead>
        <tr>
            <th>ID</th>
            <th>Название</th>
        </tr>
        </thead>
        <tbody>
        @foreach($warehouses as $warehouse)
            <tr>
                <td>{{ $warehouse->id }}</td>
                <td>{{ $warehouse->name }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endsection
