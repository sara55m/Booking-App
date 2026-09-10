<?php

namespace App\Filament\Pages\Auth;

use Filament\Auth\Pages\EditProfile as BaseEditProfile;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EditProfile extends BaseEditProfile
{
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__("messages.basic_info"))
                    ->schema([
                        TextInput::make('name')
                            ->label(__("messages.name"))
                            ->required()
                            ->maxLength(255),

                        TextInput::make('email')
                            ->label(__("messages.email"))
                            ->email()
                            ->required(),

                        TextInput::make('phone')
                            ->label(__("messages.phone"))
                            ->tel(),

                        FileUpload::make('image')
                            ->image()
                            ->label(__("messages.image"))
                            ->disk('public')
                            ->directory('profile_images')
                            ->imageEditor(),
                    ])
                    ->columns(2),

                Section::make(__("messages.account"))
                    ->schema([
                        Select::make('role')
                            ->options([
                                'user' => 'User',
                                'admin' => 'Admin',
                                'super_admin' => 'Super Admin',
                            ])
                            ->label(__("messages.role"))
                            ->disabled(),
                    ]),

                $this->getPasswordFormComponent(),
                $this->getPasswordConfirmationFormComponent(),
            ]);
    }
}
