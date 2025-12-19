<?php

namespace App\Models;

use App\Enums\ApplicationStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    /** @use HasFactory<\Database\Factories\ApplicationFactory> */
    use HasFactory;

    protected $fillable = [
        'job_listing_id',
        'user_id',
        'message',
        'status',
        'viewed_at',
        'created_at'
    ];

    protected function casts(): array
    {
        return [
            'viewed_at' => 'datetime',
            'status' => ApplicationStatus::class
        ];
    }

    public function jobListing()
    {
        return $this->belongsTo(JobListing::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
