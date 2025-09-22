<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Image;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class ImageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $images = Image::all();
        return response()->json([
            'success' => true,
            'data' => $images,
            'message' => 'List all Images'
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string',
            'url' => 'required|string',
            'description' => 'nullable|string',
            'user_id' => 'string'
        ]);

        $image = Image::create($validated);

        return response()->json([
            'success' => true,
            'data' => $image,
            'message' => 'Image Created Successfully'
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $image = Image::findOrFail($id)->with('polymorphicComments')->get();
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $image,
            'message' => 'Show Image by id ' . $id
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'title' => 'required|string',
            'url' => 'required|string',
            'description' => 'nullable|string',
        ]);

        try {
            $image = Image::findOrFail($id)->update($validated);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $image,
            'message' => 'Image Updated Successfully'
        ], 201);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $image = Image::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 404);
        }

        $image = Image::destroy($id);

        return response()->json([
            'success' => true,
            'data' => $image,
            'message' => 'Destroy Image by id ' . $id
        ]);
    }
}
