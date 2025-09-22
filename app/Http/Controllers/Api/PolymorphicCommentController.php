<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PolymorphicComment;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class PolymorphicCommentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $comments = PolymorphicComment::with('commentable')->get();
        return response()->json([
            'success' => true,
            'data' => $comments,
            'message' => 'List all Polymorphic Comments'
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'content' => 'required|string',
            'commentable_id' => 'required|integer',
            'commentable_type' => 'required|string|in:App\Models\Image,App\Models\Video',
        ]);

        $comment = PolymorphicComment::create($validated);

        return response()->json([
            'success' => true,
            'data' => $comment,
            'message' => 'Polymorphic Comment Created Successfully'
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $comment = PolymorphicComment::with('commentable')->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $comment,
            'message' => 'Show Polymorphic Comment by id ' . $id
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'content' => 'required|string',
        ]);

        try {
            $comment = PolymorphicComment::findOrFail($id)->update($validated);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $comment,
            'message' => 'Polymorphic Comment Updated Successfully'
        ], 201);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $comment = PolymorphicComment::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 404);
        }

        $comment = PolymorphicComment::destroy($id);

        return response()->json([
            'success' => true,
            'data' => $comment,
            'message' => 'Destroy Polymorphic Comment by id ' . $id
        ]);
    }

    /**
     * Obtener comentarios de una imagen específica
     */
    public function getImageComments(string $imageId)
    {
        try {

            $image = \App\Models\Image::findOrFail($imageId);

            $comments = $image->polymorphicComments;
            
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $comments,
            'message' => 'Comments for Image ' . $imageId
        ]);
    }

    /**
     * Obtener comentarios de un video específico
     */
    public function getVideoComments(string $videoId)
    {
        try {

            $video = \App\Models\Video::findOrFail($videoId);

            $comments = $video->polymorphicComments;

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $comments,
            'message' => 'Comments for Video ' . $videoId
        ]);
    }

    /**
     * Crear comentario para una imagen
     */
    public function storeImageComment(Request $request, string $imageId)
    {
        $validated = $request->validate([
            'content' => 'required|string',
        ]);

        try {
            
            $image = \App\Models\Image::findOrFail($imageId);

            $comment = $image->polymorphicComments()->create([
                'content' => $validated['content']
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $comment,
            'message' => 'Comment created for Image ' . $imageId
        ], 201);
    }

    /**
     * Crear comentario para un video
     */
    public function storeVideoComment(Request $request, string $videoId)
    {
        $validated = $request->validate([
            'content' => 'required|string',
        ]);

        try {
            $video = \App\Models\Video::findOrFail($videoId);

            $comment = $video->polymorphicComments()->create([
                'content' => $validated['content']
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $comment,
            'message' => 'Comment created for Video ' . $videoId
        ], 201);
    }
}
