<?php

namespace App\Filament\Resources\Reviews\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use App\Enums\ReviewStatus;
use Filament\Infolists\Components\RepeatableEntry;

class ReviewInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Review')
                    ->tabs([
                        Tab::make(__('messages.review_details'))
                            ->schema([
                                TextEntry::make('booking.reference')
                                    ->label(__('messages.booking')),

                                TextEntry::make('user.name')
                                    ->label(__('messages.user')),

                                TextEntry::make('property.name')
                                    ->label(__('messages.property')),

                                TextEntry::make('created_at')
                                    ->label(__('messages.created_at'))
                                    ->dateTime(),
                            ])
                            ->columns(2),

                            Tab::make(__('messages.review_content'))
                            ->schema([
                                TextEntry::make('rating')
                                    ->label(__('messages.rating'))
                                    ->badge()
                                    ->suffix('/5'),

                                TextEntry::make('status')
                                    ->label(__('messages.status'))
                                    ->color(fn (ReviewStatus $state) => $state->color())
                                    ->badge(),

                                TextEntry::make('comment')
                                    ->label(__('messages.comment'))
                                    ->columnSpanFull()
                                    ->placeholder('-'),
                            ])
                            ->columns(2),

                            Tab::make(__('messages.review_categories'))
                            ->schema([
                                RepeatableEntry::make('categories')
                                    ->label(__('messages.review_categories'))
                                    ->schema([
                                        TextEntry::make('name')
                                            ->label(__('messages.name')),

                                        TextEntry::make('pivot.rating')
                                            ->label(__('messages.rating'))
                                            ->formatStateUsing(fn ($state) => "{$state} / 5")
                                            ->badge(),
                                    ])
                                    ->columns(2),
                            ])
                            ->columns(2),

                            Tab::make(__('messages.review_tags'))
                            ->schema([
                                TextEntry::make('tags')
                                    ->label(__('messages.review_tags'))
                                    ->state(fn ($record) => $record->tags->pluck('name')->toArray())
                                    ->badge(),
                            ])
                            ->columns(2),

                    ])
                    ->columnSpanFull(),
            ]);
    }
}
