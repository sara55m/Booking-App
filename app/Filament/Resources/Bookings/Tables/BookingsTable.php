<?php

namespace App\Filament\Resources\Bookings\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\Action;
use App\Models\Booking;
use App\Filament\Resources\Payments\PaymentResource;
use App\Filament\Resources\Reviews\ReviewResource;
use App\Enums\BookingStatus;
class BookingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('reference')
                    ->label(__("messages.reference"))
                    ->sortable()
                    ->searchable()
                    ->placeholder('-'),
                TextColumn::make('user.name')
                    ->label(__('messages.user'))
                    ->sortable(),
                TextColumn::make('property.name')
                    ->label(__('messages.property'))
                    ->sortable(),
                TextColumn::make('room.display_name')
                    ->label(__('messages.room')),
                TextColumn::make('nights_count')
                    ->label(__('messages.number_of_nights'))
                    ->sortable(),
                TextColumn::make('total_price')
                    ->label(__('messages.total_price'))
                    ->money('EGP')
                    ->sortable(),
                TextColumn::make('status')
                    ->label(__('messages.status'))
                    ->badge()
                    ->color(fn (BookingStatus $state) => $state->color())
                    ->searchable(),
                TextColumn::make('created_at')
                    ->label(__('messages.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('messages.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                ->label(__('messages.status'))
                ->options([
                    'pending' => __('messages.pending'),
                    'confirmed' => __('messages.confirmed'),
                    'checked_in' => __('messages.checked_in'),
                    'checked_out' => __('messages.checked_out'),
                    'completed' => __('messages.completed'),
                    'cancelled' => __('messages.cancelled'),
                ]),

                SelectFilter::make('property_id')
                    ->relationship('property', 'name')
                    ->label(__('messages.property'))
                    ->searchable()
                    ->preload(),

                SelectFilter::make('room_type_name')
                    ->relationship('room.roomType', 'name')
                    ->label(__('messages.room_type'))
                    ->searchable()
                    ->preload(),

                SelectFilter::make('room_number')
                    ->relationship('room', 'number')
                    ->label(__('messages.room_number'))
                    ->searchable()
                    ->preload(),

                SelectFilter::make('user_id')
                    ->relationship('user', 'name')
                    ->label(__('messages.user'))
                    ->searchable()
                    ->preload(),

                Filter::make('upcoming_bookings')
                    ->label(__('messages.upcoming_bookings'))
                    ->query(fn ($query) =>
                        $query->whereDate('check_in','>', today())),

                Filter::make('past_bookings')
                    ->label(__('messages.past_bookings'))
                    ->query(fn ($query) =>
                        $query->whereDate('check_out','<', today())),

                Filter::make('current_bookings')
                ->label(__('messages.current_bookings'))
                ->query(fn ($query) =>
                    $query->whereDate('check_in', '<=', today())
                            ->whereDate('check_out', '>=', today())
                ),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                //view payments action
                Action::make('view_payments')
                    ->label(__('messages.view_payments'))
                    ->icon('heroicon-o-credit-card')
                    ->visible(fn (Booking $record) => $record->payments()->exists())
                    ->url(fn (Booking $record) => PaymentResource::getUrl('index', [
                        'filters' => [
                            'booking' => [
                                'value' => $record->id,
                            ],
                        ],
                    ])),
                    //view review
                Action::make('view_review')
                    ->label(__('messages.view_review'))
                    ->icon('heroicon-o-chat-bubble-oval-left')
                    ->visible(fn (Booking $record) => $record->review()->exists())
                    ->url(fn (Booking $record) => ReviewResource::getUrl('view', [
                        'record' => $record->review,
                    ]))
                    ->openUrlInNewTab(false),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                ]),
            ]);
    }
}
