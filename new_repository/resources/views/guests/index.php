@extends('layout')

@section('content')
    <h1>Guest List</h1>
    <a href="{{ route('guests.create') }}">Add New Guest</a>

    @if (session('success'))
        <div>{{ session('success') }}</div>
    @endif

    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Check In</th>
                <th>Check Out</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($guests as $guest)
                <tr>
                    <td>{{ $guest->name }}</td>
                    <td>{{ $guest->email }}</td>
                    <td>{{ $guest->phone }}</td>
                    <td>{{ $guest->check_in }}</td>
                    <td>{{ $guest->check_out }}</td>
                    <td>
                        <a href="{{ route('guests.show', $guest->id) }}">View</a>
                        <a href="{{ route('guests.edit', $guest->id) }}">Edit</a>
                        <form action="{{ route('guests.destroy',
                            $guest->id) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit">Delete</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection

