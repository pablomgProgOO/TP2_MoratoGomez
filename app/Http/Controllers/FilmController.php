<?php 
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Film;
use App\Repositories\FilmRepository;

/**
 * @OA\Schema(
 *     schema="Film",
 *     title="Film",
 *     description="Film model",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="title", type="string", example="ACADEMY DINOSAUR"),
 *     @OA\Property(property="description", type="string", example="A Epic Drama of a Feminist And a Mad Scientist who must Battle a Teacher in The Canadian Rockies"),
 *     @OA\Property(property="release_year", type="integer", example=2006),
 *     @OA\Property(property="language_id", type="integer", example=1, description="Foreign key referring to Language"),
 *     @OA\Property(property="length", type="integer", example=86, description="Film length in minutes"),
 *     @OA\Property(property="rating", type="string", example="PG"),
 *     @OA\Property(property="special_features", type="string", example="Deleted Scenes,Behind the Scenes"),
 *     @OA\Property(property="image", type="string", example=""),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2006-02-15 15:03:42"),
 * )
 */

class FilmController extends Controller
{
    protected $filmRepository;

    public function __construct(FilmRepository $filmRepository)
    {
        $this->filmRepository = $filmRepository;
    }

    /**
     * @OA\Post(
     *     path="/api/films",
     *     summary="Créer un film (admin seulement)",
     *     tags={"Films"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title", "release_year", "length", "description", "rating", "special_features", "image", "language_id"},
     *             @OA\Property(property="title", type="string", maxLength=50),
     *             @OA\Property(property="release_year", type="integer", example=2024),
     *             @OA\Property(property="length", type="integer"),
     *             @OA\Property(property="description", type="string"),
     *             @OA\Property(property="rating", type="string", maxLength=5),
     *             @OA\Property(property="special_features", type="string", maxLength=200),
     *             @OA\Property(property="image", type="string", maxLength=40),
     *             @OA\Property(property="language_id", type="integer")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Film créé"),
     *     @OA\Response(response=403, description="Accès refusé")
     * )
     */
    public function store(Request $request)
    {
        if ($request->user()->role_id !== 2) {
            return response()->json(['message' => 'Accès interdit.'], 403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:50',
            'release_year' => 'required|digits:4|integer|min:1900|max:' . now()->year,
            'length' => 'required|integer|min:1',
            'description' => 'required|string',
            'rating' => 'required|string|max:5',
            'special_features' => 'required|string|max:200',
            'image' => 'required|string|max:40',
            'language_id' => 'required|exists:languages,id',
        ]);

        $film = $this->filmRepository->create($validated);

        return response()->json($film, 201);
    }

    /**
     * @OA\Put(
     *     path="/api/films/{id}",
     *     summary="Modifier un film (admin seulement)",
     *     tags={"Films"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Film")
     *     ),
     *     @OA\Response(response=200, description="Film modifié"),
     *     @OA\Response(response=403, description="Accès refusé")
     * )
     */
    public function update(Request $request, Film $film)
    {
        if ($request->user()->role_id !== 2) {
            return response()->json(['message' => 'Accès interdit.'], 403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:50',
            'release_year' => 'required|digits:4|integer|min:1900|max:' . now()->year,
            'length' => 'required|integer|min:1',
            'description' => 'required|string',
            'rating' => 'required|string|max:5',
            'special_features' => 'required|string|max:200',
            'image' => 'required|string|max:40',
            'language_id' => 'required|exists:languages,id',
        ]);

        $film = $this->filmRepository->update($film, $validated);

        return response()->json($film);
    }

    /**
     * @OA\Delete(
     *     path="/api/films/{id}",
     *     summary="Supprimer un film (admin seulement)",
     *     tags={"Films"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=204, description="Film supprimé"),
     *     @OA\Response(response=403, description="Accès refusé")
     * )
     */
    public function destroy(Request $request, Film $film)
    {
        if ($request->user()->role_id !== 2) {
            return response()->json(['message' => 'Accès interdit.'], 403);
        }

        $this->filmRepository->delete($film);

        return response()->noContent();
    }
}
