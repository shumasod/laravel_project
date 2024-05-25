```markdown
# Laravel 10 Project

## Installation

1. **Create a new Laravel 10 project**
```bash
composer create-project --prefer-dist laravel/laravel new_repository "10.*"
```

2. **Install npm packages**
```bash
npm install
npm run dev
```

3. **Install Vite**
```bash
npm install -g vite
```

4. **Install Tailwind CSS**
```bash
npm install -D tailwindcss postcss autoprefixer
```

5. **Install required Laravel packages**
```bash
composer require laravel/ui
```

6. **Set up the database**
```bash
php artisan migrate
```

7. **Configure Laravel 10 routing**

Add the following routes to `routes/web.php`:

```php
Route::get('/', function () {
    return view('welcome');
});

Route::resource('users', 'UserController');
```

8. **Create a Blade template file**

Create a file `resources/views/users/index.blade.php` with the following content:

```html
@extends('layouts.app')

@section('content')
<h1>Customer List</h1>

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
            <td>{{ $user->address }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection
```

