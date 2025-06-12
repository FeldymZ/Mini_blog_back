<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Articles",
 *     description="Opérations sur les articles"
 * )
 */
class ArticleController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/articles",
     *     summary="Liste des articles",
     *     tags={"Articles"},
     *     @OA\Response(
     *         response=200,
     *         description="Succès",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="current_page", type="integer"),
     *             @OA\Property(property="data", type="array", @OA\Items(
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="user_id", type="integer"),
     *                 @OA\Property(property="titre", type="string"),
     *                 @OA\Property(property="slug", type="string"),
     *                 @OA\Property(property="contenu", type="string"),
     *                 @OA\Property(property="image_url", type="string"),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             )),
     *             @OA\Property(property="last_page", type="integer"),
     *             @OA\Property(property="total", type="integer")
     *         )
     *     )
     * )
     */
    public function index()
    {
        return Article::latest()->paginate(10);
    }

    /**
     * @OA\Post(
     *     path="/api/articles",
     *     summary="Créer un nouvel article",
     *     tags={"Articles"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"titre", "contenu"},
     *                 @OA\Property(property="titre", type="string"),
     *                 @OA\Property(property="contenu", type="string"),
     *                 @OA\Property(property="image", type="file")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=201, description="Article créé"),
     *     security={{"sanctum":{}}}
     * )
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'titre' => 'required|string|min:5|max:255',
            'contenu' => 'required|string|min:20',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
        ]);

        if ($request->hasFile('image') && !$request->file('image')->isValid()) {
            return response()->json(['message' => 'Fichier image invalide'], 400);
        }

        $slug = Str::slug($validated['titre']) . '-' . uniqid();

        $image_url = null;
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('articles', 'public');
            $image_url = Storage::disk('public')->url($path);
        }

        $article = Article::create([
            'titre' => $validated['titre'],
            'slug' => $slug,
            'contenu' => $validated['contenu'],
            'image_url' => $image_url,
            'user_id' => $request->user()->id,
        ]);

        return response()->json($article, 201);
    }

    /**
     * @OA\Get(
     *     path="/api/articles/{id}",
     *     summary="Afficher un article",
     *     tags={"Articles"},
     *     @OA\Parameter(
     *         name="id", in="path", required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Détails de l'article"),
     *     @OA\Response(response=404, description="Article introuvable")
     * )
     */
    public function show($id)
    {
        $article = Article::findOrFail($id);
        return response()->json($article);
    }

    /**
     * @OA\Put(
     *     path="/api/articles/{id}",
     *     summary="Modifier un article",
     *     tags={"Articles"},
     *     @OA\Parameter(
     *         name="id", in="path", required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="titre", type="string"),
     *                 @OA\Property(property="contenu", type="string"),
     *                 @OA\Property(property="image", type="file")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=200, description="Article modifié"),
     *     @OA\Response(response=403, description="Non autorisé"),
     *     security={{"sanctum":{}}}
     * )
     */
    public function update(Request $request, $id)
    {
        $article = Article::findOrFail($id);

        if ($request->user()->id !== $article->user_id) {
            return response()->json(['message' => 'Accès refusé'], 403);
        }

        $validated = $request->validate([
            'titre' => 'required|string|min:5|max:255',
            'contenu' => 'required|string|min:20',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
        ]);

        if ($request->hasFile('image') && !$request->file('image')->isValid()) {
            return response()->json(['message' => 'Fichier image invalide'], 400);
        }

        $article->titre = $validated['titre'];
        $article->slug = Str::slug($validated['titre']) . '-' . uniqid();
        $article->contenu = $validated['contenu'];

        if ($request->hasFile('image')) {
            if ($article->image_url) {
                $ancienneImage = str_replace('/storage/', '', parse_url($article->image_url, PHP_URL_PATH));
                Storage::disk('public')->delete($ancienneImage);
            }
            $path = $request->file('image')->store('articles', 'public');
            $article->image_url = Storage::disk('public')->url($path);
        }

        $article->save();

        return response()->json($article);
    }

    /**
     * @OA\Delete(
     *     path="/api/articles/{id}",
     *     summary="Supprimer un article",
     *     tags={"Articles"},
     *     @OA\Parameter(
     *         name="id", in="path", required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Article supprimé"),
     *     @OA\Response(response=403, description="Non autorisé"),
     *     security={{"sanctum":{}}}
     * )
     */
    public function destroy(Request $request, $id)
    {
        $article = Article::findOrFail($id);

        if ($request->user()->id !== $article->user_id) {
            return response()->json(['message' => 'Accès refusé'], 403);
        }

        if ($article->image_url) {
            $ancienneImage = str_replace('/storage/', '', parse_url($article->image_url, PHP_URL_PATH));
            Storage::disk('public')->delete($ancienneImage);
        }

        $article->delete();

        return response()->json(['message' => 'Article supprimé']);
    }
}
