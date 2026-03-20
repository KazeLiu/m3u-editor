<?php

namespace App\Filament\Auth;

use Filament\Forms;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EditProfile extends \Filament\Auth\Pages\EditProfile
{
    /**
     * After the profile is saved, clear the must_change_password flag
     * if the user just fulfilled a forced-password-change requirement.
     */
    protected function afterSave(): void
    {
        $user = auth()->user();
        if ($user && $user->must_change_password && filled($this->data['password'] ?? null)) {
            $user->update(['must_change_password' => false]);
        }
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->description('Update your profile information')
                    ->schema([
                        // Forms\Components\FileUpload::make('avatar_url')
                        //     ->disk('public')
                        //     ->avatar(),
                        // TextInput::make('username')->required()->maxLength(255),
                        $this->getNameFormComponent(),

                        // $this->getEmailFormComponent(),
                        $this->getPasswordFormComponent()
                            ->helperText('Leave blank to keep the current password'),
                        $this->getPasswordConfirmationFormComponent(),
                    ]),
            ]);
    }
}
