<?php

use App\Http\Controllers\Api\ConnectedPortalController;
use App\Http\Controllers\Api\RegulatoryInsightController;
use Illuminate\Support\Facades\Route;

Route::middleware('api.read')->group(function (): void {
    Route::get('regulatory-insights', [RegulatoryInsightController::class, 'index']);
    Route::get('regulatory-insights/{regulatory_insight}', [RegulatoryInsightController::class, 'show']);

    Route::get('connected-portals', [ConnectedPortalController::class, 'index']);
    Route::post('connected-portals', [ConnectedPortalController::class, 'store']);
    Route::get('connected-portals/{connected_portal}', [ConnectedPortalController::class, 'show']);
    Route::put('connected-portals/{connected_portal}', [ConnectedPortalController::class, 'update']);
    Route::patch('connected-portals/{connected_portal}', [ConnectedPortalController::class, 'update']);
    Route::delete('connected-portals/{connected_portal}', [ConnectedPortalController::class, 'destroy']);
});

Route::middleware('api.token')->group(function (): void {
    Route::post('regulatory-insights', [RegulatoryInsightController::class, 'store']);
    Route::put('regulatory-insights/{regulatory_insight}', [RegulatoryInsightController::class, 'update']);
    Route::patch('regulatory-insights/{regulatory_insight}', [RegulatoryInsightController::class, 'update']);
    Route::delete('regulatory-insights/{regulatory_insight}', [RegulatoryInsightController::class, 'destroy']);
});
