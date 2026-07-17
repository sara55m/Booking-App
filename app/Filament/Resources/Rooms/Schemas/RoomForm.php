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
use App\Models\RoomType;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;

class RoomForm
{
    public static function configure(Schema $schema): Schema
    {
        $fillRoomTypeData = function (?string $state, Set $set) {
            $roomType = RoomType::find($state);
            if(!$roomType){
                return;
            }
            $set('capacity', $roomType?->capacity);
            $set('price_per_night', $roomType?->base_price);
        };

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
                                        ->live()
                                        ->afterStateUpdated(fn (Set $set) => $set('room_type_id', null))
                                        ->required(),

                                    Select::make('room_type_id')
                                        ->label(__('messages.room_type'))
                                        ->options(fn (Get $get) =>
                                            RoomType::query()
                                                ->where('property_id', $get('property_id'))
                                                ->pluck('name', 'id')
                                        )
                                        ->preload()
                                        ->live()
                                        ->afterStateHydrated($fillRoomTypeData)
                                        ->afterStateUpdated($fillRoomTypeData)
                                        ->required(),
                                    TextInput::make('capacity')
                                        ->disabled()
                                        ->dehydrated(false),
                                    TextInput::make('price_per_night')
                                        ->disabled()
                                        ->dehydrated(false)
                                        ->suffix('EGP'),
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
