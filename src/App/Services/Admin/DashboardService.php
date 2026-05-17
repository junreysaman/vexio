<?php

declare(strict_types=1);

namespace App\Services\Admin;

use App\Services\Media\MediaCatalogService;

class DashboardService
{
    public function __construct(private MediaCatalogService $media)
    {
    }

    public function overview(): array
    {
        return [
            'stats' => $this->media->dashboardStats(),
            'trending' => $this->media->trendingByViews(8),
        ];
    }
}
