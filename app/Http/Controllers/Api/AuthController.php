<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    // ثبت نام کاربر جدید
    public function register(Request $request)
    {
        // اعتبارسنجی ورودی‌ها
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users', // ایمیل باید یکتا باشد
            'password' => 'required|string|min:6',
        ]);

        // ایجاد کاربر جدید و رمزگذاری پسورد
        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => bcrypt($request->password),
        ]);

        // پاسخ موفقیت ثبت نام
        return response()->json(['message' => 'ثبت‌نام با موفقیت انجام شد'], 201);
    }

    // ورود کاربر و صدور توکن
    public function login(Request $request)
    {
        // اعتبارسنجی ورودی‌ها
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required'
        ]);

        // جستجوی کاربر با ایمیل وارد شده
        $user = User::where('email', $request->email)->first();

        // بررسی صحت پسورد
        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['اطلاعات ورود نادرست است.'],
            ]);
        }

        // ایجاد توکن دسترسی شخصی برای API
        $token = $user->createToken('api_token')->plainTextToken;

        // بازگرداندن توکن به کلاینت
        return response()->json(['token' => $token], 200);
    }

}