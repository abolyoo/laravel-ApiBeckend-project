<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ClaimModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use App\Jobs\ProcessClaimFile;


class ClaimController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // دریافت کاربر لاگین شده
        $user = auth()->user();
    
        // شروع کوئری دریافت ادعاها همراه با واکنش‌ها
        $claims = ClaimModel::with('reactions');
        
        // اگر کاربر ادمین نیست، فقط ادعاهای خودش را نشان بده
        if ($user->role !== 'admin') {
            $claims->where('user_id', $user->id);
        }
    
        // فیلتر بر اساس عنوان اگر ارسال شده باشد
        if ($request->has('title')) {
            $claims->where('title', 'like', '%' . $request->title . '%');
        }
    
        // فیلتر بر اساس وضعیت اگر ارسال شده باشد
        if ($request->has('status')) {
            $claims->where('status', $request->status);
        }
    
        // گرفتن نتایج مرتب شده به صورت جدیدترین‌ها
        $claims = $claims->latest()->get();
        
        // اجرای صف پردازش فایل ادعا برای هر ادعا
        $claims->each(fn($claim) => ProcessClaimFile::dispatch($claim));
    
        // تهیه داده‌های خروجی با اطلاعات کاربر و واکنش‌ها
        $claims = $claims->map(function ($claim) {
            $userId = $claim->user_id;
    
            // کش کردن اطلاعات کاربر برای 10 دقیقه
            $userInfo = Cache::remember("user-info-{$userId}", 600, function () use ($userId) {
                $response = Http::withToken(request()->bearerToken())
                    ->get(config('services.user_service.base_url') . "/users/{$userId}");
    
                // اگر پاسخ موفق بود، اطلاعات کاربر را برگردان
                if ($response->successful()) {
                    return $response->json();
                }
    
                // در غیر اینصورت، نام کاربر فعلی را برگردان به همراه آواتار خالی
                return ['name' => $user->name, 'avatar' => null];
            });
    
            // بازگرداندن آرایه‌ای از اطلاعات ادعا به همراه واکنش‌ها و اطلاعات کاربر
            return [
                'id' => $claim->id,
                'title' => $claim->title,
                'description' => $claim->description,
                'status' => $claim->status,
                'created_at' => $claim->created_at,
                'reactions' => $claim->reactions
                    ->groupBy('emoji')
                    ->map(fn($items) => count($items)),
                'user' => $userInfo,
            ];
        });
        
        // پاسخ JSON با داده‌های پردازش شده
        return response()->json($claims);
    }
    
    
    
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // اعتبارسنجی ورودی‌ها
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|required|max:1000',
            'file' => 'nullable|file|max:30240|mimes:jpg,jpeg,png,mp4,mp3,pdf,docx',
        ]);

        // مسیر فایل ذخیره شده را به صورت پیش‌فرض خالی قرار می‌دهیم
        $filePath = null;

        // اگر فایل ارسال شده است، ذخیره و نام آن را تعیین می‌کنیم
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $fileName = uniqid() . '.' . $file->getClientOriginalExtension();
            $filePath = $file->storeAs('claims/' . 1, $fileName, 'public'); 
        }

        // ایجاد رکورد جدید ادعا با اطلاعات ارسال شده و مسیر فایل (اگر وجود داشته باشد)
        $claim = ClaimModel::create([
            'user_id' => 1,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'file_path' => $filePath,
        ]);

        // بازگرداندن پاسخ JSON با داده ادعا ایجاد شده و کد وضعیت 201
        return response()->json($claim, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        // پیدا کردن ادعا با شناسه مشخص شده یا خطای 404 در صورت عدم وجود
        $claim = ClaimModel::findOrFail($id);
        
        // بازگرداندن ادعا به صورت JSON
        return response()->json($claim);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        // گرفتن کاربر لاگین شده
        $user = auth()->user();
    
        // پیدا کردن ادعایی که متعلق به کاربر است یا خطا در صورت عدم وجود
        $claim = ClaimModel::where('id', $id)->where('user_id', $user->id)->firstOrFail();
    
        // اعتبارسنجی ورودی‌ها
        $request->validate([
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'file' => 'nullable|file|max:30240', 
        ]);
    
        // حذف فایل قبلی در صورت وجود و ارسال فایل جدید
        if ($request->hasFile('file') && $claim->file_path) {
            if (Storage::disk('public')->exists($claim->file_path)) {
                Storage::disk('public')->delete($claim->file_path);
            }
        }
    
        // ذخیره فایل جدید اگر ارسال شده باشد
        if ($request->hasFile('file')) {
            $path = $request->file('file')->storeAs(
                'claims/' . $user->id,
                uniqid() . '.' . $request->file('file')->getClientOriginalExtension(),
                'public'
            );
            $claim->file_path = $path;
        }
    
        // بروزرسانی عنوان و توضیحات در صورت ارسال
        $claim->title = $request->input('title', $claim->title);
        $claim->description = $request->input('description', $claim->description);
        $claim->save();
    
        // بازگرداندن پاسخ JSON با پیام موفقیت و اطلاعات ادعا
        return response()->json([
            'message' => 'ادعا با موفقیت ویرایش شد.',
            'claim' => $claim,
        ]);
    }
    

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        // پیدا کردن ادعا یا خطا در صورت عدم وجود
        $claim = Claim::findOrFail($id);
    
        // بررسی اجازه حذف (ادمین یا مالک ادعا)
        if (auth()->user()->role !== 'admin' && $claim->user_id !== auth()->id()) {
            return response()->json(['message' => 'شما اجازه حذف این ادعا را ندارید.'], 403);
        }
    
        // حذف فایل ادعا در صورت وجود
        if ($claim->file_path && Storage::disk('public')->exists($claim->file_path)) {
            Storage::disk('public')->delete($claim->file_path);
        }
    
        // حذف رکورد ادعا
        $claim->delete();
    
        // بازگرداندن پیام موفقیت حذف
        return response()->json(['message' => 'ادعا با موفقیت حذف شد.']);
    }    
    

    public function updateStatus(Request $request, $id)
    {
        // اعتبارسنجی ورودی وضعیت
        $request->validate([
            'status' => 'required|in:pending,approved,rejected',
        ]);
    
        // پیدا کردن ادعا یا خطا در صورت عدم وجود
        $claim = Claim::findOrFail($id);
        
        // بروزرسانی وضعیت ادعا
        $claim->status = $request->status;
        $claim->save();
    
        // بازگرداندن پیام موفقیت و داده ادعا
        return response()->json([
            'message' => 'وضعیت با موفقیت به‌روزرسانی شد',
            'claim' => $claim
        ]);
    }
    
}
