<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer List</title>

    <!-- Tailwind CSS -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss/dist/tailwind.min.css" rel="stylesheet">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">

    <!-- Custom CSS -->
    <style>
        /* Add your custom CSS styles here */
    </style>
</head>
<body class="bg-gray-100">
    @extends('layouts.app')

    @section('content')
        <div class="container mx-auto py-8">
            <h1 class="text-3xl font-bold mb-4">Customer List</h1>

            <?php
                $totalCustomers = count($users);
                $dateNow = date('Y-m-d');
            ?>

            <div class="mb-6">
                <p class="text-lg">Total customers: {{ $totalCustomers }}</p>
                <p class="text-lg">Today's date: {{ $dateNow }}</p>
            </div>

            <div class="overflow-x-auto">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr class="bg-gray-200">
                            <th class="px-4 py-2 border">Name</th>
                            <th class="px-4 py-2 border">Email</th>
                            <th class="px-4 py-2 border">Phone Number</th>
                            <th class="px-4 py-2 border">Address</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $user)
                            <tr>
                                <td class="px-4 py-2 border">{{ $user->name }}</td>
                                <td class="px-4 py-2 border">{{ $user->email }}</td>
                                <td class="px-4 py-2 border">{{ $user->phone_number }}</td>
                                <td class="px-4 py-2 border">
                                    {{ $user->address }}<br>
                                    {{ $user->prefecture }} <!-- Prefecture --><br>
                                    {{ $user->city }} <!-- City --><br>
                                    {{ $user->town }} <!-- Town --><br>
                                    {{ $user->building }} <!-- Building --><br>
                                    {{ $user->remarks }} <!-- Remarks -->
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endsection

    <!-- Bootstrap JS -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <!-- Tailwind JS (if needed) -->
    <script src="https://cdn.jsdelivr.net/npm/tailwindcss/dist/tailwind.min.js"></script>

    <!-- Custom JavaScript -->
    <script>
        // Add your custom JavaScript code here
    </script>
</body>
</html>