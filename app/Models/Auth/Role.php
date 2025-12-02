<?php

namespace App\Models\Auth;


// Importaciones necesarias para el modelo Role:
// - Model: clase base para modelos Eloquent
// - SoftDeletes: permite borrado lógico (no elimina físicamente)
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

// Importación del modelo User para la relación
use App\Models\Auth\User;

// El modelo Role representa los roles de usuario (admin, user, etc)
// Extiende Model y usa SoftDeletes para permitir borrado lógico
// El modelo Role representa los roles de usuario (admin, user, etc)
// Extiende Model y usa SoftDeletes para permitir borrado lógico
class Role extends Model
{
    use SoftDeletes;

    // Nombre de la tabla asociada (opcional si sigue convención)
    protected $table = 'roles';

    // $fillable define los atributos que pueden asignarse masivamente
    protected $fillable = [
        'name',
        'description',
    ];

    // Relación: un rol tiene muchos usuarios
    public function users()
    {
        return $this->hasMany(User::class, 'role_id');
    }
}
