<?php

namespace App\Filament\Resources\Rooms;

use App\Filament\Resources\Rooms\Pages\CreateRoom;
use App\Filament\Resources\Rooms\Pages\EditRoom;
use App\Filament\Resources\Rooms\Pages\ListRooms;
use App\Filament\Resources\Rooms\Schemas\RoomForm;
use App\Filament\Resources\Rooms\Tables\RoomsTable;
use App\Models\Room;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use App\Filament\Resources\Rooms\Pages\ViewRoom;

class RoomResource extends Resource
{
    protected static ?string $model = Room::class;

    public static function getNavigationGroup(): ?string
    {
        return __('messages.rooms');
    }
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-key';
    protected static ?int $navigationSort = 5;

    public static function getModelLabel(): string
    {
        return __('messages.room');
    }

    public static function getPluralModelLabel(): string
    {
        return __('messages.rooms');
    }

    public static function getNavigationLabel(): string
    {
        return __('messages.rooms');
    }

    public static function form(Schema $schema): Schema
    {
        return RoomForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RoomsTable::configure($table);
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
            'index' => ListRooms::route('/'),
            'create' => CreateRoom::route('/create'),
            'view' => ViewRoom::route('/{record}'),
            'edit' => EditRoom::route('/{record}/edit'),
        ];
    }
}
