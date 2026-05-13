<?php

namespace App\Filament\Resources\Rooms\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Forms\Components\TextArea;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Toggle;


class RoomForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Room Details')
                    ->tabs([
                         Tab::make('Basic Info')
                            ->label(__('messages.basic_info'))
                            ->components([
                                    Select::make('property_id')
                                    ->label(__('messages.property'))
                                        ->relationship('property', 'name')
                                        ->required(),
                                    TextInput::make('name')
                                        ->label(__('messages.name'))
                                        ->required(),
                                    TextInput::make('number')
                                        ->label(__('messages.room_number'))
                                        ->required(),
                            ]),

                            Tab::make('Room Details')
                            ->label(__('messages.room_details'))
                            ->components([
                                TextArea::make('description')
                                    ->label(__('messages.description'))
                                    ->required(),
                                Select::make('amenities')
                                ->relationship('amenities', 'name')
                                ->preload()
                                ->searchable()
                                ->multiple(),
                                TextInput::make('price-per-night')
                                    ->label(__('messages.price_per_night'))
                                    ->required()
                                    ->numeric(),
                                TextInput::make('capacity')
                                    ->label(__('messages.capacity'))
                                    ->required()
                                    ->numeric(),
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
                                        ->directory('rooms')
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


            ]);
    }
}
