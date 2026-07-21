<?php

namespace App\Filament\Resources\TravelCategories\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TextArea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Support\Str;
use Filament\Forms\Components\Select;

class TravelCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('travel_category_tabs')
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
                                TextArea::make('description')
                                    ->label(__("messages.description"))
                                    ->nullable(),
                            ]),
                            Tab::make('Icon')
                            ->label(__('messages.icon'))
                            ->schema([
                                Select::make('icon')
                                ->label(__("messages.icon"))
                                    ->options([
                                        'heroicon-o-globe-alt' => '🌍 General Travel',
                                        'heroicon-o-map' => '🗺️ Sightseeing',
                                        'heroicon-o-map-pin' => '📍 Destinations',
                                        'heroicon-o-building-library' => '🏛️ Cultural Experiences',
                                        'heroicon-o-building-office-2' => '🏙️ City Breaks',
                                        'heroicon-o-home-modern' => '🏡 Countryside',
                                        'heroicon-o-mountain' => '⛰️ Mountains & Hiking',
                                        'heroicon-o-sun' => '🏖️ Beaches',
                                        'heroicon-o-sparkles' => '✨ Luxury Spas',
                                        'heroicon-o-fire' => '🏜️ Desert Adventures',
                                        'heroicon-o-paper-airplane' => '✈️ Adventure Travel',
                                        'heroicon-o-camera' => '📷 Photography',
                                        'heroicon-o-binoculars' => '🔭 Wildlife & Safari',
                                        'heroicon-o-tree-pine' => '🌲 Nature',
                                        'heroicon-o-bolt' => '⚡ Extreme Sports',
                                        'heroicon-o-heart' => '💕 Romantic Getaways',
                                        'heroicon-o-users' => '👨‍👩‍👧 Family Friendly',
                                        'heroicon-o-user-group' => '🧑‍🤝‍🧑 Group Travel',
                                        'heroicon-o-cake' => '🍰 Food & Cooking',
                                        'heroicon-o-shopping-bag' => '🛍️ Shopping',
                                        'heroicon-o-musical-note' => '🎵 Music & Festivals',
                                        'heroicon-o-calendar-days' => '🎉 Events & Festivals',
                                        'heroicon-o-moon' => '🌙 Nightlife',
                                        'heroicon-o-book-open' => '📚 History & Heritage',
                                        'heroicon-o-academic-cap' => '🎓 Educational Trips',
                                        'heroicon-o-trophy' => '🏆 Sports Tourism',
                                        'heroicon-o-lifebuoy' => '🤿 Diving & Snorkeling',
                                        'heroicon-o-cloud' => '🌤️ Outdoor Escapes',
                                        'heroicon-o-rocket-launch' => '🚀 Unique Experiences',
                                        'heroicon-o-star' => '⭐ Popular Destinations',
                                    ])
                                    ->searchable()
                                    ->required(),
                            ]),
                            Tab::make('Is_active and Sort Order')
                            ->label(__('messages.is_active_and_sort_order'))
                            ->schema([
                                Toggle::make('is_active')
                                    ->label(__("messages.is_active"))
                                    ->required(),
                                TextInput::make('sort_order')
                                    ->label(__('messages.sort_order'))
                                    ->numeric()
                                    ->default(0),
                            ]),
                        ])->columns(2)->columnSpanFull(),
            ]);
    }
}
