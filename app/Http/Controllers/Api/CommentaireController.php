<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Commentaire;
use App\Models\Article;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

class CommentaireController extends Controller
{

    /**
 * @OA\Get(
 *     path="/api/articles/{id}/commentaires",
 *     summary="Lister les commentaires d’un article",
 *     tags={"Commentaires"},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID de l'article",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Liste des commentaires",
 *         @OA\JsonContent(type="array", @OA\Items(
 *             @OA\Property(property="id", type="integer"),
 *             @OA\Property(property="contenu", type="string"),
 *             @OA\Property(property="valide", type="boolean"),
 *             @OA\Property(property="article_id", type="integer"),
 *             @OA\Property(property="created_at", type="string", format="date-time")
 *         ))
 *     )
 * )
 */

    // Liste des commentaires validés pour un article
    public function index($articleId)
    {
        $commentaires = Commentaire::where('article_id', $articleId)
            ->where('approved', true)
            ->latest()
            ->get();

        return response()->json($commentaires);
    }


    /**
 * @OA\Post(
 *     path="/api/commentaires",
 *     summary="Créer un commentaire",
 *     tags={"Commentaires"},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"contenu", "article_id"},
 *             @OA\Property(property="contenu", type="string", example="Très bon article !"),
 *             @OA\Property(property="article_id", type="integer", example=1)
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Commentaire créé"
 *     )
 * )
 */

    // Créer un commentaire (non validé par défaut)
    public function store(Request $request)
    {
        $validated = $request->validate([
            'article_id' => 'required|exists:articles,id',
            'auteur_nom' => 'required|string|max:255',
            'contenu' => 'required|string',
        ]);

        $commentaire = Commentaire::create($validated);

        return response()->json([
            'message' => 'Commentaire en attente de validation',
            'commentaire' => $commentaire
        ], 201);
    }


    /**
 * @OA\Put(
 *     path="/api/commentaires/{id}/valider",
 *     summary="Valider un commentaire",
 *     tags={"Commentaires"},
 *     security={{"sanctum":{}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID du commentaire",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Commentaire validé"
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Commentaire introuvable"
 *     )
 * )
 */

    // Valider un commentaire (admin)
    public function valider($id)
    {
        $commentaire = Commentaire::findOrFail($id);
        $commentaire->approved = true;
        $commentaire->save();

        return response()->json(['message' => 'Commentaire validé']);
    }


    /**
 * @OA\Delete(
 *     path="/api/commentaires/{id}",
 *     summary="Supprimer un commentaire",
 *     tags={"Commentaires"},
 *     security={{"sanctum":{}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID du commentaire",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Commentaire supprimé"
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Commentaire introuvable"
 *     )
 * )
 */

    // Supprimer un commentaire
    public function destroy($id)
    {
        $commentaire = Commentaire::findOrFail($id);
        $commentaire->delete();

        return response()->json(['message' => 'Commentaire supprimé']);
    }
}
