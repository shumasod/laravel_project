@extends('layouts.app')

@section('content')
    <h1>Customer List</h1>

    <?php
        $totalCustomers = count($users);
        $dateNow = date('Y-m-d');
    ?>

    <p>Total customers: {{ $totalCustomers }}</p>
    <p>Today's date: {{ $dateNow }}</p>

    <table class="table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Phone Number</th>
                <th>Address</th>
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
                        {{ $user->prefecture }} <!-- Prefecture -->
                        {{ $user->city }} <!-- City -->
                        {{ $user->town }} <!-- Town -->
                        {{ $user->building }} <!-- Building -->
                        {{ $user->remarks }} <!-- Remarks -->
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection