<?php

namespace App\Http\Controllers;

use App\Models\CleaningCriteria;
use Illuminate\Http\Request;

class CleaningCriteriaController extends Controller
{
    public function index()
    {
        return response()->json(CleaningCriteria::orderBy('order')->get());
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'response_type' => 'required|in:boolean,number,text',
        ]);

        $criteria = CleaningCriteria::create([
            'name' => $request->name,
            'response_type' => $request->response_type,
            'is_active' => $request->is_active ?? true,
            'order' => $request->order ?? 0,
        ]);

        return response()->json($criteria, 201);
    }

    public function update(Request $request, string $id)
    {
        $criteria = CleaningCriteria::findOrFail($id);

        $criteria->update($request->only(['name', 'response_type', 'is_active', 'order']));

        return response()->json($criteria);
    }

    public function destroy(string $id)
    {
        CleaningCriteria::findOrFail($id)->delete(); // soft delete

        return response()->json(['message' => 'Criterio eliminado']);
    }
}
