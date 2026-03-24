<?php

namespace App\Filament\Resources\ChannelScrubbers\Pages;

use App\Filament\Resources\ChannelScrubbers\ChannelScrubberResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewChannelScrubber extends ViewRecord
{
    protected static string $resource = ChannelScrubberResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
            DeleteAction::make(),
        ];
    }
}
