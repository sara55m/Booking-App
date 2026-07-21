<?php

namespace App\Filament\Resources\Amenities;

use App\Filament\Resources\Amenities\Pages\CreateAmenity;
use App\Filament\Resources\Amenities\Pages\EditAmenity;
use App\Filament\Resources\Amenities\Pages\ListAmenities;
use App\Filament\Resources\Amenities\Pages\ViewAmenity;
use App\Filament\Resources\Amenities\Schemas\AmenityForm;
use App\Filament\Resources\Amenities\Schemas\AmenityInfolist;
use App\Filament\Resources\Amenities\Tables\AmenitiesTable;
use App\Models\Amenity;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class AmenityResource extends Resource
{
    protected static ?string $model = Amenity::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-sparkles';

    public static function getNavigationGroup(): ?string
    {
        return __('messages.amenities');
    }
    protected static ?int $navigationSort = 5;

    public static function getModelLabel(): string
    {
        return __('messages.amenity');
    }

    public static function getPluralModelLabel(): string
    {
        return __('messages.amenities');
    }

    public static function getNavigationLabel(): string
    {
        return __('messages.amenities');
    }

    public static function form(Schema $schema): Schema
    {
        return AmenityForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return AmenityInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AmenitiesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAmenities::route('/'),
            'create' => CreateAmenity::route('/create'),
            'view' => ViewAmenity::route('/{record}'),
            'edit' => EditAmenity::route('/{record}/edit'),
        ];
    }
}
