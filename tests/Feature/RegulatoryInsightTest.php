<?php

namespace Tests\Feature;

use App\Enums\Prioridad;
use App\Models\RegulatoryInsight;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegulatoryInsightTest extends TestCase
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

    public function test_store_creates_insight_from_n8n_payload(): void
    {
        $payload = [
            'relevante' => true,
            'pais' => 'México',
            'autoridad' => 'COFEPRIS',
            'fecha_publicacion' => '2026-05-01',
            'url_fuente' => 'https://example.com/norma-1',
            'tipo_publicacion' => 'Resolución',
            'sector' => ['Agroquímico'],
            'prioridad' => 'Alta',
            'titulo_ejecutivo' => 'Nueva restricción en fitosanitarios',
            'resumen_tecnico' => 'Resumen técnico de prueba.',
            'impacto_para_bayer' => [
                'tipo_impacto' => ['Regulatorio'],
                'descripcion' => 'Impacto moderado',
                'nivel_confianza' => 'Alto',
                'justificacion_confianza' => 'Fuente oficial',
            ],
            'resumen_puntos' => ['Punto 1'],
            'requiere_revision_humana' => true,
            'razon_revision_humana' => 'Alta prioridad',
        ];

        $response = $this->withToken(self::TOKEN)
            ->postJson('/api/regulatory-insights', $payload);

        $response->assertCreated()
            ->assertJsonPath('data.prioridad', 'Alta')
            ->assertJsonPath('data.pais', 'México');

        $this->assertDatabaseHas('regulatory_insights', [
            'url_fuente' => 'https://example.com/norma-1',
            'prioridad' => 'Alta',
        ]);
    }

    public function test_store_is_idempotent_by_url_fuente(): void
    {
        RegulatoryInsight::factory()->create([
            'url_fuente' => 'https://example.com/norma-1',
            'titulo_ejecutivo' => 'Título original',
            'prioridad' => Prioridad::Baja,
        ]);

        $response = $this->withToken(self::TOKEN)
            ->postJson('/api/regulatory-insights', [
                'url_fuente' => 'https://example.com/norma-1',
                'titulo_ejecutivo' => 'Título actualizado',
                'prioridad' => 'Alta',
            ]);

        $response->assertOk()
            ->assertJsonPath('data.titulo_ejecutivo', 'Título actualizado')
            ->assertJsonPath('data.prioridad', 'Alta');

        $this->assertDatabaseCount('regulatory_insights', 1);
    }

    public function test_index_filters_by_prioridad(): void
    {
        RegulatoryInsight::factory()->create(['prioridad' => Prioridad::Alta, 'titulo_ejecutivo' => 'Alta 1']);
        RegulatoryInsight::factory()->create(['prioridad' => Prioridad::Media, 'titulo_ejecutivo' => 'Media 1']);
        RegulatoryInsight::factory()->create(['prioridad' => Prioridad::Baja, 'titulo_ejecutivo' => 'Baja 1']);

        $response = $this->withToken(self::TOKEN)
            ->getJson('/api/regulatory-insights?prioridad=Alta,Media');

        $response->assertOk();
        $this->assertCount(2, $response->json('data'));
    }

    public function test_rejects_requests_without_token(): void
    {
        $this->postJson('/api/regulatory-insights', [
            'titulo_ejecutivo' => 'Sin token',
        ])->assertUnauthorized();

        $this->getJson('/api/regulatory-insights')->assertUnauthorized();
    }

    public function test_store_resolves_country_code_from_pais(): void
    {
        $response = $this->withToken(self::TOKEN)
            ->postJson('/api/regulatory-insights', [
                'titulo_ejecutivo' => 'Insight Colombia',
                'pais' => 'Colombia',
                'prioridad' => 'Media',
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.country_code', 'CO');

        $this->assertDatabaseHas('regulatory_insights', [
            'titulo_ejecutivo' => 'Insight Colombia',
            'country_code' => 'CO',
        ]);
    }

    public function test_index_filters_by_country_code(): void
    {
        RegulatoryInsight::factory()->create([
            'country_code' => 'CO',
            'pais' => 'Colombia',
            'titulo_ejecutivo' => 'Colombia insight',
        ]);
        RegulatoryInsight::factory()->create([
            'country_code' => 'MX',
            'pais' => 'México',
            'titulo_ejecutivo' => 'Mexico insight',
        ]);

        $response = $this->withToken(self::TOKEN)
            ->getJson('/api/regulatory-insights?country_code=CO');

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
        $this->assertSame('CO', $response->json('data.0.country_code'));
    }

    public function test_index_includes_frontend_aliases(): void
    {
        RegulatoryInsight::factory()->create([
            'titulo_ejecutivo' => 'Título alias',
            'resumen_tecnico' => 'Resumen alias',
            'resumen_puntos' => ['Punto 1'],
            'country_code' => 'AR',
        ]);

        $response = $this->withToken(self::TOKEN)
            ->getJson('/api/regulatory-insights?country_code=AR');

        $response->assertOk()
            ->assertJsonPath('data.0.title', 'Título alias')
            ->assertJsonPath('data.0.summary', 'Resumen alias')
            ->assertJsonPath('data.0.key_findings.0', 'Punto 1');

        $this->assertNotNull($response->json('data.0.created_date'));
    }

    public function test_store_accepts_n8n_batch_array_payload(): void
    {
        $payload = [
            [
                'relevante' => false,
                'titulo_ejecutivo' => 'Invima presenta actividades territoriales e InvimÁgil en Guainía para trámites sanitarios',
                'pais' => 'Colombia',
                'autoridad' => 'Instituto Nacional de Vigilancia de Medicamentos y Alimentos (Invima)',
                'prioridad' => 'Baja',
                'fecha_publicacion' => '2026-04-20',
                'url_fuente' => 'https://www.invima.gov.co/blog/sala-de-prensa-13/el-gobierno-del-cambio-le-cumple-a-guainia-actividades-con-el-invima-y-el-sena-para-transformar-el-territorio-438',
                'resumen_tecnico' => 'El Invima participará en actividades institucionales en Inírida, Guainía, del 20 al 23 de abril de 2026.',
                'analisis_impacto' => 'La publicación tiene carácter informativo y operativo.',
                'recomendacion_preliminar' => 'Monitorear si Invima emite lineamientos adicionales.',
                'resumen_puntos' => [
                    'Actividades institucionales en Inírida, Guainía, del 20 al 23 de abril de 2026.',
                    'Presentación de InvimÁgil para registros, permisos y notificaciones sanitarias en línea.',
                ],
            ],
        ];

        $response = $this->withToken(self::TOKEN)
            ->postJson('/api/regulatory-insights', $payload);

        $response->assertCreated()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('meta.created', 1)
            ->assertJsonPath('data.0.prioridad', 'Baja')
            ->assertJsonPath('data.0.pais', 'Colombia');

        $insight = RegulatoryInsight::query()->first();
        $this->assertNotNull($insight);
        $this->assertSame('Colombia', $insight->payload_original['pais']);
        $this->assertCount(2, $insight->resumen_puntos);
    }

    public function test_store_batch_is_idempotent_by_url_fuente(): void
    {
        $item = [
            'titulo_ejecutivo' => 'Título original',
            'url_fuente' => 'https://example.com/norma-batch',
            'prioridad' => 'Baja',
        ];

        $this->withToken(self::TOKEN)
            ->postJson('/api/regulatory-insights', [$item])
            ->assertCreated();

        $response = $this->withToken(self::TOKEN)
            ->postJson('/api/regulatory-insights', [
                array_merge($item, [
                    'titulo_ejecutivo' => 'Título actualizado',
                    'prioridad' => 'Alta',
                ]),
            ]);

        $response->assertOk()
            ->assertJsonPath('meta.updated', 1)
            ->assertJsonPath('meta.created', 0)
            ->assertJsonPath('data.0.titulo_ejecutivo', 'Título actualizado');

        $this->assertDatabaseCount('regulatory_insights', 1);
    }

    public function test_store_accepts_full_n8n_json_with_empty_strings(): void
    {
        $payload = [
            'relevante' => true,
            'pais' => '',
            'autoridad' => '',
            'fecha_publicacion' => '',
            'url_fuente' => '',
            'tipo_publicacion' => '',
            'sector' => [],
            'prioridad' => 'Alta',
            'titulo_ejecutivo' => '',
            'resumen_tecnico' => '',
            'analisis_impacto' => '',
            'impacto_para_bayer' => [
                'tipo_impacto' => [],
                'descripcion' => '',
                'nivel_confianza' => 'Alto',
                'justificacion_confianza' => '',
            ],
            'resumen_puntos' => [],
            'obligaciones_o_acciones' => [],
            'fechas_clave' => [],
            'productos_o_categorias_mencionadas' => [],
            'entidades_mencionadas' => [],
            'palabras_clave_regulatorias' => [],
            'riesgos_identificados' => [],
            'recomendacion_preliminar' => '',
            'requiere_revision_humana' => true,
            'razon_revision_humana' => '',
            'evidencia_textual_relevante' => [],
            'workflow_id' => 'n8n-extra-field',
        ];

        $response = $this->withToken(self::TOKEN)
            ->postJson('/api/regulatory-insights', $payload);

        $response->assertCreated()
            ->assertJsonPath('data.titulo_ejecutivo', 'Sin título')
            ->assertJsonPath('data.pais', null)
            ->assertJsonPath('data.prioridad', 'Alta');

        $insight = RegulatoryInsight::query()->first();
        $this->assertNotNull($insight);
        $this->assertSame('n8n-extra-field', $insight->payload_original['workflow_id']);
        $this->assertSame(true, $insight->payload_original['relevante']);
    }

    public function test_store_accepts_updated_n8n_schema_with_structured_obligaciones(): void
    {
        $payload = [
            'relevante' => true,
            'titulo_ejecutivo' => 'Invima emite recomendaciones sobre uso seguro y registro sanitario de dispositivos de ortodoncia',
            'pais' => 'Colombia',
            'autoridad' => 'Instituto Nacional de Vigilancia de Medicamentos y Alimentos – Invima',
            'prioridad' => 'Alta',
            'fecha_publicacion' => '2026-04-10',
            'url_fuente' => 'https://www.invima.gov.co/blog/sala-de-prensa-13/invima-entrega-recomendaciones-para-el-uso-seguro-de-dispositivos-de-ortodoncia-417',
            'resumen_tecnico' => 'El Invima orienta sobre el uso seguro de dispositivos médicos empleados en ortodoncia.',
            'analisis_impacto' => 'Impacto potencial bajo para Bayer, sujeto a validación del portafolio local.',
            'recomendacion_preliminar' => 'Validar con el equipo regulatorio local.',
            'resumen_puntos' => [
                'Invima recomienda verificar registro sanitario o autorización vigente en dispositivos de ortodoncia.',
            ],
            'tipo_publicacion' => 'Comunicado oficial',
            'sector' => ['Dispositivos médicos', 'Salud humana'],
            'nivel_confianza' => 'Bajo',
            'riesgos_identificados' => [
                'Riesgo de incumplimiento por uso o comercialización de dispositivos médicos sin registro sanitario vigente',
            ],
            'obligaciones_o_acciones' => [
                [
                    'accion_requerida' => 'Verificar que los dispositivos médicos utilizados cuenten con registro sanitario vigente o autorización del Invima.',
                    'responsable_sugerido' => 'Profesional en odontología',
                    'plazo' => 'No especificado',
                    'estado' => 'Explícito en la fuente',
                ],
            ],
        ];

        $response = $this->withToken(self::TOKEN)
            ->postJson('/api/regulatory-insights', $payload);

        $response->assertCreated()
            ->assertJsonPath('data.nivel_confianza', 'Bajo')
            ->assertJsonPath('data.obligaciones_o_acciones.0.accion_requerida', $payload['obligaciones_o_acciones'][0]['accion_requerida'])
            ->assertJsonPath('data.obligaciones_o_acciones.0.responsable_sugerido', 'Profesional en odontología');

        $insight = RegulatoryInsight::query()->first();
        $this->assertNotNull($insight);
        $this->assertSame('Bajo', $insight->nivel_confianza?->value);
        $this->assertIsArray($insight->obligaciones_o_acciones);
        $this->assertSame('Explícito en la fuente', $insight->obligaciones_o_acciones[0]['estado']);
    }

    public function test_store_normalizes_legacy_obligaciones_strings(): void
    {
        $response = $this->withToken(self::TOKEN)
            ->postJson('/api/regulatory-insights', [
                'titulo_ejecutivo' => 'Legacy obligaciones',
                'url_fuente' => 'https://example.com/legacy-obligaciones',
                'obligaciones_o_acciones' => ['Verificar registro sanitario vigente'],
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.obligaciones_o_acciones.0.accion_requerida', 'Verificar registro sanitario vigente')
            ->assertJsonPath('data.obligaciones_o_acciones.0.responsable_sugerido', null);
    }

    public function test_store_accepts_exact_n8n_fixture_file(): void
    {
        $payload = json_decode(
            file_get_contents(base_path('tests/Fixtures/n8n_full_payload.json')),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        $response = $this->withToken(self::TOKEN)
            ->postJson('/api/regulatory-insights', $payload);

        $response->assertCreated()
            ->assertJsonPath('meta.created', 1)
            ->assertJsonPath('data.0.obligaciones_o_acciones.2.estado', 'Inferido preliminarmente')
            ->assertJsonPath('data.0.evidencia_textual_relevante.2.fragmento', 'los brackets, arcos, ligaduras y demás productos utilizados en ortodoncia son considerados dispositivos médicos');
    }

    public function test_store_accepts_full_n8n_payload_with_fechas_and_evidencia(): void
    {
        $evidenciaJson = json_encode([
            [
                'fragmento' => 'verificar que los dispositivos utilizados cuenten con registro sanitario o autorización vigente.',
                'por_que_es_relevante' => 'Soporta la identificación de requisitos regulatorios aplicables a dispositivos médicos de ortodoncia.',
            ],
            [
                'fragmento' => 'los brackets, arcos, ligaduras y demás productos utilizados en ortodoncia son considerados dispositivos médicos',
                'por_que_es_relevante' => 'Identifica categorías reguladas explícitamente por la fuente oficial.',
            ],
        ], JSON_UNESCAPED_UNICODE);

        $payload = [
            [
                'relevante' => true,
                'titulo_ejecutivo' => 'Invima emite recomendaciones sobre uso seguro y registro sanitario de dispositivos de ortodoncia',
                'pais' => 'Colombia',
                'autoridad' => 'Instituto Nacional de Vigilancia de Medicamentos y Alimentos – Invima',
                'prioridad' => 'Alta',
                'fecha_publicacion' => '2026-04-10',
                'url_fuente' => 'https://www.invima.gov.co/blog/sala-de-prensa-13/invima-entrega-recomendaciones-para-el-uso-seguro-de-dispositivos-de-ortodoncia-417',
                'resumen_tecnico' => 'El Invima orienta sobre el uso seguro de dispositivos médicos empleados en ortodoncia.',
                'analisis_impacto' => 'Impacto potencial bajo para Bayer, sujeto a validación del portafolio local.',
                'recomendacion_preliminar' => 'Validar con el equipo regulatorio local.',
                'resumen_puntos' => ['Invima recomienda verificar registro sanitario o autorización vigente en dispositivos de ortodoncia.'],
                'tipo_publicacion' => 'Comunicado oficial',
                'sector' => ['Dispositivos médicos', 'Salud humana', 'Trámites sanitarios'],
                'nivel_confianza' => 'Bajo',
                'riesgos_identificados' => ['Riesgo de incumplimiento por uso o comercialización de dispositivos médicos sin registro sanitario vigente'],
                'obligaciones_o_acciones' => [
                    [
                        'accion_requerida' => 'Verificar que los dispositivos médicos utilizados cuenten con registro sanitario vigente o autorización del Invima.',
                        'responsable_sugerido' => 'Profesional en odontología',
                        'plazo' => 'No especificado',
                        'estado' => 'Explícito en la fuente',
                    ],
                    [
                        'accion_requerida' => 'Validar si las categorías de dispositivos odontológicos mencionadas corresponden al portafolio o canales comerciales de Bayer en Colombia.',
                        'responsable_sugerido' => 'Equipo regulatorio local',
                        'plazo' => 'No especificado',
                        'estado' => 'Inferido preliminarmente',
                    ],
                ],
                'productos_o_categorias_mencionadas' => ['Alineadores', 'Brackets', 'Dispositivos odontológicos'],
                'entidades_mencionadas' => ['Instituto Nacional de Vigilancia de Medicamentos y Alimentos – Invima'],
                'palabras_clave_regulatorias' => ['Registro sanitario', 'Dispositivos médicos'],
                'fechas_clave' => [
                    [
                        'tipo_fecha' => 'Publicación',
                        'fecha' => '2026-04-10',
                        'descripcion' => 'Fecha indicada en la publicación oficial del Invima.',
                    ],
                ],
                'requiere_revision_humana' => true,
                'razon_revision_humana' => 'Se requiere validar si las categorías de dispositivos odontológicos corresponden al portafolio de Bayer en Colombia.',
                'evidencia_textual_relevante' => $evidenciaJson,
            ],
        ];

        $response = $this->withToken(self::TOKEN)
            ->postJson('/api/regulatory-insights', $payload);

        $response->assertCreated()
            ->assertJsonPath('meta.created', 1)
            ->assertJsonPath('data.0.fechas_clave.0.tipo_fecha', 'Publicación')
            ->assertJsonPath('data.0.fechas_clave.0.fecha', '2026-04-10')
            ->assertJsonPath('data.0.productos_o_categorias_mencionadas.1', 'Brackets')
            ->assertJsonPath('data.0.requiere_revision_humana', true)
            ->assertJsonPath('data.0.evidencia_textual_relevante.0.fragmento', 'verificar que los dispositivos utilizados cuenten con registro sanitario o autorización vigente.')
            ->assertJsonPath('data.0.evidencia_textual_relevante.1.por_que_es_relevante', 'Identifica categorías reguladas explícitamente por la fuente oficial.');

        $insight = RegulatoryInsight::query()->first();
        $this->assertNotNull($insight);
        $this->assertCount(2, $insight->obligaciones_o_acciones);
        $this->assertCount(2, $insight->evidencia_textual_relevante);
        $this->assertSame('Inferido preliminarmente', $insight->obligaciones_o_acciones[1]['estado']);
    }
}
