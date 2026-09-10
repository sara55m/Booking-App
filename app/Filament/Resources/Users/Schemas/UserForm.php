<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Resources\Pages\ViewRecord;
use Filament\Forms\Components\Toggle;
use App\Models\User;
use Filament\Schemas\Components\Utilities\Get;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make()
                ->schema([
                    Tab::make(__("messages.basic_info"))
                        ->schema([
                            TextInput::make('name')
                                ->label(__("messages.name"))
                                ->required()
                                ->maxLength(255),

                            TextInput::make('email')
                                ->label(__("messages.email"))
                                ->email()
                                ->required()
                                ->unique(ignoreRecord: true),

                            TextInput::make('phone')
                                ->label(__("messages.phone"))
                                ->tel(),

                            Select::make('role')
                                ->label(__("messages.role"))
                                //only super admin can edit role
                                ->options(fn () => auth()->user()->role === 'super_admin'
                                    ? [
                                        'user' => 'User',
                                        'admin' => 'Admin',
                                        'super_admin' => 'Super Admin',
                                    ]
                                    : [
                                        'user' => 'User',
                                    ])
                                ->default('user')
                                ->required()
                                ->disabled(fn (?User $record) => $record?->is(auth()->user())),

                            Toggle::make('receive_marketing_emails')
                                ->label(__("messages.receive_marketing_emails"))
                                ->required()
                                ->default(true),

                            FileUpload::make('image')
                                ->label(__("messages.image"))
                                ->image()
                                ->disk('public')
                                ->directory('profile_images')
                                ->imageEditor(),
                        ])->columns(2)->columnSpanFull(),

                    Tab::make(__('messages.security'))
                        ->icon('heroicon-o-lock-closed')
                        ->hidden(fn ($livewire) => $livewire instanceof ViewRecord)
                        ->schema([
                            TextInput::make('password')
                                ->label(__('messages.password'))
                                ->password()
                                ->revealable()
                                ->required(fn (string $operation) => $operation === 'create')
                                ->minLength(8)
                                ->dehydrated(fn (?string $state) => filled($state))
                                ->autocomplete('new-password'),

                            TextInput::make('password_confirmation')
                                ->label(__('messages.confirm_password'))
                                ->password()
                                ->revealable()
                                ->same('password')
                                ->required(fn (string $operation) => $operation === 'create')
                                ->dehydrated(false)
                                ->autocomplete('new-password'),
                        ])->columns(2)->columnSpanFull(),

                    Tab::make(__('messages.preferences'))
                        ->schema([
                            Select::make('locale')
                                ->label(__('messages.language'))
                                ->options([
                                    'en' => __('messages.english'),
                                    'ar' => __('messages.arabic'),
                                ])
                                ->default('en')
                                ->required(),

                            Select::make('currency')
                                ->label(__('messages.currency'))
                                ->options([
                                    'USD' => 'USD',
                                    'EGP' => 'EGP',
                                    'EUR' => 'EUR',
                                ])
                                ->required(),
                        ])
                        ->columns(2)
                        ->columnSpanFull()
                        ->visible(fn (Get $get): bool => $get('role') === 'user'),
                    ])->columns(2)->columnSpanFull(),
            ]);
    }
}
