<?php

namespace App\Filament\Resources\Reviews\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;
use App\Enums\ReviewStatus;
use App\Models\Booking;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;


class ReviewForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Review Details')
                    ->tabs([
                        Tab::make(__('messages.review_details'))
                            ->schema([
                                //remember make the booking id disabled
                                Select::make('booking_id')
                                    ->relationship('booking','id')
                                    ->label(__('messages.booking'))
                                    ->reactive()
                                    ->afterStateUpdated(function ($state,$set) {
                                        $booking = Booking::find($state);
                                        if ($booking) {
                                            $set('user_id', $booking->user_id);
                                            $set('property_id', $booking->property_id);
                                        }
                                    }),
                                Select::make('user_id')
                                    ->relationship('user', 'name')
                                    ->label(__('messages.user'))
                                    ->disabled()
                                    ->dehydrated(true),
                                Select::make('property_id')
                                    ->relationship('property', 'name')
                                    ->disabled()
                                    ->dehydrated(true)
                                    ->label(__('messages.property')),
                            ]),
                            Tab::make(__('messages.review_content'))
                            ->schema([
                                TextInput::make('rating')
                                    ->label(__('messages.rating'))
                                    ->required()
                                    ->numeric()
                                    ->minValue(1)
                                    ->maxValue(5),
                                Textarea::make('comment')
                                    ->label(__('messages.comment'))
                                    ->columnSpanFull(),
                                Select::make('status')
                                    ->label(__('messages.status'))
                                    ->required()
                                    ->options(ReviewStatus::class)
                                    ->default(ReviewStatus::Pending),
                            ])
                    ])->columns(2)
                    ->columnSpanFull()
            ]);
    }
}
