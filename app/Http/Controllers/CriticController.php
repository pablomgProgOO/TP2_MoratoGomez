<?php 
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Film;
use App\Repositories\CriticRepository;
use App\Models\Critic;

class CriticController extends Controller
{
    protected $criticRepository;

    public function __construct(CriticRepository $criticRepository)
    {
        $this->criticRepository = $criticRepository;
    }

    /**
     * @OA\Post(
     *     path="/api/films/{id}/critics",
     *     summary="Créer une critique pour un film (1 critique max par utilisateur)",
     *     tags={"Critics"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"score"},
     *             @OA\Property(property="score", type="integer", example=8),
     *             @OA\Property(property="comment", type="string", example="Excellent film !")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Critique créée"),
     *     @OA\Response(response=403, description="Déjà critiqué"),
     *     @OA\Response(response=404, description="Film introuvable")
     * )
     */
    public function store(Request $request, Film $film)
    {
        $userId = $request->user()->id;

        if ($this->criticRepository->hasAlreadyCriticized($userId, $film->id)) {
            return response()->json([
                'message' => 'Vous avez déjà critiqué ce film.'
            ], 403);
        }

        $validated = $request->validate([
            'score' => 'required|integer|min:0|max:10',
            'comment' => 'nullable|string',
        ]);

        $critic = $this->criticRepository->create([
            'user_id' => $userId,
            'film_id' => $film->id,
            'score' => $validated['score'],
            'comment' => $validated['comment'] ?? null,
        ]);

        return response()->json($critic, 201);
    }
}
