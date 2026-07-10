<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // 管理者ゲート: User モデルに is_admin カラムが追加されたら自動的に機能する。
        // 現時点では全ユーザーが false になるため、addAdminResponse は管理者のみ利用可能。
        Gate::define('admin', function ($user) {
            return (bool) ($user->is_admin ?? false);
        });
    }
}
