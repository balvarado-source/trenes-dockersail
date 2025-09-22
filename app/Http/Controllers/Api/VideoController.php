<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Video;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class VideoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $videos = Video::all();
        return response()->json([
            'success' => true,
            'data' => $videos,
            'message' => 'List all Videos'
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
            'duration' => 'nullable|integer',
            'description' => 'nullable|string',
        ]);

        $video = Video::create($validated);

        return response()->json([
            'success' => true,
            'data' => $video,
            'message' => 'Video Created Successfully'
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $video = Video::findOrFail($id)->with('polymorphicComments')->get();
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $video,
            'message' => 'Show Video by id ' . $id
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
            'duration' => 'nullable|integer',
            'description' => 'nullable|string',
        ]);

        try {
            $video = Video::findOrFail($id)->update($validated);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $video,
            'message' => 'Video Updated Successfully'
        ], 201);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $video = Video::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 404);
        }

        $video = Video::destroy($id);

        return response()->json([
            'success' => true,
            'data' => $video,
            'message' => 'Destroy Video by id ' . $id
        ]);
    }
}
