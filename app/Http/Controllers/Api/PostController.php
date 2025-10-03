<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class PostController extends Controller
{
    
    /** 
     * @OA\Get(
     *  path="/api/posts",
     *  summary="Obtneer lista de posts",
     *  tags={"Posts"},
     *  security={{"sanctum":{}}},     
     *  @OA\Response(
     *      response=200, 
     *      description="Lista de Posts Obtenida Exitosamente",
     *  @OA\JsonContent(
     *      type="object",
     *      @OA\Property(property="success", type="boolean", example="true"),
     *      @OA\Property(property="data", type="array", @OA\Items(type="object", 
     *      @OA\Property(property="id", type="integer", example="1"), 
     *      @OA\Property(property="title", type="string", example="Post 1"), 
     *      @OA\Property(property="content", type="string", example="Content 1"))),
     *      @OA\Property(property="created_at", type="string", example="2021-01-01 00:00:00"),
     *      @OA\Property(property="updated_at", type="string", example="2021-01-01 00:00:00"),
     *  )
     * ),
     * )
     * @OA\Response(
     *      response=401, 
     *      description="No autorizado",
     *  @OA\JsonContent(
     *      type="object",
     *      @OA\Property(property="success", type="boolean", example="false"),
     *      @OA\Property(property="message", type="string", example="No autorizado")
     *  )
     * )
     */
    public function index()
    {
        $posts = Post::with('user', 'comments')->get();
        return response()->json([
            'success' => true,
            'data' => $posts,
            'message' => 'List all Post'
        ]);
    }


    /**
     * @OA\Post(
     *  path="/api/posts",
     *  summary="Crear un nuevo post",
     *  tags={"Posts"},
     *  security={{"sanctum":{}}},
     *  @OA\RequestBody(
     *  required=true, 
     *  @OA\JsonContent(type="object", 
     *  @OA\Property(property="title", type="string", example="Titulo Postt"), 
     *  @OA\Property(property="content", type="string", example="Contenido del Post")
     *  )
     * ),
     *  @OA\Response(
     *      response=201, 
     *      description="Post creado exitosamente",
     *  @OA\JsonContent(
     *      type="object",
     *      @OA\Property(property="success", type="boolean", example="true"),
     *      @OA\Property(property="data", type="object", ref="#/components/schemas/Post"),
     *      @OA\Property(property="message", type="string", example="Post creado exitosamente")
     * ),
     * ),
     * @OA\Response(
     *      response=401, 
     *      description="No autorizado",
     *  @OA\JsonContent(
     *      type="object",
     *      @OA\Property(property="success", type="boolean", example="false"),
     *      @OA\Property(property="message", type="string", example="No autorizado")
     *  )
     * )
     * )
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string',
            'content' => 'required|string',
        ]);

        // Agregar el user_id después de la validación
        $validated['user_id'] = auth()->id();

        try {
            $post = Post::create($validated);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating post'
            ], 500);
        }

        return response()->json([
            'success' => true,
            'data' => $post,
            'message' => 'Post Created Successfully'
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $post = Post::findOrFail($id)->with('comments')->get();
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 404);
        }


        return response()->json([
            'success' => true,
            'data' => $post,
            'message' => 'Show Post by id ' . $id
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'title' => 'required|string',
            'content' => 'required|string',
        ]);

        try {
            $post = Post::findOrFail($id)->update($validated);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $post,
            'message' => 'Post Updated Successfully'
        ], 201);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $post = Post::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 404);
        }

        $post = Post::destroy($id);

        return response()->json([
            'success' => true,
            'data' => $post,
            'message' => 'Destroy Post by id ' . $id
        ]);
    }
}
