<?php

namespace App\Http\Controllers;

use App\Jobs\SaveSettingsOnMetafield;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SettingsController extends Controller
{
    public function saveSettings(Request $request): JsonResponse
    {
        $dataArray = $request->all();
        foreach ($dataArray as $data) {
            Setting::updateOrCreate(
                [
                    'user_id' => Auth::user()->id,
                    'slug' => $data['slug'],
                ],
                [
                    'value' => $data['value'],
                ]
            );
        }

        SaveSettingsOnMetafield::dispatch(Auth::user());
        $settings = Setting::where(['user_id' => Auth::user()->id])->get();

        return response()->json([
            'success' => true,
            'message' => 'Settings saved',
            'data' => $settings,
        ]);
    }

    public function getSettings(): JsonResponse
    {
        $data = [];
        $success = false;
        $settings = Setting::where(['user_id' => Auth::user()->id])->get();
        if ($settings->count() > 0) {
            $data = $settings;
            $success = true;
        }

        return response()->json([
            'success' => $success,
            'data' => $data,
        ]);
    }
}
