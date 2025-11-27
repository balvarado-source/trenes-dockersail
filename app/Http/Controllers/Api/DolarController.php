<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\DolarOficial;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class DolarController extends Controller
{


    /**
     * Display the specified resource.
     */
    public function show()
    {
        $dolar_value = Redis::get('dolar_oficial');

        //$dolar_value = DolarOficial::latest()->first()->value;

        return response()->json([
            'success' => true,
            'data' => ['value' => $dolar_value],
        ]);
    }
}
