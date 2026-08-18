<?php

namespace App\Filament\Resources\ReviewCategories\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use Filament\Schemas\Components\Section;

class ReviewCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('messages.review_category_details'))
                    ->components([
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

                        TextInput::make('sort_order')
                            ->label(__("messages.sort_order"))
                            ->required()
                            ->numeric()
                            ->integer()
                            ->minValue(0)
                            ->default(0),

                        Toggle::make('is_active')
                            ->label(__("messages.is_active"))
                            ->default(true)
                            ->required(),
                            ])->columns(2)->columnSpanFull(),
            ]);
    }
}
