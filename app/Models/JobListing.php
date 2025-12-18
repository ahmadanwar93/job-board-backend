<?php

namespace App\Models;

use App\Enums\JobListingStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobListing extends Model
{
    /** @use HasFactory<\Database\Factories\JobFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'location',
        'salary_range',
        'is_remote',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'is_remote' => 'boolean',
            'status' => JobListingStatus::class, // Cast to enum
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function applications()
    {
        return $this->hasMany(Application::class);
    }

    // TODO: check if scope is necessary
    public function scopePublished($query)
    {
        return $query->where('status', JobListingStatus::PUBLISHED);
    }

    public function scopeForEmployer($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}
