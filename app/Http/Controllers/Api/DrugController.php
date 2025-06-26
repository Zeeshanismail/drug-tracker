<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\RxNormService;
use Illuminate\Support\Facades\Cache;

class DrugController extends Controller
{
    public function search(Request $request)
    {
        $name = $request->query('drug_name');
        if (!$name) {
            return response()->json(['error' => 'drug_name is required'], 422);
        }

        return Cache::remember("drug_search:$name", 3600, function () use ($name) {
            $results = (new RxNormService)->searchDrug($name);
            return response()->json($results);
        });
    }
}
