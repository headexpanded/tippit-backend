<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use JetBrains\PhpStorm\ArrayShape;
use Laravel\Sanctum\HasApiTokens;
use Spatie\OneTimePasswords\Models\Concerns\HasOneTimePasswords;
use Illuminate\Database\Eloquent\Builder;
use Laravel\Sanctum\NewAccessToken;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasOneTimePasswords;
    use Notifiable;

    /** @use HasFactory<UserFactory> */

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'username',
        'email',
        'password',
        'supported_team_id',
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
        'supported_team_id' => "string",
        'last_login_at' => "string",
        'email_reminders_enabled' => "string",
        'is_admin' => "string",
        'passkey_credentials' => "string"
    ])] protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'supported_team_id' => 'integer',
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
    public function roundStatistics(): HasMany
    {
        return $this->hasMany(RoundUserStatistics::class);
    }


    /**
     * Get the team that the user supports.
     */
    public function supportedTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'supported_team_id');
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
    public function leagues(): BelongsToMany
    {
        return $this->belongsToMany(League::class)
            ->withTimestamps()
            ->withPivot('joined_at');
    }

    /**
     * @return HasMany
     */
    public function createdLeagues(): HasMany
    {
        return $this->hasMany(League::class, 'created_by');
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

    /**
     * @return HasMany
     */
    public function tokens(): HasMany
    {
        return $this->hasMany(PersonalAccessToken::class, 'tokenable_id')
            ->where('tokenable_type', static::class);
    }

    /**
     * @param  string  $name
     * @param  array  $abilities
     *
     * @return NewAccessToken
     */
    public function createToken(string $name, array $abilities = ['*']): NewAccessToken
    {
        return $this->tokens()->create([
            'name' => $name,
            'token' => hash('sha256', $plainTextToken = Str::random(64)),
            'abilities' => $abilities,
        ]);
    }

    /**
     * @param  Builder  $query
     * @param  string  $column
     * @param $value
     *
     * @return Builder
     */
    public function scopeWhere(Builder $query, string $column, $value): Builder
    {
        return $query->where($column, $value);
    }

    /**
     * @param  string  $role
     *
     * @return bool
     */
    public function hasRole(string $role): bool
    {
        return $this->is_admin && $role === 'admin';
    }

    /**
     * Set the user's password.
     *
     * @param string $value
     * @return void
     */
    public function setPasswordAttribute($value): void
    {
        $this->attributes['password'] = bcrypt($value);
    }

}
