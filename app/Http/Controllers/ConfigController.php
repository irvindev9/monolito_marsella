<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Config;
use App\Models\PaymentSettingLog;

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

    public function paymentLogs()
    {
        return response()->json(
            PaymentSettingLog::orderByDesc('id')
                ->limit(100)
                ->get(['id', 'user_name', 'changes', 'created_at'])
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $allowedBanks = ['BBVA', 'Santander', 'Banorte', 'HSBC'];
        $paymentLabels = [
            'payment_bank' => 'Banco',
            'payment_clabe' => 'CLABE',
            'payment_beneficiary' => 'Beneficiario',
        ];
        $changes = [];

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
            }

            foreach ($request->all() as $value) {
                $slug = $value['slug'] ?? null;
                $setting = $value['setting'] ?? null;

                $config = Config::where('slug', $slug)->first();
                if ($config) {
                    if (isset($paymentLabels[$slug]) && (string) ($config->setting ?? '') !== (string) ($setting ?? '')) {
                        $changes[] = [
                            'field' => $paymentLabels[$slug],
                            'from' => (string) ($config->setting ?? ''),
                            'to' => (string) ($setting ?? ''),
                        ];
                    }

                    $config->setting = $setting;
                    $config->save();
                }
            }

            if (! empty($changes)) {
                PaymentSettingLog::create([
                    'user_id' => $request->user()?->id,
                    'user_name' => $request->user()?->name ?? 'Sistema',
                    'changes' => $changes,
                    'created_at' => now(),
                ]);
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
