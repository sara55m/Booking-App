<?php

namespace App\Filament\Resources\Cities\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TextArea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Support\Str;
use Filament\Forms\Components\Repeater;
use SalemAljebaly\FilamentMapPicker\MapPicker;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;

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
                                Select::make('country_id')
                                ->relationship('country','name')
                                ->preload()
                                ->searchable()
                                ->label(__("messages.country"))
                                ->required(),
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

                                TextArea::make('description')
                                    ->label(__("messages.description"))
                                    ->nullable(),
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
                                        ->directory('cities')
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
                                ->minItems(1)
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
                            ->label(__("messages.status"))
                            ->schema([
                                Toggle::make('is_featured')
                                    ->label(__('messages.featured'))
                                    ->default(false),

                                Toggle::make('is_active')
                                    ->label(__('messages.active'))
                                    ->default(true),
                            ]),
                    ])->columns(2)->columnSpanFull(),
                    Section::make(__("messages.location"))
                            ->schema([
                                TextInput::make('latitude')
                                    ->label(__("messages.latitude"))
                                    ->numeric()
                                    ->required()
                                    ->live(),

                                TextInput::make('longitude')
                                    ->label(__("messages.longitude"))
                                    ->numeric()
                                    ->required()
                                    ->live(),

                                MapPicker::make('location')
                                    ->label(__('messages.location'))
                                    ->latlngFields('latitude', 'longitude')
                                    ->searchable()
                                    ->collapsibleSearch()
                                    ->draggable()
                                    ->height(500),
                            ])->columns(2)->columnSpanFull(),
            ]);
    }
}
