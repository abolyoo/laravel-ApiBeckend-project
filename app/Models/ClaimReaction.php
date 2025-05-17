<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\ClaimModel;

class ClaimReaction extends Model
{
    // نام جدول مرتبط با این مدل
    
    protected $table = 'claim_reactions';

    // فیلدهای قابل پر شدن (mass assignable)
    protected $fillable = ['claim_id', 'user_id', 'emoji'];

    // تعریف رابطه برعکس با مدل ClaimModel (هر واکنش متعلق به یک ادعاست)
    public function claim()
    {
        return $this->belongsTo(ClaimModel::class, 'claim_id');
    }

    // تعریف رابطه برعکس با مدل User (هر واکنش متعلق به یک کاربر است)
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
