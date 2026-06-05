<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRegulatoryInsightRequest;
use App\Http\Requests\UpdateRegulatoryInsightRequest;
use App\Http\Resources\RegulatoryInsightResource;
use App\Models\RegulatoryInsight;
use App\Services\RegulatoryInsightPersister;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class RegulatoryInsightController extends Controller
{
    public function __construct(
        private readonly RegulatoryInsightPersister $persister,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $sortBy = $request->query('sort_by', 'created_at');
        $sortDir = strtolower($request->query('sort_dir', 'desc')) === 'asc' ? 'asc' : 'desc';

        $allowedSorts = ['created_at', 'fecha_publicacion', 'prioridad', 'titulo_ejecutivo'];
        if (! in_array($sortBy, $allowedSorts, true)) {
            $sortBy = 'created_at';
        }

        $query = RegulatoryInsight::query()
            ->filter($request->only([
                'prioridad',
                'pais',
                'country_code',
                'relevante',
                'requiere_revision_humana',
                'estado',
                'autoridad',
                'search',
            ]));

        if ($sortBy === 'prioridad') {
            $query->orderByRaw(
                "CASE prioridad WHEN 'Alta' THEN 1 WHEN 'Media' THEN 2 WHEN 'Baja' THEN 3 ELSE 4 END {$sortDir}"
            )->orderBy('created_at', 'desc');
        } else {
            $query->orderBy($sortBy, $sortDir);
        }

        $perPage = min((int) $request->query('per_page', 15), 100);

        return RegulatoryInsightResource::collection(
            $query->paginate($perPage)->withQueryString()
        );
    }

    public function store(StoreRegulatoryInsightRequest $request): JsonResponse
    {
        if ($request->isBatch()) {
            return $this->storeBatch($request);
        }

        $result = $this->persister->persist(
            $request->insightAttributes(),
            $request->fullPayload()
        );

        $status = $result['created'] ? 201 : 200;

        return (new RegulatoryInsightResource($result['insight']))
            ->response()
            ->setStatusCode($status);
    }

    public function show(RegulatoryInsight $regulatoryInsight): RegulatoryInsightResource
    {
        return new RegulatoryInsightResource($regulatoryInsight);
    }

    public function update(
        UpdateRegulatoryInsightRequest $request,
        RegulatoryInsight $regulatoryInsight
    ): RegulatoryInsightResource {
        $insight = $this->persister->update(
            $regulatoryInsight,
            $request->insightAttributes(),
            $request->fullPayload()
        );

        return new RegulatoryInsightResource($insight);
    }

    public function destroy(RegulatoryInsight $regulatoryInsight): JsonResponse
    {
        $regulatoryInsight->delete();

        return response()->json(null, 204);
    }

    private function storeBatch(StoreRegulatoryInsightRequest $request): JsonResponse
    {
        $result = $this->persister->persistMany(
            $request->batchInsightAttributes(),
            $request->batchRawItems()
        );

        $status = $result['created'] > 0 ? 201 : 200;

        return response()->json([
            'data' => RegulatoryInsightResource::collection($result['insights']),
            'meta' => [
                'total' => count($result['insights']),
                'created' => $result['created'],
                'updated' => $result['updated'],
            ],
        ], $status);
    }
}
