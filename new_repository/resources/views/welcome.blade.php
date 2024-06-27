<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ソリッド商事 - 取引先一覧</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

    <style>
        .sidebar {
            background-color: #1e3a8a;
            color: white;
            height: 100vh;
        }
        .main-content {
            background-color: #f3f4f6;
        }
        .table-header {
            background-color: #e5e7eb;
        }
        .status-icon {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 5px;
        }
        .status-active { background-color: #10b981; }
        .status-inactive { background-color: #ef4444; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-2 d-none d-md-block sidebar">
                <div class="position-sticky pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item mb-2">
                            <a class="nav-link text-white" href="#">
                                <i class="fas fa-home me-2"></i> ホーム
                            </a>
                        </li>
                        <li class="nav-item mb-2">
                            <a class="nav-link text-white active" href="#">
                                <i class="fas fa-users me-2"></i> 取引先一覧
                            </a>
                        </li>
                        <!-- Add more menu items as needed -->
                    </ul>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-10 ms-sm-auto px-md-4 main-content">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">取引先一覧</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-file-export me-1"></i> エクスポート
                            </button>
                        </div>
                        <button type="button" class="btn btn-sm btn-primary">
                            <i class="fas fa-plus me-1"></i> 新規登録
                        </button>
                    </div>
                </div>

                <!-- Filter and search -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="input-group">
                            <input type="text" class="form-control" placeholder="検索...">
                            <button class="btn btn-outline-secondary" type="button">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-6 text-end">
                        <select class="form-select d-inline-block w-auto">
                            <option>すべての取引先</option>
                            <!-- Add more options as needed -->
                        </select>
                        <button class="btn btn-outline-primary ms-2">
                            <i class="fas fa-filter me-1"></i> 詳細検索
                        </button>
                    </div>
                </div>

                <!-- Customer list table -->
                <div class="table-responsive">
                    <table class="table table-striped table-sm">
                        <thead class="table-header">
                            <tr>
                                <th><input type="checkbox"></th>
                                <th>取引先ID</th>
                                <th>ステータス</th>
                                <th>区分</th>
                                <th>取引先名</th>
                                <th>電話番号</th>
                                <th>都道府県</th>
                                <th>市区町村</th>
                                <th>登録日</th>
                                <th>アクション</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><input type="checkbox"></td>
                                <td>2179</td>
                                <td><span class="status-icon status-active"></span> 活動中</td>
                                <td>法人</td>
                                <td>ニッポン ABC</td>
                                <td>03-1234-5678</td>
                                <td>東京都</td>
                                <td>港区1-1</td>
                                <td>2017/12/09</td>
                                <td>
                                    <button class="btn btn-sm btn-outline-secondary">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <!-- Add more rows as needed -->
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <nav aria-label="Page navigation" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <li class="page-item disabled">
                            <a class="page-link" href="#" tabindex="-1" aria-disabled="true">前へ</a>
                        </li>
                        <li class="page-item active"><a class="page-link" href="#">1</a></li>
                        <li class="page-item"><a class="page-link" href="#">2</a></li>
                        <li class="page-item"><a class="page-link" href="#">3</a></li>
                        <li class="page-item">
                            <a class="page-link" href="#">次へ</a>
                        </li>
                    </ul>
                </nav>
            </main>
        </div>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>