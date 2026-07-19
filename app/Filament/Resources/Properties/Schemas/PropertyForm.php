<?php

namespace App\Filament\Resources\Properties\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextArea;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\FileUpload;
use SalemAljebaly\FilamentMapPicker\MapPicker;
use Filament\Schemas\Components\Section;
use App\Models\City;


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
                                TextArea::make('description')
                                ->label(__('messages.description'))
                                ->rows(3),
                                Select::make('property_type_id')
                                ->relationship('PropertyType', 'name')
                                ->label(__('messages.type'))
                                ->preload()
                                ->required(),
                                TextInput::make('minimum_partial_payment_percentage')
                                    ->label(__('messages.minimum_partial_payment_percentage'))
                                    ->numeric()
                                    ->minValue(1)
                                    ->maxValue(100)
                                    ->suffix('%')
                                    ->helperText(__('messages.minimum_partial_payment_percentage_help'))
                                    ->nullable(),
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

                                TextArea::make('caption')
                                ->label(__("messages.description"))
                                ->nullable(),
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
                        Tab::make('status')
                            ->label(__('messages.status'))
                            ->schema([
                                Toggle::make('is_featured')
                                    ->label(__('messages.featured'))
                                    ->default(false),

                                Toggle::make('is_active')
                                    ->label(__('messages.active'))
                                    ->default(true),
                            ]),
                    ])->columns(2)->columnSpanFull(),
                    Section::make(__('messages.location'))
                        ->schema([
                            Select::make('city_id')
                                ->relationship('city', 'name')
                                ->label(__('messages.city'))
                                ->preload()
                                ->required()
                                ->live()
                                ->afterStateUpdated(function ($state, callable $set) {
                                    $city = City::find($state);
                                
                                    if ($city) {
                                        $set('latitude', $city->latitude);
                                        $set('longitude', $city->longitude);
                                    }
                                }),
                            Textarea::make('address')
                                ->label(__('messages.address'))
                                ->required(),
                            TextInput::make('latitude')
                            ->label(__("messages.latitude"))
                            ->numeric()
                            ->required()
                            ->live()
                            ->readOnly(),

                            TextInput::make('longitude')
                                ->label(__("messages.longitude"))
                                ->numeric()
                                ->required()
                                ->live()
                                ->readOnly(),

                            MapPicker::make('location')
                                ->label(__('messages.location'))
                                ->latlngFields('latitude', 'longitude')
                                ->dehydrated(false)
                                ->searchable()
                                ->collapsibleSearch()
                                ->draggable()
                                ->height(500),
                        ])->columns(2)->columnSpanFull(),
            ]);
    }
}
