<?php

namespace App\Filament\Resources\ReviewTags\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;

class ReviewTagForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('messages.review_tag_details'))
                ->components([
                    TextInput::make('name')
                    ->label(__("messages.name"))
                    ->required()
                    ->maxLength(255),
                Select::make('type')
                    ->label(__("messages.type"))
                    ->options([
                        'positive' => __("messages.positive"),
                        'negative' => __("messages.negative"),
                        'neutral' => __("messages.neutral")
                        ])
                    ->default('positive')
                    ->required(),
                Toggle::make('is_active')
                    ->label(__("messages.is_active"))
                    ->required()
                    ->default(true),
                ])->columns(2)->columnSpanFull(),
            ]);
    }
}
