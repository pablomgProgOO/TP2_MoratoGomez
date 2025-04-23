<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Repositories\UserRepository;
use App\Http\Resources\UserResource;

class UserController extends Controller
{
    protected $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * @OA\Get(
     *     path="/api/users/{id}",
     *     summary="Voir ses propres informations",
     *     tags={"Users"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Utilisateur trouvé"),
     *     @OA\Response(response=403, description="Accès refusé"),
     *     @OA\Response(response=404, description="Utilisateur introuvable")
     * )
     */
    public function show(Request $request, $id)
    {
        $user = $this->userRepository->findById($id);

        if (!$user) {
            return response()->json(['message' => 'Utilisateur introuvable'], 404);
        }

        if ($request->user()->id !== $user->id) {
            return response()->json(['message' => 'Accès refusé.'], 403);
        }

        return new UserResource($user);
    }

    /**
     * @OA\Patch(
     *     path="/api/users/{id}/password",
     *     summary="Mettre à jour son mot de passe",
     *     tags={"Users"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"password", "password_confirmation"},
     *             @OA\Property(property="password", type="string", example="newPassword123"),
     *             @OA\Property(property="password_confirmation", type="string", example="newPassword123")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Mot de passe mis à jour"),
     *     @OA\Response(response=403, description="Accès refusé"),
     *     @OA\Response(response=422, description="Erreur de validation")
     * )
     */
    public function updatePassword(Request $request, User $user)
    {
        if ($request->user()->id !== $user->id) {
            return response()->json(['message' => 'Accès refusé.'], 403);
        }

        $validated = $request->validate([
            'password' => 'required|string|min:6|confirmed',
            'password_confirmation' => 'required|string|min:6',
        ]);

        $this->userRepository->updatePassword($user, $validated['password']);

        return response()->json(['message' => 'Mot de passe mis à jour avec succès.'], 200);
    }
}
