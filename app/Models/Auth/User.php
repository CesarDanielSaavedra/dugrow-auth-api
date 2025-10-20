<?php

namespace App\Models\Auth;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Models\Auth\Role;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'role_id',
        'password',
        'company_id',
        'email_verified_at',
        'remember_token',
    ];

    // Relación: un usuario tiene un rol
    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }
    // Relación: un usuario pertenece a una empresa
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

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
}
