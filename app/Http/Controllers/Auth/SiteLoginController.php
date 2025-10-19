<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class SiteLoginController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'user'     => ['required', 'string'],
            'password' => ['required', 'string'],
            'remember' => ['nullable', 'boolean'],
        ]);

        $userInput = (string) $request->input('user');
        $password  = (string) $request->input('password');
        $remember  = (bool)   $request->boolean('remember');

        // Xác định cột đăng nhập linh hoạt: email | username | name | tai_khoan (nếu có)
        if (filter_var($userInput, FILTER_VALIDATE_EMAIL) && Schema::hasColumn('users', 'email')) {
            $field = 'email';
        } elseif (Schema::hasColumn('users', 'username')) {
            $field = 'username';
        } elseif (Schema::hasColumn('users', 'name')) {
            $field = 'name';
        } elseif (Schema::hasColumn('users', 'tai_khoan')) {
            $field = 'tai_khoan';
        } elseif (Schema::hasColumn('users', 'email')) {
            // fallback
            $field = 'email';
        } else {
            // Nếu users table không có các cột trên, báo lỗi thân thiện
            return back()->withErrors(['auth' => 'Không xác định được cột đăng nhập trên bảng users.'])->withInput();
        }

        $credentials = [
            $field     => $userInput,
            'password' => $password,
        ];

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();
            // đưa vào admin (Filament) sau khi đăng nhập
            return redirect()->intended('/admin');
        }

        return back()
            ->withErrors(['auth' => 'Sai thông tin đăng nhập.'])
            ->withInput($request->only('user', 'remember'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}
