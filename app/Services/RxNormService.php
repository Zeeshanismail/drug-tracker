<?php
// app/Services/RxNormService.php
namespace App\Services;
use Illuminate\Support\Facades\Http;

class RxNormService
{
    public function searchDrug($name)
    {
        $baseUrl = 'https://rxnav.nlm.nih.gov/REST';

        $response = Http::get("$baseUrl/drugs.json", ['name' => $name]);
        $drugs = collect($response['drugGroup']['conceptGroup'] ?? [])
            ->filter(fn($group) => $group['tty'] === 'SBD')
            ->flatMap(fn($group) => $group['conceptProperties'] ?? [])
            ->take(5)
            ->map(function ($drug) use ($baseUrl) {
                $rxcui = $drug['rxcui'];
                $detail = Http::get("$baseUrl/rxcui/$rxcui/history/status.json");

                return [
                    'rxcui' => $rxcui,
                    'name' => $drug['name'],
                    'baseNames' => collect($detail['rxcuiStatus']['ingredientAndStrength'] ?? [])
                        ->pluck('baseName')
                        ->unique()
                        ->values(),
                    'doseForms' => collect($detail['rxcuiStatus']['doseFormGroupConcept'] ?? [])
                        ->pluck('doseFormGroupName')
                        ->unique()
                        ->values(),
                ];
            });

        return $drugs->values();
    }

    public function validateRxcui($rxcui)
    {
        $response = Http::get("https://rxnav.nlm.nih.gov/REST/rxcui/$rxcui/properties.json");

        // If the request is successful and name is available, rxcui is valid
        return $response->ok() && isset($response['properties']['name']);
    }

    public function getDrugDetails($rxcui)
    {
        $baseUrl = 'https://rxnav.nlm.nih.gov/REST';

        $nameResp = Http::get("$baseUrl/rxcui/$rxcui/properties.json");
        $name = $nameResp['properties']['name'] ?? null;

        $detailResp = Http::get("$baseUrl/rxcui/$rxcui/history/status.json");
        $baseNames = collect($detailResp['rxcuiStatus']['ingredientAndStrength'] ?? [])
            ->pluck('baseName')->unique()->values();
        $doseForms = collect($detailResp['rxcuiStatus']['doseFormGroupConcept'] ?? [])
            ->pluck('doseFormGroupName')->unique()->values();

        if (!$name) return null;

        return [
            'rxcui' => $rxcui,
            'name' => $name,
            'baseNames' => $baseNames,
            'doseForms' => $doseForms,
        ];
    }

}
