<?php

namespace App\Filament\Resources\RewardPoints\Pages;

use App\Filament\Resources\RewardPoints\RewardPointResource;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Widgets\RewardPointsStats;

class ListRewardPoints extends ListRecords
{
    protected static string $resource = RewardPointResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            RewardPointsStats::class,
        ];
    }
}
