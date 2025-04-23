<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Http\Resources\UserResource;
/**
 * @OA\Info(
 *     title="TP2 API de films",
 *     version="1.0.0",
 *     description="API REST pour l’authentification TP2.1"
 * )
 */
class AuthController extends Controller{
    

    /**
 * @OA\Post(
 *     path="/api/signup",
 *     summary="Enregistre un nouvel utilisateur",
 *     description="Crée un utilisateur. Ne retourne pas de token. Limite de 5 requêtes/minute.",
 *     tags={"Auth"},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"login", "email", "email_confirmation", "password", "password_confirmation", "first_name", "last_name"},
 *             @OA\Property(property="login", type="string", example="johndoe"),
 *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
 *             @OA\Property(property="email_confirmation", type="string", format="email", example="john@example.com"),
 *             @OA\Property(property="password", type="string", format="password", example="secret123"),
 *             @OA\Property(property="password_confirmation", type="string", format="password", example="secret123"),
 *             @OA\Property(property="first_name", type="string", example="John"),
 *             @OA\Property(property="last_name", type="string", example="Doe")
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Utilisateur enregistré",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Utilisateur enregistré"),
 *         )
 *     ),
 *     @OA\Response(response=422, description="Erreur de validation"),
 *     @OA\Response(response=429, description="Trop de requêtes")
 * )
 */

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'login' => 'required|string|unique:users,login',
            'email' => 'required|email|confirmed',
            'email_confirmation' => 'required|email',
            'password' => 'required|string|min:6|confirmed',
            'password_confirmation' => 'required|string|min:6',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Erreur de validation',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = User::create([
            'login' => $request->login,
            'email' => $request->email,
            'password' => Hash::make($request->password), // bcrypt
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
        ]);

        return response()->json([
            'message' => 'Utilisateur enregistré',
            'user' => new UserResource($user)
        ], 201);
    }
/**
 * @OA\Post(
 *     path="/api/signin",
 *     summary="Connecte un utilisateur",
 *     description="Retourne un token et les infos utilisateur. Limite de 5 requêtes/minute.",
 *     tags={"Auth"},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"login", "password"},
 *             @OA\Property(property="login", type="string", example="johndoe"),
 *             @OA\Property(property="password", type="string", format="password", example="secret123")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Connexion complete",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Connexion complete"),
 *             @OA\Property(property="token", type="string", example="teyJeEVAaSRVFrSrb")
 *         )
 *     ),
 *     @OA\Response(response=401, description="Login ou/et mot de passe invalide"),
 *     @OA\Response(response=429, description="Trop de requêtes")
 * )
 */

    public function login(Request $request)
    {
        $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('login', $request->login)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Login ou/et mot de passe invalide'
            ], 401);
        }

        $token = $user->createToken('authToken');

        return response()->json([
            'message' => 'Connexion complete',
            'user' => new UserResource($user),
            'token' => $token->plainTextToken
        ],200);
    }
/**
 * @OA\Post(
 *     path="/api/signout",
 *     summary="Déconnecte un utilisateur",
 *     description="Révoque tous les tokens de l’utilisateur. Nécessite un token Bearer. Limite de 5 requêtes/minute.",
 *     tags={"Auth"},
 *     security={{"sanctum":{}}},
 *     @OA\Response(
 *         response=204,
 *         description="Déconnexion réussie (no content)"
 *     ),
 *     @OA\Response(response=401, description="Non authentifié"),
 *     @OA\Response(response=429, description="Trop de requêtes")
 * )
 */

    public function logout(Request $request)
{
    $user = $request->user();

    if ($user) {
        $user->tokens()->delete(); 
        return response()->noContent();
    }

    return response()->json([
        'message' => 'Utilisateur non authentifié'
    ], 401);
}

}
