<?php

namespace App\Http\Controllers\Api;

use App\Enums\PortalStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreConnectedPortalRequest;
use App\Http\Requests\UpdateConnectedPortalRequest;
use App\Http\Resources\ConnectedPortalResource;
use App\Models\ConnectedPortal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ConnectedPortalController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $sortBy = $request->query('sort_by', 'created_at');
        $sortDir = strtolower($request->query('sort_dir', 'desc')) === 'asc' ? 'asc' : 'desc';

        if (! in_array($sortBy, ['created_at', 'name'], true)) {
            $sortBy = 'created_at';
        }

        $query = ConnectedPortal::query()
            ->filter($request->only(['country_code', 'status', 'category']))
            ->orderBy($sortBy, $sortDir);

        $perPage = min((int) $request->query('per_page', 100), 100);

        return ConnectedPortalResource::collection(
            $query->paginate($perPage)->withQueryString()
        );
    }

    public function store(StoreConnectedPortalRequest $request): JsonResponse
    {
        $data = $request->validated();

        if (! isset($data['status'])) {
            $data['status'] = PortalStatus::Activo;
        }

        $portal = ConnectedPortal::query()->create($data);

        return (new ConnectedPortalResource($portal))
            ->response()
            ->setStatusCode(201);
    }

    public function show(ConnectedPortal $connectedPortal): ConnectedPortalResource
    {
        return new ConnectedPortalResource($connectedPortal);
    }

    public function update(
        UpdateConnectedPortalRequest $request,
        ConnectedPortal $connectedPortal,
    ): ConnectedPortalResource {
        $connectedPortal->update($request->validated());

        return new ConnectedPortalResource($connectedPortal->fresh());
    }

    public function destroy(ConnectedPortal $connectedPortal): JsonResponse
    {
        $connectedPortal->delete();

        return response()->json(null, 204);
    }
}
