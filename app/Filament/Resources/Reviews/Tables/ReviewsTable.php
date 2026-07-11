<?php

namespace App\Filament\Resources\Reviews\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Actions\ViewAction;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\Action;
use Filament\Tables\Filters\Filter;
use App\Models\Review;
use App\Filament\Resources\Bookings\BookingResource;
use App\Enums\ReviewStatus;

class ReviewsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->searchable()
                    ->sortable()
                    ->label(__('messages.user')),
                TextColumn::make('booking.reference')
                    ->searchable()
                    ->sortable()
                    ->label(__('messages.booking')),
                TextColumn::make('property.name')
                    ->searchable()
                    ->sortable()
                    ->label(__('messages.property')),
                TextColumn::make('rating')
                    ->numeric()
                    ->formatStateUsing(fn ($state) => $state . ' ⭐')
                    ->sortable()
                    ->label(__('messages.rating')),
                TextColumn::make('status')
                    ->label(__('messages.status'))
                    ->badge()
                    ->color(fn (ReviewStatus $state) => $state->color())
                    ->default('pending'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label(__('messages.created_at')),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label(__('messages.updated_at')),
            ])
            ->filters([
                SelectFilter::make('user_id')
                    ->relationship('user', 'name')
                    ->preload()
                    ->searchable()
                    ->label(__('messages.user')),

                SelectFilter::make('property_id')
                    ->relationship('property', 'name')
                    ->preload()
                    ->searchable()
                    ->label(__('messages.property')),

                SelectFilter::make('booking')
                    ->relationship('booking', 'reference')
                    ->preload()
                    ->searchable()
                    ->label(__('messages.booking_reference')),

                SelectFilter::make('rating')
                    ->label(__('messages.rating'))
                    ->options([
                        1 => '1',
                        2 => '2',
                        3 => '3',
                        4 => '4',
                        5 => '5',
                    ]),
                    Filter::make('high_rating')
                        ->label(__('messages.high_rating') . ' (4-5)')
                        ->query(fn ($query) => $query->where('rating', '>=', 4)),

                    Filter::make('low_rating')
                        ->label(__('messages.low_rating') . ' (1-2)')
                        ->query(fn ($query) => $query->where('rating', '<=', 2)),
                    SelectFilter::make('status')
                    ->label(__('messages.status'))
                    ->options([
                        ReviewStatus::Pending->value => __('messages.pending'),
                        ReviewStatus::Approved->value => __('messages.approved'),
                        ReviewStatus::Rejected->value => __('messages.rejected'),
                    ]),
            ])
            ->recordActions([
                //approve and reject actions
                Action::make('approve')
                ->action(fn($record) => $record->update(['status' => ReviewStatus::Approved]))
                ->label(__('messages.approve'))
                ->color('success')
                ->icon('heroicon-o-check')
                ->visible(fn($record) => $record->status !== ReviewStatus::Approved),

                Action::make('reject')
                ->action(fn($record) => $record->update(['status' =>ReviewStatus::Rejected]))
                ->label(__('messages.reject'))
                ->color('danger')
                ->icon('heroicon-o-x-mark')
                ->visible(fn($record) => $record->status !== ReviewStatus::Rejected),

                ViewAction::make(),
                EditAction::make(),

                Action::make('view_booking')
                    ->label(__('messages.view_booking'))
                    ->icon('heroicon-o-calendar')
                    ->url(fn (Review $record) => BookingResource::getUrl('view', [
                        'record' => $record->booking,
                    ]))
                    ->openUrlInNewTab(false),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
