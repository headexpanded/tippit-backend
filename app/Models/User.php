<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use JetBrains\PhpStorm\ArrayShape;
use Laravel\Sanctum\HasApiTokens;
use Spatie\OneTimePasswords\HasOneTimePasswords;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, HasOneTimePasswords;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'google_id',
        'passkey_credentials',
        'email_reminders_enabled',
        'last_login_at',
        'is_admin',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'passkey_credentials',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    #[ArrayShape([
        'email_verified_at' => "string",
        'password' => "string",
        'last_login_at' => "string",
        'email_reminders_enabled' => "string",
        'is_admin' => "string",
        'passkey_credentials' => "string"
    ])] protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_login_at' => 'datetime',
            'email_reminders_enabled' => 'boolean',
            'is_admin' => 'boolean',
            'passkey_credentials' => 'array',
        ];
    }

    /**
     * @return HasOne
     */
    public function statistics(): HasOne
    {
        return $this->hasOne(UserStatistics::class);
    }

    /**
     * @return HasMany
     */
    public function predictions(): HasMany
    {
        return $this->hasMany(Prediction::class);
    }

    /**
     * @return BelongsToMany
     */
    public function miniLeagues(): BelongsToMany
    {
        return $this->belongsToMany(MiniLeague::class)
            ->withTimestamps()
            ->withPivot('joined_at');
    }

    /**
     * @return HasMany
     */
    public function createdMiniLeagues(): HasMany
    {
        return $this->hasMany(MiniLeague::class, 'created_by');
    }

    /**
     * Get the entity's notifications.
     *
     * @return HasMany
     */
    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * @return HasMany
     */
    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }
}
