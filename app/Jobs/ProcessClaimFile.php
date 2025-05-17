<?php

namespace App\Jobs;

use App\Models\ClaimModel;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ProcessClaimFile implements ShouldQueue
{
    use Dispatchable, Queueable;

    // نگهداری نمونه ادعا برای پردازش
    protected $claim;

    // سازنده که نمونه ادعا را دریافت می‌کند
    public function __construct(ClaimModel $claim)
    {
        $this->claim = $claim;
    }

    // متد اجرای پردازش پس از اجرای صف
    public function handle()
    {
        // ثبت لاگ برای پردازش فایل ادعا با شناسه مشخص
        Log::info("Processing file for claim ID: " . $this->claim->id);
    }
}