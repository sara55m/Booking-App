<?php

namespace App\Filament\Resources\Amenities\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Filament\Infolists\Components\IconEntry;
use Filament\Schemas\Components\Section;

class AmenityInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('messages.amenity_details'))
                    ->label(__('messages.amenity_details'))
                    ->components([
                        TextEntry::make('name')
                        ->label(__('messages.name')),

                        IconEntry::make('icon')
                        ->label(__('messages.icon'))
                        ->icon(fn ($record) => $record->icon),
                        TextEntry::make('created_at')
                            ->label(__('messages.created_at'))
                            ->dateTime()
                            ->placeholder('-'),
                        TextEntry::make('updated_at')
                            ->label(__('messages.updated_at'))
                            ->dateTime()
                            ->placeholder('-'),
                    ])->columns(2),

            ]);

    }
}
