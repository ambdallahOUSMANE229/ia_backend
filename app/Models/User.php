<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\Hasmany;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable =[
	'name',
	'email',
	'password',
];

    protected $hidden = [
	'password',
	'remember_token',
];
	
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }


public function addresses(): HasMany
{
    return $this->hasMany(Address::class);
}

public function carts(): HasMany
{
    return $this->hasMany(Cart::class);
}

public function orders(): HasMany
{
    return $this->hasMany(Order::class);
}

public function reviews(): HasMany
{
    return $this->hasMany(Review::class);
}
}
