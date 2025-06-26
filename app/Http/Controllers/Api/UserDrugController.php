<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\RxNormService;
use App\Models\Medication;
use Illuminate\Support\Facades\Auth;


class UserDrugController extends Controller
{
    protected $rxNorm;

    public function __construct(RxNormService $rxNormService)
    {
        $this->rxNorm = $rxNormService;
    }

    public function store(Request $request)
    {
        $request->validate([
            'rxcui' => 'required|string',
        ]);

        $user = Auth::user();
        $rxcui = $request->rxcui;

        if (!$this->rxNorm->validateRxcui($rxcui)) {
            return response()->json(['error' => 'Invalid rxcui'], 400);
        }

        $alreadyExists = Medication::where('user_id', $user->id)->where('rxcui', $rxcui)->exists();
        if ($alreadyExists) {
            return response()->json(['error' => 'Drug already added'], 409);
        }

        $med = Medication::create([
            'user_id' => $user->id,
            'rxcui' => $rxcui,
        ]);

        return response()->json(['message' => 'Drug added successfully', 'data' => $med], 201);
    }

    public function destroy($rxcui)
    {
        $user = Auth::user();

        $deleted = Medication::where('user_id', $user->id)
            ->where('rxcui', $rxcui)
            ->delete();

        if (!$deleted) {
            return response()->json(['error' => 'Drug not found in user list'], 404);
        }

        return response()->json(['message' => 'Drug removed successfully']);
    }

    public function index()
    {
        $user = Auth::user();
        $medications = Medication::where('user_id', $user->id)->pluck('rxcui');

        $drugs = $medications->map(function ($rxcui) {
            return $this->rxNorm->getDrugDetails($rxcui);
        })->filter();

        return response()->json($drugs->values());
    }
}
