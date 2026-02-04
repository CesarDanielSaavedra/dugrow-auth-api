<?php

namespace App\Models\Auth;


// Importaciones necesarias para el modelo User:
// - Authenticatable: clase base para usuarios autenticables en Laravel
// - HasFactory: permite usar factories para tests y seeders
// - Notifiable: habilita notificaciones (emails, etc)
// - SoftDeletes: permite borrado lógico (no elimina físicamente)
// - JWTSubject: interfaz para compatibilidad JWT puro (tymon/jwt-auth)
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Tymon\JWTAuth\Contracts\JWTSubject;

// Importación del modelo Role para la relación
use App\Models\Auth\Role;
// Importación explícita del modelo Company para la relación belongsTo (separation-ready)
use App\Models\Auth\Company;

// El modelo User extiende Authenticatable para heredar la funcionalidad de usuario autenticable de Laravel
// e implementa JWTSubject para ser compatible con JWT puro (tymon/jwt-auth)
class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable, SoftDeletes;

    // $fillable define los atributos que pueden asignarse masivamente (mass assignment)
    // Es una medida de seguridad para evitar que se asignen campos no deseados al crear/actualizar usuarios
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

    // $hidden oculta estos atributos cuando el modelo se convierte a array o JSON
    // Así, nunca se exponen el password ni el remember_token en respuestas API
    protected $hidden = [
        'password',
        'remember_token',
    ];

    // casts define cómo se deben convertir ciertos atributos al accederlos
    // 'email_verified_at' se trata como datetime, 'password' se hashea automáticamente
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Métodos requeridos por JWTSubject para JWT puro:
    // Devuelve el identificador único del usuario para el token JWT (normalmente el id)
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    // Devuelve un array de claims personalizados para el JWT (puedes agregar info extra si lo deseas)
    public function getJWTCustomClaims()
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
            'company_id' => $this->company_id,
            'role_id' => $this->role_id,
            'role_name' => $this->role->name ?? 'user',
        ];
    }
}
