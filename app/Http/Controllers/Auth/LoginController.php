<?php

namespace App\Http\Controllers\Auth;

use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;

class LoginController extends Controller
{
    /**
     * Login de usuario
     *
     * Validaciones requeridas para el login:
     * - email: obligatorio, formato email, debe existir en users
     * - password: obligatorio, mínimo 8 caracteres, al menos una mayúscula y un símbolo especial
     * - company_id: obligatorio, debe existir en la tabla companies
     */
    public function login(LoginRequest $request)
    {
        // 1. Validar los datos recibidos (ya lo hace LoginRequest)
        $validated = $request->validated();

        // 2. Intentar autenticar al usuario con email, password y company_id
        //    - JWTAuth::attempt() recibe solo email y password por defecto
        $credentials = [
            'email' => $validated['email'],
            'password' => $validated['password'],
        ];

        if (!$token = JWTAuth::attempt($credentials)) {
            // Si las credenciales son incorrectas, devolver error 401
            return response()->json([
                'success' => false,
                'message' => 'Credenciales inválidas.'
            ], 401);
        }

        // 3. Validar que el usuario autenticado pertenezca a la company_id enviada
        $user = auth()->user();
        if ($user->company_id != $validated['company_id']) {
            // Si el company_id no coincide, invalidar el token y devolver error 401
            JWTAuth::invalidate($token);
            return response()->json([
                'success' => false,
                'message' => 'Empresa inválida.'
            ], 401);
        }

        // 4. Si la autenticación y company_id son correctos, devolver token
        return response()->json([
            'success' => true,
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => JWTAuth::factory()->getTTL() * 60, // por standard son 60 minutos
        ]);
    }
}
