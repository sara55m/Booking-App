<?php

namespace App\Filament\Resources\Countries\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;

class CountryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('country_tabs')
                    ->tabs([
                        Tab::make('Basic Info')
                            ->label(__('messages.basic_info'))
                            ->schema([
                                TextInput::make('name')
                                    ->label(__("messages.name"))
                                    ->required(),
                                TextInput::make('iso_code')
                                    ->label(__("messages.iso_code"))
                                    ->maxLength(2)
                                    ->required(),
                                TextInput::make('currency')
                                    ->label(__("messages.currency"))
                                    ->required(),
                            ]),
                            Tab::make('Flag')
                            ->label(__('messages.flag'))
                            ->schema([
                                FileUpload::make('flag')
                                    ->label(__("messages.flag"))
                                    ->image()
                                    ->maxSize(2048)
                                    ->disk('public')
                                    ->directory('countries')
                                    ->required(),

                                Toggle::make('is_active')
                                    ->label(__("messages.is_active"))
                                    ->required(),
                            ]),
                    ])->columns(2)->columnSpanFull(),
            ]);
    }
}
