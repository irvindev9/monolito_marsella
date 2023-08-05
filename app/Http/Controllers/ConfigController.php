<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Config;

class ConfigController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(Config::all());
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
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
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        if (is_array($request->all())) {
            foreach ($request->all() as $key => $value) {
                $config = Config::where('slug', $value['slug'])->first();
                $config->setting = $value['setting'];
                $config->save();
            }
        }

        return response()->json([
            'message' => 'Configuración actualizada exitosamente',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
