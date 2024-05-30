@extends('layout')

@section('content')
    <h1>Guest Details</h1>

    <div>
        <label>Name:</label>
        <span>{{ $guest->name }}</span>
    </div>
    <div>
        <label>Email:</label>
        <span>{{ $guest->email }}</span>
    </div>
    <div>
        <label>Phone:</label>
        <span>{{ $guest->phone }}</span>
    </div>
    <div>
        <label>Check In:</label>
        <span>{{ $guest->check_in }}</span>
    </div>
    <div>
        <label>Check Out:</label>
        <span>{{ $guest->check_out }}</span>
    </div>

    <a href="{{ route('guests.edit', $guest->id) }}">Edit</a>
    <form action="{{ route('guests.destroy', $guest->id) }}" method="POST" style="display:inline;">
        @csrf
        @method('DELETE')
        <button type="submit">Delete</button>
    </form>
    <a href="{{ route('guests.index') }}">Back to List</a>
@endsection
