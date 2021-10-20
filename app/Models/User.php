<?php

namespace App\Models;

use App\Models\Post;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Notifications\Notifiable;
use App\Notifications\NewUserVerification;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /** Relationships */

    /**
     * Get all of the posts for the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    /** MUTATORS */

    /**
     * Set User Password
     * 
     * @param string $password
     * @return void
     */
    public function setPasswordAttribute(string $password): void
    {
        $this->attributes['password'] = Hash::make($password);
    }

    /**
     * Get User account verification link
     * 
     * @return string
     */
    public function getVerifyAccountUrlAttribute(): string
    {
        return env('APP_API_URL') . '/verify/' . $this->email_verification_token;
    }

    /** Custom Methods */

    /**
     * Send user email verification
     * 
     * @return void
     */
    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new NewUserVerification());
    }

    /**
     * Mark the given user's email as verified.
     *
     * @return bool
     */
    public function markEmailAsVerified(): bool
    {
        return $this->forceFill([
            'is_active' => 1,
            'email_verified_at' => $this->freshTimestamp(),
        ])->save();
    }

    /**
     * Generate user token
     * 
     * @return string
     */
    public function generateToken(): string
    {
        return $this->createToken(config('app.name'))->plainTextToken;
    }
}
