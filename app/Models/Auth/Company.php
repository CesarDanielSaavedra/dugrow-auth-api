<?php

namespace App\Models\Auth;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $table = 'companies';

    protected $fillable = [
        'name',
        'cuit',
        'email',
        'phone',
        'address',
    ];

    // Relación: una empresa tiene muchos usuarios
    public function users()
    {
        return $this->hasMany(User::class);
    }
}
