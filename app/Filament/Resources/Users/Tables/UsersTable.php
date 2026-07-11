<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\DatePicker;
use Illuminate\Support\Facades\Storage;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Actions\DeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\Action;
use App\Filament\Resources\Bookings\BookingResource;
use App\Filament\Resources\Reviews\ReviewResource;
use App\Filament\Resources\Properties\PropertyResource;
use App\Filament\Resources\RewardPoints\RewardPointResource;
USE App\Models\User;
use Filament\Actions\ExportAction;
use App\Filament\Exports\UserExporter;
use Filament\Actions\ExportBulkAction;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
            TextColumn::make('id')
                ->label(__("messages.id"))
                ->sortable(),

            TextColumn::make('name')
                ->label(__("messages.name"))
                ->searchable()
                ->sortable(),

            TextColumn::make('email')
                ->label(__("messages.email"))
                ->searchable()
                ->copyable(),

            TextColumn::make('role')
                ->label(__("messages.role"))
                ->searchable()
                ->copyable(),

            ImageColumn::make('image')
                ->label(__('messages.image'))
                ->circular()
                ->imageSize(60)
                ->getStateUsing(function ($record) {
                    return $record->image
                        ? Storage::disk('public')->url($record->image)
                        : asset('storage/profile_images/avatar.jpeg');
                }),

            IconColumn::make('email_verified')
                ->label(__('messages.verified'))
                ->state(fn ($record) => $record->email_verified_at !== null)
                ->boolean(),

            TextColumn::make('reward_points')
                ->label(__('messages.points'))
                ->badge()
                ->color('warning')
                ->sortable(),

            TextColumn::make('bookings_count')
                ->counts('bookings')
                ->label(__('messages.bookings'))
                ->badge(),

            TextColumn::make('favorite_properties_count')
                ->counts('favoriteProperties')
                ->label(__('messages.favorites'))
                ->badge(),

            TextColumn::make('created_at')
                ->label(__("messages.created_at"))
                ->dateTime()
                ->sortable(),
            TextColumn::make('deleted_at')
                ->label(__('messages.deleted_at'))
                ->dateTime()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true)
                ->placeholder('-'),
            ])
            ->filters([
                TrashedFilter::make(),
                TernaryFilter::make('email_verified_at')
                    ->label(__('messages.email_verified'))
                    ->nullable()
                    ->placeholder(__('messages.all'))
                    ->trueLabel(__('messages.verified'))
                    ->falseLabel(__('messages.not_verified')),
                //users with reward points
                Filter::make('has_reward_points')
                    ->query(fn (Builder $query) => $query->where('reward_points', '>', 0))
                    ->label(__('messages.has_reward_points')),

                //creation date filter
                Filter::make('created_at')
                    ->label(__('messages.registration_date'))
                    ->schema([
                        DatePicker::make('from')
                            ->label(__('messages.from')),

                        DatePicker::make('until')
                            ->label(__('messages.until')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'] ?? null,
                                fn (Builder $query, $date) => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['until'] ?? null,
                                fn (Builder $query, $date) => $query->whereDate('created_at', '<=', $date),
                            );
                    }),

                //users with bookings
                TernaryFilter::make('bookings')
                ->label(__('messages.bookings'))
                ->placeholder(__('messages.all'))
                ->trueLabel(__('messages.has_bookings'))
                ->falseLabel(__('messages.no_bookings'))
                ->queries(
                    true: fn (Builder $query) => $query->has('bookings'),
                    false: fn (Builder $query) => $query->doesntHave('bookings'),
                    blank: fn (Builder $query) => $query,
                ),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
                RestoreAction::make(),
                ForceDeleteAction::make(),
                //view only the user bookings from the booking resource
                Action::make('viewBookings')
                    ->label(__('messages.view_bookings'))
                    ->icon('heroicon-o-calendar')
                    ->url(fn (User $record) => BookingResource::getUrl('index', [
                        'filters' => [
                            'user_id' => [
                                'value' => $record->id,
                            ],
                        ],
                    ])),
                //view only the user reviews from the booking resource
                Action::make('viewReviews')
                ->label(__('messages.view_reviews'))
                ->icon('heroicon-o-chat-bubble-oval-left')
                ->url(fn (User $record) => ReviewResource::getUrl('index', [
                    'filters' => [
                        'user_id' => [
                            'value' => $record->id,
                        ],
                    ],
                ])),
                //view only properties that are favorited by the user
                Action::make('viewFavorites')
                    ->label(__('messages.view_favorites'))
                    ->icon('heroicon-o-heart')
                    ->color('danger')
                    ->url(fn (User $record) => PropertyResource::getUrl('index', [
                        'filters' => [
                            'favorite_user' => [
                                'value' => $record->id,
                            ],
                        ],
                    ])),
                //view user reward points history
                Action::make('viewRewardPointsHistory')
                ->label(__('messages.view_history'))
                ->icon('heroicon-o-gift')
                ->color('danger')
                ->url(fn (User $record) => RewardPointResource::getUrl('index', [
                    'filters' => [
                        'user_id' => [
                            'value' => $record->id,
                        ],
                    ],
                ])),
            ])
            ->headerActions([
                ExportAction::make()
                    ->exporter(UserExporter::class),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    //export all users
                    ExportBulkAction::make()
                    ->exporter(UserExporter::class),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
