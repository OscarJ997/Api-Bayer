<?php

namespace Tests\Feature;

use App\Models\ConnectedPortal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConnectedPortalTest extends TestCase
{
    use RefreshDatabase;

    private const TOKEN = 'test-api-token';

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.n8n.api_token' => self::TOKEN,
            'services.api.read_token' => self::TOKEN,
        ]);
    }

    public function test_index_filters_by_country_code(): void
    {
        ConnectedPortal::factory()->create(['country_code' => 'CO', 'name' => 'Invima']);
        ConnectedPortal::factory()->create(['country_code' => 'MX', 'name' => 'COFEPRIS']);

        $response = $this->withToken(self::TOKEN)
            ->getJson('/api/connected-portals?country_code=CO');

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
        $this->assertSame('Invima', $response->json('data.0.name'));
    }

    public function test_store_creates_portal(): void
    {
        $response = $this->withToken(self::TOKEN)
            ->postJson('/api/connected-portals', [
                'country_code' => 'co',
                'name' => 'Banco de la República',
                'url' => 'https://www.banrep.gov.co',
                'description' => 'Portal del banco central',
                'category' => 'banco_central',
                'status' => 'activo',
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.country_code', 'CO')
            ->assertJsonPath('data.name', 'Banco de la República');

        $this->assertDatabaseHas('connected_portals', [
            'country_code' => 'CO',
            'name' => 'Banco de la República',
        ]);
    }

    public function test_store_prepends_https_when_url_has_no_scheme(): void
    {
        $response = $this->withToken(self::TOKEN)
            ->postJson('/api/connected-portals', [
                'country_code' => 'CO',
                'name' => 'Invima',
                'url' => 'www.invima.gov.co',
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.url', 'https://www.invima.gov.co');

        $this->assertDatabaseHas('connected_portals', [
            'url' => 'https://www.invima.gov.co',
        ]);
    }

    public function test_destroy_deletes_portal(): void
    {
        $portal = ConnectedPortal::factory()->create();

        $this->withToken(self::TOKEN)
            ->deleteJson("/api/connected-portals/{$portal->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('connected_portals', ['id' => $portal->id]);
    }
}
