```markdown
# Laravel 10 Project

## Installation

1. Create a new Laravel 10 project
```
composer create-project --prefer-dist laravel/laravel new_repository "10.*"
```

2. **Install npm packages**
```
npm install
npm run dev
```

3. **Install Vite**
```
npm install vite
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
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer List</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold mb-6">Customer List</h1>

        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phone Number</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Address</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <!-- Example customer data, replace with your actual data -->
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">John Doe</td>
                        <td class="px-6 py-4 whitespace-nowrap">john@example.com</td>
                        <td class="px-6 py-4 whitespace-nowrap">123-456-7890</td>
                        <td class="px-6 py-4 whitespace-nowrap">123 Main St, Anytown, USA</td>
                    </tr>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">Jane Smith</td>
                        <td class="px-6 py-4 whitespace-nowrap">jane@example.com</td>
                        <td class="px-6 py-4 whitespace-nowrap">098-765-4321</td>
                        <td class="px-6 py-4 whitespace-nowrap">456 Elm St, Othertown, USA</td>
                    </tr>
                    <!-- Add more rows as needed -->
                </tbody>
            </table>
        </div>

        <!-- Pagination (if needed) -->
        <div class="mt-4 flex justify-center">
            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                <a href="#" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                    Previous
                </a>
                <a href="#" class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">
                    1
                </a>
                <a href="#" class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">
                    2
                </a>
                <a href="#" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                    Next
                </a>
            </nav>
        </div>
    </div>
</body>
</html>
```

