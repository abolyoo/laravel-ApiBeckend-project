<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ClaimModel extends Model
{

    use HasFactory;

    // نام جدول مرتبط با این مدل 

    protected $table = 'claim';

    // فیلدهای قابل پر شدن (mass assignable)

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'file_path',
        'status',
    ];

    // تعریف رابطه یک به چند با مدل ClaimReaction
    
    public function reactions()
    {
        return $this->hasMany(ClaimReaction::class, 'claim_id');
    }
    
}