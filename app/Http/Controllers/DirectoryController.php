<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Directory;


class DirectoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
        $request->validate([
            'name' => 'required',
            'position' => 'required',
            'phone' => 'required:numeric',
            'description' => 'required',
            'file' => 'required:file',
        ]);

        $directory = new Directory();
            $directory->name = $request->name;
            $directory->position = $request->position;
            $directory->phone = $request->phone ?? null;
            $directory->description = $request->description ?? null;

            if (isset($request->file)) {
                $fileName = time().'.'.$request->file->getClientOriginalExtension();
                ini_set('max_execution_time', 300);

                \Tinify\setKey(env('TINY_API_KEY'));

                try {
                    $source = \Tinify\fromFile($request->file);
                    
                    Storage::disk('public')->put($fileName, $source->toBuffer());

                    $directory->photo = $fileName;
                }catch (\Tinify\AccountException $e) {
                    return response()->json(['message' => 'La cuenta de TinyPNG ha alcanzado su límite de compresión mensual, contacte con el administrador']);
                }
            }
            
        $directory->save();
              
        return response()->json(['message' => 'El directorio se ha creado correctamente']);
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
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
