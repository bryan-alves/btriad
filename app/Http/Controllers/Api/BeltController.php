<?php

namespace App\Http\Controllers\Api;

use App\Models\Belt;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class BeltController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $belts =  Belt::all();

            return response()->json($belts, 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Erro ao buscar alunos'
            ], 500);
        }

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Belt $belt)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Belt $belt)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Belt $belt)
    {
        //
    }
}
