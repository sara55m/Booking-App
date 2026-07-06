<?php

namespace App\Filament\Resources\Cities\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Support\Str;

class CityForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('city_tabs')
                    ->tabs([
                        Tab::make('Basic Info')
                            ->label(__('messages.basic_info'))
                            ->schema([
                                TextInput::make('name')
                                    ->label(__('messages.name'))
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(fn ($state, callable $set) =>
                                        $set('slug', Str::slug($state))
                                    ),
                                TextInput::make('slug')
                                    ->label(__('messages.slug'))
                                    ->required()
                                    ->unique(ignoreRecord: true),
                            ]),
                            Tab::make('Image & Status')
                            ->label(__('messages.image'))
                            ->schema([
                                FileUpload::make('image')
                                ->label(__('messages.image'))
                                ->required()
                                ->image()
                                ->acceptedFileTypes([
                                    'image/jpg',
                                    'image/jpeg',
                                    'image/png',
                                    'image/webp',
                                ])
                                ->maxSize(2048) // KB = 2MB
                                ->disk('public')
                                ->directory('cities'),
                                Toggle::make('is_active')
                                    ->label(__('messages.is_active'))
                                    ->required(),
                            ]),
                    ])->columns(2)->columnSpanFull(),
            ]);
    }
}
