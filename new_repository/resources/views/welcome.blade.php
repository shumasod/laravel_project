<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GMS 2018 - 顧客管理システム</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

    <style>
        body { font-size: 0.9rem; }
        .navbar { background-color: #f8f9fa; border-bottom: 1px solid #dee2e6; }
        .sidebar { width: 50px; background-color: #f8f9fa; border-right: 1px solid #dee2e6; }
        .main-content { margin-left: 50px; }
        .filter-section { background-color: #e9ecef; padding: 10px; margin-bottom: 10px; }
        .data-table { font-size: 0.8rem; }
        .data-table th { background-color: #e9ecef; }
        .status-icon { width: 10px; height: 10px; border-radius: 50%; display: inline-block; margin-right: 5px; }
        .status-green { background-color: #28a745; }
        .status-red { background-color: #dc3545; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">GMS 2018</a>
            <div class="d-flex">
                <button class="btn btn-outline-primary btn-sm me-2">新規作成</button>
                <div class="dropdown">
                    <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-user"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                        <li><a class="dropdown-item" href="#">プロフィール</a></li>
                        <li><a class="dropdown-item" href="#">ログアウト</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <div class="d-flex">
        <div class="sidebar">
            <!-- Sidebar icons -->
            <div class="d-flex flex-column align-items-center py-3">
                <a href="#" class="mb-3"><i class="fas fa-home"></i></a>
                <a href="#" class="mb-3"><i class="fas fa-users"></i></a>
                <a href="#" class="mb-3"><i class="fas fa-cog"></i></a>
            </div>
        </div>

        <div class="main-content flex-grow-1 p-3">
            <div class="filter-section">
                <div class="row g-2">
                    <div class="col-md-2">
                        <input type="text" class="form-control form-control-sm" placeholder="顧客名">
                    </div>
                    <div class="col-md-2">
                        <select class="form-select form-select-sm">
                            <option>ステータス</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="text" class="form-control form-control-sm" placeholder="担当者">
                    </div>
                    <div class="col-md-2">
                        <input type="text" class="form-control form-control-sm" placeholder="電話番号">
                    </div>
                    <div class="col-md-2">
                        <input type="text" class="form-control form-control-sm" placeholder="メールアドレス">
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-primary btn-sm w-100">検索</button>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-hover data-table">
                    <thead>
                        <tr>
                            <th>編集</th>
                            <th>顧客ID</th>
                            <th>ステータス</th>
                            <th>顧客名</th>
                            <th>担当者</th>
                            <th>電話番号</th>
                            <th>メールアドレス</th>
                            <th>最終更新日</th>
                            <th>備考</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><i class="fas fa-edit"></i></td>
                            <td>C001</td>
                            <td><span class="status-icon status-green"></span> アクティブ</td>
                            <td>株式会社サンプル</td>
                            <td>山田太郎</td>
                            <td>03-1234-5678</td>
                            <td>info@sample.com</td>
                            <td>2023-04-01</td>
                            <td>長期取引先</td>
                        </tr>
                        <tr>
                            <td><i class="fas fa-edit"></i></td>
                            <td>C002</td>
                            <td><span class="status-icon status-red"></span> 非アクティブ</td>
                            <td>有限会社テスト</td>
                            <td>鈴木花子</td>
                            <td>06-9876-5432</td>
                            <td>contact@test.co.jp</td>
                            <td>2023-03-15</td>
                            <td>新規顧客</td>
                        </tr>
                        <!-- Add more rows as needed -->
                    </tbody>
                </table>
            </div>

            <nav aria-label="Page navigation">
                <ul class="pagination justify-content-center">
                    <li class="page-item disabled"><a class="page-link" href="#">前へ</a></li>
                    <li class="page-item active"><a class="page-link" href="#">1</a></li>
                    <li class="page-item"><a class="page-link" href="#">2</a></li>
                    <li class="page-item"><a class="page-link" href="#">3</a></li>
                    <li class="page-item"><a class="page-link" href="#">次へ</a></li>
                </ul>
            </nav>
        </div>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>