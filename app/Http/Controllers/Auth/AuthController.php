<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use App\Models\Auth\User;
use App\Models\Auth\Role;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;

/**
 * AuthController
 *
 * Controlador "gordo" que concentra TODA la autenticación en un solo lugar:
 * login, registro, logout y datos del usuario autenticado.
 *
 * Usa Laravel Sanctum: cuando el usuario se loguea, emite un token que se
 * guarda en la tabla `personal_access_tokens`. En cada request protegida, el
 * front manda ese token en el header `Authorization: Bearer <token>`.
 */
class AuthController extends Controller
{
    /**
     * Login: valida credenciales y devuelve un token + el objeto user.
     *
     * Validaciones: las hace LoginRequest (email, password y company_id).
     */
    public function login(LoginRequest $request)
    {
        $validated = $request->validated();

        // 1. Buscar al usuario por email y verificar la contraseña a mano.
        //    Hash::check compara la contraseña en texto plano contra el hash guardado.
        $user = User::where('email', $validated['email'])->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Credenciales inválidas.',
            ], 401);
        }

        // 2. Validar que el usuario pertenezca a la company_id enviada.
        if ($user->company_id != $validated['company_id']) {
            return response()->json([
                'success' => false,
                'message' => 'Empresa inválida.',
            ], 401);
        }

        // 3. Emitir el token de Sanctum (se persiste en personal_access_tokens).
        $token = $user->createToken('auth')->plainTextToken;

        // 4. Devolver token + user para que el front lo guarde de una.
        return response()->json([
            'success'      => true,
            'access_token' => $token,
            'token_type'   => 'Bearer',
            'user'         => $this->userPayload($user),
        ]);
    }

    /**
     * Registro: crea el usuario y lo deja logueado (devuelve token + user).
     *
     * Validaciones: las hace RegisterRequest.
     */
    public function register(RegisterRequest $request)
    {
        $validated = $request->validated();

        // 1. Si no mandaron role_id, asignar el rol por defecto ('user').
        if (empty($validated['role_id'])) {
            $defaultRole = Role::where('name', 'user')->first();
            $validated['role_id'] = $defaultRole ? $defaultRole->id : null;
        }

        // 2. Crear el usuario (el password se hashea solo por el cast del modelo).
        $user = User::create($validated);

        // 3. Emitir token, igual que en login (auto-login tras registrarse).
        $token = $user->createToken('auth')->plainTextToken;

        return response()->json([
            'success'      => true,
            'message'      => 'Usuario registrado correctamente.',
            'access_token' => $token,
            'token_type'   => 'Bearer',
            'user'         => $this->userPayload($user),
        ], 201);
    }

    /**
     * Logout: invalida el token con el que se hizo la request.
     *
     * Con Sanctum esto es literal borrar la fila del token en la DB:
     * revocación instantánea (a diferencia de JWT, que no se podía).
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Sesión cerrada correctamente.',
        ]);
    }

    /**
     * Datos del usuario autenticado.
     *
     * Lo usa el front al recargar la página: pega acá con el token guardado
     * para revalidar que sigue vivo y traer los datos frescos del usuario.
     */
    public function user(Request $request)
    {
        return response()->json([
            'success' => true,
            'user'    => $this->userPayload($request->user()),
        ]);
    }

    /**
     * Recuperar contraseña.
     *
     * TODO (Parte C): requiere configurar envío de emails (SMTP).
     */
    public function recover(Request $request)
    {
        return response()->json([
            'success' => false,
            'message' => 'Recuperar contraseña todavía no está implementado (Parte C).',
        ], 501);
    }

    /**
     * Forma estándar del objeto user que devuelven login, register y user.
     * Un solo lugar para definirlo => las 3 respuestas quedan consistentes.
     */
    private function userPayload(User $user): array
    {
        return [
            'id'         => $user->id,
            'name'       => $user->name,
            'email'      => $user->email,
            'company_id' => $user->company_id,
            'role_id'    => $user->role_id,
            'role_name'  => $user->role?->name,
        ];
    }
}
