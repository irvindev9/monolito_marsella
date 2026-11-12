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

    public function paymentInfo()
    {
        $configs = Config::whereIn('slug', ['mdtpr', 'payment_bank', 'payment_clabe', 'payment_beneficiary'])
            ->pluck('setting', 'slug');

        return response()->json([
            'mdtpr' => (int) ($configs['mdtpr'] ?? 7),
            'bank' => $configs['payment_bank'] ?? '',
            'clabe' => $configs['payment_clabe'] ?? '',
            'beneficiary' => $configs['payment_beneficiary'] ?? '',
        ]);
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
        $allowedBanks = ['BBVA', 'Santander', 'Banorte', 'HSBC'];

        if (is_array($request->all())) {
            foreach ($request->all() as $value) {
                $slug = $value['slug'] ?? null;
                $setting = $value['setting'] ?? null;

                if ($slug === 'payment_clabe' && $setting !== null && $setting !== '' && ! preg_match('/^[\d\s]+$/', $setting)) {
                    return response()->json(['message' => 'La CLABE solo debe contener números y espacios'], 422);
                }
                if ($slug === 'payment_bank' && $setting !== null && $setting !== '' && ! in_array($setting, $allowedBanks, true)) {
                    return response()->json(['message' => 'Banco no válido'], 422);
                }

                $config = Config::where('slug', $slug)->first();
                if ($config) {
                    $config->setting = $setting;
                    $config->save();
                }
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
