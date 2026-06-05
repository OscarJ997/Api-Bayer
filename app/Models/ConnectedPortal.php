<?php

namespace App\Models;

use App\Enums\PortalCategory;
use App\Enums\PortalStatus;
use Database\Factories\ConnectedPortalFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'country_code',
    'name',
    'url',
    'description',
    'category',
    'status',
])]
class ConnectedPortal extends Model
{
    /** @use HasFactory<ConnectedPortalFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'category' => PortalCategory::class,
            'status' => PortalStatus::class,
        ];
    }

    /**
     * @param  Builder<self>  $query
     */
    public function scopeFilter(Builder $query, array $filters): void
    {
        if (isset($filters['country_code'])) {
            $query->where('country_code', strtoupper((string) $filters['country_code']));
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['category'])) {
            $query->where('category', $filters['category']);
        }
    }
}
