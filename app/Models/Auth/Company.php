<?php

namespace App\Models\Auth;


// Importación necesaria para el modelo Company:
// - Model: clase base para modelos Eloquent
use Illuminate\Database\Eloquent\Model;

// El modelo Company representa una empresa (multi-tenant)
// Extiende Model (no usa SoftDeletes por ahora, agregar si es necesario)
// El modelo Company representa una empresa (multi-tenant)
// Extiende Model y usa SoftDeletes para permitir borrado lógico
use Illuminate\Database\Eloquent\SoftDeletes;
class Company extends Model
{
    use SoftDeletes;

    // Nombre de la tabla asociada (opcional si sigue convención)
    protected $table = 'companies';

    // $fillable define los atributos que pueden asignarse masivamente
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
