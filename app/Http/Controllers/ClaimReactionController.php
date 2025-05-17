<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ClaimReaction;
use App\Models\ClaimModel;

class ClaimReactionController extends Controller
{
    // نمایش خلاصه واکنش‌ها برای یک ادعا
    public function index(ClaimModel $claim)
    {
        $summary = $claim->reactions()
            ->select('emoji', \DB::raw('count(*) as count'))
            ->groupBy('emoji')
            ->get();

        return response()->json($summary);
    }

    // افزودن یک واکنش جدید برای ادعا توسط کاربر
    public function store(Request $request, ClaimModel $claim)
    {   
        $validated = $request->validate([
            'emoji' => 'required|max:10' // اعتبارسنجی فیلد ایموجی
        ]);
    
        $userId = auth()->id(); // گرفتن شناسه کاربر فعلی
    
        // چک کردن اینکه آیا این واکنش قبلاً توسط همین کاربر ثبت شده یا نه
        $existing = ClaimReaction::where('claim_id', $claim->id)
                                 ->where('user_id', $userId)
                                 ->where('emoji', $validated['emoji'])
                                 ->first();
    
        if ($existing) {
            return response()->json(['message' => 'You already reacted with this emoji'], 409);
        }
    
        // ثبت واکنش جدید
        ClaimReaction::create([
            'claim_id' => $claim->id,
            'user_id' => $userId,
            'emoji' => $validated['emoji']
        ]);
    
        return response()->json(['message' => 'Reaction added']);
    }
    
    // حذف واکنش کاربر به ادعا
    public function destroy(Request $request, ClaimModel $claim)
    {
        $validated = $request->validate([
            'emoji' => 'required|max:10' // اعتبارسنجی فیلد ایموجی
        ]);
    
        // پیدا کردن واکنش مورد نظر برای حذف
        $reaction = ClaimReaction::where('claim_id', $claim->id)
                                 ->where('user_id', auth()->id())
                                 ->where('emoji', $request->emoji)
                                 ->first();
    
        if ($reaction) {
            $reaction->delete();
            return response()->json(['message' => 'Reaction removed']);
        }
    
        return response()->json(['message' => 'No reaction found'], 404);
    }
}