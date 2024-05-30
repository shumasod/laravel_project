@extends('layout')

@section('content')
    <h1>Edit Guest</h1>

    @if ($errors->any())
        <div>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('guests.update', $guest->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div>
            <label>Name</label>
            <input type="text" name="name" value="{{ old('name', $guest->name) }}">
        </div>
        <div>
            <label>Email</label>
            <input type="email" name="email" value="{{ old('email', $guest->email) }}">
        </div>
        <div>
            <label>Phone</label>
            <input type="text" name="phone" value="{{ old('phone', $guest->phone) }}">
        </div>
        <div>
            <label>Check In</label>
            <input type="date" name="check_in" value="{{ old('check_in', $guest->check_in) }}">
        </div>
        <div>
            <label>Check Out</label>
            <input type="date" name="check_out" value="{{ old('check_out', $guest->check_out) }}">
        </div>
        <button type="submit">Save</button>
    </form>
@endsection
