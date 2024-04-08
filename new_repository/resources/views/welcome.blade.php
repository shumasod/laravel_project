<!DOCTYPE html>
@extends('layouts.app')

@section('content')
    <h1>顧客一覧</h1>

    <table class="table">
        <thead>
            <tr>
                <th>氏名</th>
                <th>メールアドレス</th>
                <th>電話番号</th>
                <th>住所</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($users as $user)
                <tr>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>{{ $user->phone_number }}</td>
                    <td>
                        {{ $user->address }}<br>
                        {{ $user->prefecture }} <!-- 都道府県 -->
                        {{ $user->city }} <!-- 市区町村 -->
                        {{ $user->town }} <!-- 町域 -->
                        {{ $user->building }} <!-- 建物名・部屋番号 -->
                        {{ $user->remarks }} <!-- 備考欄 -->
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
