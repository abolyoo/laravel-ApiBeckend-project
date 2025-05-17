<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ClaimController;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\ClaimReactionController;

// مسیر دریافت اطلاعات کاربر با احراز هویت sanctum
Route::middleware('auth:sanctum')->get('/users/{id}', function ($id) {
    $user = User::findOrFail($id);
    return response()->json([
        'id' => $user->id,
        'name' => $user->name,
        'avatar' => $user->avatar, 
    ]);
});

// مسیر ثبت نام کاربر
Route::post('/register', [AuthController::class, 'register']);

// مسیر ورود کاربر
Route::post('/login', [AuthController::class, 'login']);

// دریافت لیست ادعاها با احراز هویت
Route::middleware('auth:sanctum')->get('/claims', [ClaimController::class, 'index']);

// به‌روزرسانی وضعیت ادعا با احراز هویت
Route::middleware('auth:sanctum')->patch('/claims/{id}/status', [ClaimController::class, 'updateStatus']);

// دریافت واکنش‌های یک ادعا (بدون نیاز به احراز هویت)
Route::get('/claims/{claim}/reactions', [ClaimReactionController::class, 'index']);

// اضافه کردن واکنش به ادعا با احراز هویت
Route::middleware('auth:sanctum')->post('/claims/{claim}/reactions', [ClaimReactionController::class, 'store']);

// حذف واکنش ادعا (بدون نیاز به احراز هویت)
Route::delete('/claims/{claim}/reactions', [ClaimReactionController::class, 'destroy']);


/*
|--------------------------------------------------------------------------
| مسیرهای عمومی (بدون نیاز به احراز هویت)
|--------------------------------------------------------------------------
*/

// مسیر ورود با اعتبارسنجی دستی و ایجاد توکن
Route::post('/login', function (Request $request) {
    $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    $user = User::where('email', $request->email)->first();

    if (! $user || ! Hash::check($request->password, $user->password)) {
        throw ValidationException::withMessages([
            'email' => ['The credentials are incorrect.'],
        ]);
    }

    return response()->json([
        'token' => $user->createToken('api-token')->plainTextToken,
        'user' => $user
    ]);
});


/*
|--------------------------------------------------------------------------
| مسیرهای محافظت شده (middleware احراز هویت sanctum)
|--------------------------------------------------------------------------
*/

// به‌روزرسانی وضعیت ادعا (تکرار تعریف قبلی)
Route::patch('/claims/{claim}/status', [ClaimController::class, 'updateStatus']);

// گروه مسیرهای محافظت شده با middleware auth:sanctum
Route::middleware('auth:sanctum')->group(function () {
    
    // مسیر دریافت اطلاعات کاربر فعلی
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // منابع CRUD برای ادعاها
    Route::apiResource('claims', ClaimController::class);

    // به‌روزرسانی وضعیت ادعا (تکرار مجدد)
    Route::patch('/claims/{claim}/status', [ClaimController::class, 'updateStatus']);
});
