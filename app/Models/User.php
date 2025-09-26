<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'phone',
        'sns_id',
        'sns_driver',
        'name',
        'role_id',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the role that owns the user.
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Get the shop associated with the user.
     */
    public function shop(): HasOne
    {
        return $this->hasOne(Shop::class);
    }

    /**
     * Get the news created by the user.
     */
    public function news(): HasMany
    {
        return $this->hasMany(News::class, 'created_by');
    }

    /**
     * Get the jobs created by the user.
     */
    public function jobs(): HasMany
    {
        return $this->hasMany(Job::class, 'created_by');
    }

    /**
     * Get the reviews created by the user.
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class, 'created_by');
    }
}
