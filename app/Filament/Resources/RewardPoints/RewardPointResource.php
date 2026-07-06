<?php

namespace App\Filament\Resources\RewardPoints;

use App\Filament\Resources\RewardPoints\Pages\ListRewardPoints;
use App\Filament\Resources\RewardPoints\Schemas\RewardPointForm;
use App\Filament\Resources\RewardPoints\Tables\RewardPointsTable;
use App\Models\RewardPoint;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use App\Filament\Widgets\RewardPointsStats;
use Filament\Tables\Table;

class RewardPointResource extends Resource
{
    protected static ?string $model = RewardPoint::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-gift';

    public static function getNavigationGroup(): ?string
    {
        return __('messages.reward_points');
    }
    protected static ?int $navigationSort = 11;

    public static function getModelLabel(): string
    {
        return __('messages.reward_point');
    }

    public static function getPluralModelLabel(): string
    {
        return __('messages.reward_points');
    }

    public static function getNavigationLabel(): string
    {
        return __('messages.reward_points');
    }

    public static function getWidgets(): array
    {
        return [
            RewardPointsStats::class,
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return RewardPointForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RewardPointsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRewardPoints::route('/'),
            //'create' => CreateRewardPoint::route('/create'),
            //'edit' => EditRewardPoint::route('/{record}/edit'),
        ];
    }
}
