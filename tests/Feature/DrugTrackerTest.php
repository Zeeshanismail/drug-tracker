<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Http;

class DrugTrackerTest extends TestCase
{
    use RefreshDatabase;

    protected function authenticate()
    {
        $user = User::factory()->create([
            'password' => bcrypt('password')
        ]);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password'
        ]);

        $token = $response->json('token');
        return ['Authorization' => 'Bearer ' . $token];
    }

    public function test_user_can_register()
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Test User',
            'email' => 'user@example.com',
            'password' => 'password'
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['token', 'user']);
    }

    public function test_user_can_login()
    {
        $user = User::factory()->create(['password' => bcrypt('password')]);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password'
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['token', 'user']);
    }

    public function test_drug_search_returns_data()
    {
        Http::fake([
            'rxnav.nlm.nih.gov/*' => Http::response([
                'drugGroup' => [
                    'conceptGroup' => [[
                        'tty' => 'SBD',
                        'conceptProperties' => [
                            ['rxcui' => '123456', 'name' => 'Sample Drug']
                        ]
                    ]]
                ]
            ], 200),
        ]);

        $response = $this->getJson('/api/search?drug_name=aspirin');

        $response->assertStatus(200)
            ->assertJsonStructure([
                ['rxcui', 'name', 'baseNames', 'doseForms']
            ]);
    }

    public function test_user_can_add_drug()
    {
        Http::fake([
            'rxnav.nlm.nih.gov/*' => Http::response([
                'properties' => ['name' => 'Valid Drug']
            ], 200),
        ]);

        $headers = $this->authenticate();

        $response = $this->postJson('/api/user/drugs', [
            'rxcui' => '123456'
        ], $headers);

        $response->assertStatus(201)
            ->assertJsonFragment(['message' => 'Drug added successfully']);
    }

    public function test_user_cannot_add_invalid_drug()
    {
        Http::fake([
            'rxnav.nlm.nih.gov/*' => Http::response([], 404)
        ]);

        $headers = $this->authenticate();

        $response = $this->postJson('/api/user/drugs', [
            'rxcui' => '999999'
        ], $headers);

        $response->assertStatus(400)
            ->assertJson(['error' => 'Invalid rxcui']);
    }

    public function test_user_can_get_their_drugs()
    {
        Http::fake([
            'rxnav.nlm.nih.gov/*' => Http::response([
                'properties' => ['name' => 'Valid Drug']
            ], 200),
        ]);

        $headers = $this->authenticate();

        $this->postJson('/api/user/drugs', [
            'rxcui' => '123456'
        ], $headers);

        Http::fake([
            'rxnav.nlm.nih.gov/*' => Http::response([
                'properties' => ['name' => 'Valid Drug'],
                'rxcuiStatus' => [
                    'ingredientAndStrength' => [['baseName' => 'Ibuprofen']],
                    'doseFormGroupConcept' => [['doseFormGroupName' => 'Tablet']]
                ]
            ], 200),
        ]);

        $response = $this->getJson('/api/user/drugs', $headers);

        $response->assertStatus(200)
            ->assertJsonFragment(['rxcui' => '123456']);
    }

    public function test_user_can_delete_drug()
    {
        Http::fake([
            'rxnav.nlm.nih.gov/*' => Http::response([
                'properties' => ['name' => 'Valid Drug']
            ], 200),
        ]);

        $headers = $this->authenticate();

        // Add drug
        $this->postJson('/api/user/drugs', [
            'rxcui' => '123456'
        ], $headers);

        // Delete drug
        $response = $this->deleteJson('/api/user/drugs/123456', [], $headers);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Drug removed successfully']);
    }
}
