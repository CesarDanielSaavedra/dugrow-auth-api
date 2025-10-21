<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;

class RegisterController extends Controller
{
    /**
     * Registrar un nuevo usuario
     *
     * Forma esperada de la request (JSON):
     * {
     *   "name": "Juan Perez",           // requerido
     *   "email": "juan@email.com",      // requerido, único
     *   "password": "secreto123",       // requerido, mínimo 8 caracteres
     *   "company_id": 1,                 // opcional, debe existir si se envía
     *   "role_id": 2                      // opcional, debe existir si se envía
     * }
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(RegisterRequest $request)
    {
        // 1. Validar los datos recibidos
        // Usamos el validador de Laravel para asegurar integridad y seguridad

        // Validaciones requeridas para el registro:
        // - name: obligatorio, string, máximo 255 caracteres
        // - email: obligatorio, formato email, único en users
        // - password: obligatorio, mínimo 8 caracteres, al menos una mayúscula y un símbolo especial
        // - company_id: obligatorio, debe existir en la tabla companies
        // - role_id: opcional, si no se envía se asigna el rol por defecto (usuario común)
        // Usamos el RegisterRequest para validar y obtener los datos validados
        $validated = $request->validated();

        // Si la validación falla, Laravel responde automáticamente con 422 y los errores

        // 2. Asignar rol por defecto si no se envía (por ejemplo, 'user')
        if (empty($validated['role_id'])) {
            // Buscar el ID del rol 'user' (ajusta el nombre si tu rol por defecto es otro)
            $defaultRole = \App\Models\Auth\Role::where('name', 'user')->first();
            $validated['role_id'] = $defaultRole ? $defaultRole->id : null;
        }

        // 3. Crear el usuario (el password se hashea automáticamente por el cast en el modelo)
        $user = \App\Models\Auth\User::create($validated);

        // 4. Devolver respuesta JSON estandarizada (sin password)
        // Puedes personalizar los datos devueltos según tu frontend
        return response()->json([
            'success' => true,
            'message' => 'Usuario registrado correctamente.',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'company_id' => $user->company_id,
                'role' => $user->role ? $user->role->name : null,
            ]
        ], 201);
    }
}
