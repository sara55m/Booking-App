<?php

namespace App\Filament\Resources\PropertyTypes\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Support\Str;

class PropertyTypeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('property_type_tabs')
                    ->tabs([
                        Tab::make('Basic Info')
                            ->label(__('messages.basic_info'))
                            ->schema([
                                TextInput::make('name')
                                    ->label(__('messages.name'))
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function ($state, $set) {
                                        $set('slug', Str::slug($state));
                                    }),
                                TextInput::make('slug')
                                    ->label(__('messages.slug'))
                                    ->required()
                                    ->unique(ignoreRecord: true),
                            ]),
                        Tab::make('Image & Sort Order')
                            ->label(__('messages.image_and_sort_order'))
                            ->schema([
                                FileUpload::make('image')
                                    ->label(__('messages.image'))
                                    ->image()
                                    ->acceptedFileTypes([
                                        'image/jpg',
                                        'image/jpeg',
                                        'image/png',
                                        'image/webp',
                                    ])
                                    ->maxSize(2048) // KB = 2MB
                                    ->disk('public')
                                    ->directory('property-types'),
                                TextInput::make('sort_order')
                                    ->label(__('messages.sort_order'))
                                    ->required()
                                    ->numeric()
                                    ->default(0),
                                Toggle::make('is_active')
                                    ->label(__('messages.is_active'))
                                    ->required(),
                            ]),
                    ])->columns(2)->columnSpanFull(),
            ]);
    }
}
