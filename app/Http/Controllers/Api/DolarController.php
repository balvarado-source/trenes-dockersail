<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\DolarOficial;
use Exception;
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
        try {
            $dolar_value = Redis::get('dolar_oficial');
            if (empty($dolar_value)) {
                Redis::set('dolar_oficial', DolarOficial::latest()->first()->value);
            }
        } catch (Exception $e) {
            $dolar_value = DolarOficial::latest()->first()->value;
        }

        return response()->json([
            'success' => true,
            'data' => ['value' => $dolar_value],
        ]);
    }
}
