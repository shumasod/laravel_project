<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use App\Models\User; // Userモデルを使う場合、必要に応じて変更してください

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    public function getUsers()
    {
        // 例：ユーザーモデルから全てのユーザーを取得する
        $users = User::all();
        return response()->json($users);
    }

    public function getUser($id)
    {
        // 例：特定のIDのユーザーを取得する
        $user = User::findOrFail($id);
        return response()->json($user);
    }

    public function createUser(Request $request)
    {
        // 例：新しいユーザーを作成する
        $user = new User();
        $user->name = $request->input('name');
        $user->email = $request->input('email');
        $user->password = bcrypt($request->input('password')); // パスワードのハッシュ化
        $user->save();
        return response()->json($user, 201);
    }

    public function updateUser(Request $request, $id)
    {
        // 例：特定のIDのユーザーを更新する
        $user = User::findOrFail($id);
        $user->name = $request->input('name');
        $user->email = $request->input('email');
        $user->password = bcrypt($request->input('password')); // パスワードのハッシュ化
        $user->save();
        return response()->json($user);
    }

    public function deleteUser($id)
    {
        // 例：特定のIDのユーザーを削除する
        $user = User::findOrFail($id);
        $user->delete();
        return response()->json('User deleted successfully');
    }
}
