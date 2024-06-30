<?php

namespace App\Http\Controllers;

use App\Models\Customization;
use Illuminate\Http\JsonResponse;

class CustomizationController extends Controller
{
    public function getCustomizations(): JsonResponse
    {
        $data = Customization::all();

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }
}
