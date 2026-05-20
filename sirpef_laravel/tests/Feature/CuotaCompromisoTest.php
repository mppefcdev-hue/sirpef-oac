<?php

namespace Tests\Feature;

use Tests\TestCase;
use Tests\Feature\UserTestable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Models\CuotaCompromiso;
use App\Models\User;

class CuotaCompromisoTest extends TestCase
{
    use DatabaseTransactions, UserTestable;

    protected function setUp(): void
    {
        parent::setUp();
        CuotaCompromiso::query()->delete();
    }

    public function test_it_returns_cuotas_list_with_timestamps()
    {
        $this->actingAs(UserTestable::userAdmin());

        $cuota = CuotaCompromiso::create([
            'year' => 2026,
            'mes' => 5,
            'monto' => 1500.00
        ]);

        $response = $this->getJson('/api/oac/cuotas');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'ano',
                        'mes',
                        'monto_limite',
                        'monto_ejecutado',
                        'monto_disponible',
                        'created_at',
                        'updated_at'
                    ]
                ]
            ]);

        $this->assertEquals(2026, $response->json('data.0.ano'));
        $this->assertNotNull($response->json('data.0.created_at'));
        $this->assertNotNull($response->json('data.0.updated_at'));
    }

    public function test_it_stores_a_new_cuota_if_not_exists()
    {
        $this->actingAs(UserTestable::userAdmin());

        $response = $this->postJson('/api/oac/cuotas', [
            'ano' => 2026,
            'mes' => 6,
            'monto' => 2000.50
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Cuota de compromiso guardada exitosamente.'
            ]);

        $this->assertDatabaseHas('tbl_cuotas_compromiso', [
            'year' => 2026,
            'mes' => 6,
            'monto' => 2000.50
        ]);
    }

    public function test_it_fails_to_store_if_already_exists()
    {
        $this->actingAs(UserTestable::userAdmin());

        CuotaCompromiso::create([
            'year' => 2026,
            'mes' => 6,
            'monto' => 2000.50
        ]);

        $response = $this->postJson('/api/oac/cuotas', [
            'ano' => 2026,
            'mes' => 6,
            'monto' => 3000.00
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Ya existe una cuota registrada para este mes y año. Utilice la opción de Modificar Cuota si es administrador/director.'
            ]);

        // Verify it was not modified
        $this->assertDatabaseHas('tbl_cuotas_compromiso', [
            'year' => 2026,
            'mes' => 6,
            'monto' => 2000.50
        ]);
    }

    public function test_it_updates_existing_cuota_using_put()
    {
        $this->actingAs(UserTestable::userAdmin());

        CuotaCompromiso::create([
            'year' => 2026,
            'mes' => 6,
            'monto' => 2000.50
        ]);

        $response = $this->putJson('/api/oac/cuotas', [
            'ano' => 2026,
            'mes' => 6,
            'monto' => 3500.00
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Cuota de compromiso modificada exitosamente.'
            ]);

        $this->assertDatabaseHas('tbl_cuotas_compromiso', [
            'year' => 2026,
            'mes' => 6,
            'monto' => 3500.00
        ]);
    }

    public function test_it_fails_to_update_if_not_exists()
    {
        $this->actingAs(UserTestable::userAdmin());

        $response = $this->putJson('/api/oac/cuotas', [
            'ano' => 2026,
            'mes' => 7,
            'monto' => 4000.00
        ]);

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'No existe una cuota registrada para este mes y año. Primero debe guardarla.'
            ]);
    }

    public function test_regular_user_cannot_access_or_modify()
    {
        // Role ID 3 is Monitor (forbidden for write/index in cuotas controller)
        $user = User::factory()->create([ 'role_id' => 3 ]);
        $this->actingAs($user);

        $this->getJson('/api/oac/cuotas')->assertStatus(403);
        $this->postJson('/api/oac/cuotas', ['ano' => 2026, 'mes' => 5, 'monto' => 1000])->assertStatus(403);
        $this->putJson('/api/oac/cuotas', ['ano' => 2026, 'mes' => 5, 'monto' => 1000])->assertStatus(403);
    }
}
