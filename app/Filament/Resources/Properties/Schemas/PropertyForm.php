<?php

namespace App\Filament\Resources\Properties\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use App\Enums\PropertyType;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\FileUpload;

class PropertyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('property_tabs')
                    ->tabs([
                        Tab::make('Basic Info')
                            ->label(__('messages.basic_info'))
                            ->schema([
                                TextInput::make('name')
                                ->label(__('messages.name'))
                                ->required(),
                                Textarea::make('description')
                                ->label(__('messages.description'))
                                ->rows(3),
                                Select::make('type')
                                ->label(__('messages.type'))
                                ->options(PropertyType::class)
                                ->required(),
                            ]),
                        Tab::make('Location')
                            ->label(__('messages.location'))
                            ->schema([
                                TextInput::make('city')
                                    ->label(__('messages.city'))
                                    ->required(),
                                Textarea::make('address')
                                    ->label(__('messages.address'))
                                    ->required(),
                            ]),
                        Tab::make('Facilities')
                            ->label(__('messages.facilities'))
                            ->schema([
                                Select::make('amenities')
                                ->label(__('messages.amenities'))
                                ->relationship('amenities', 'name')
                                ->multiple()
                                ->preload()
                                ->required(),
                            ]),
                        Tab::make('Images')
                        ->label(__('messages.images'))
                        ->schema([
                            Repeater::make('images')
                            ->label(__('messages.images'))
                            ->relationship()
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
                                    ->directory('properties')
                                    ->required(),

                                Toggle::make('is_cover')
                                    ->label(__('messages.cover_image')),

                                TextInput::make('sort_order')
                                    ->label(__('messages.sort_order'))
                                    ->numeric()
                                    ->default(0),
                            ])
                            ->collapsible()
                            ->cloneable()
                            ->reorderable()
                            ->minItems(2)
                            ->rules([
                                function () {
                                    return function (string $attribute, $value, $fail) {
                                        $coverCount = collect($value ?? [])
                                            ->where('is_cover', true)
                                            ->count();

                                        if ($coverCount < 1) {
                                            $fail(__('messages.select_one_cover_image'));
                                        }

                                        if ($coverCount > 1) {
                                            $fail(__('messages.only_one_cover_image_allowed'));
                                        }
                                    };
                                },
                            ])
                            ->addActionLabel(__('messages.add_image')),
                        ]),
                    ])->columns(2)->columnSpanFull(),

                Toggle::make('is_active')
                    ->default(true),
            ]);
    }
}
