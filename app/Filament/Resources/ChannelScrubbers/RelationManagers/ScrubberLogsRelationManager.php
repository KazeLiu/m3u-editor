<?php

namespace App\Filament\Resources\ChannelScrubbers\RelationManagers;

use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\Layout\Panel;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ScrubberLogsRelationManager extends RelationManager
{
    protected static string $relationship = 'logs';

    protected static ?string $title = 'Run Logs';

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading('Run Logs')
            ->recordTitleAttribute('created_at')
            ->defaultSort('created_at', 'desc')
            ->paginated([10, 25])
            ->defaultPaginationPageOption(10)
            ->columns([
                Split::make([
                    TextColumn::make('created_at')
                        ->label('Ran At')
                        ->dateTime()
                        ->sortable()
                        ->toggleable(),
                    Split::make([
                        TextColumn::make('status')
                            ->badge()
                            ->color(fn (string $state) => $state === 'completed' ? 'success' : 'danger')
                            ->sortable()
                            ->toggleable(),
                        TextColumn::make('channel_count')
                            ->label('Checked')
                            ->suffix(' channels')
                            ->sortable()
                            ->toggleable(),
                        TextColumn::make('dead_count')
                            ->label('Dead Links')
                            ->color(fn ($state) => $state > 0 ? 'danger' : 'success')
                            ->suffix(fn ($state) => $state === 1 ? ' dead link' : ' dead links')
                            ->sortable()
                            ->toggleable(),
                        TextColumn::make('runtime')
                            ->label('Runtime')
                            ->formatStateUsing(fn ($state): string => $state ? gmdate('H:i:s', (int) $state) : '-')
                            ->sortable()
                            ->toggleable(),
                    ])->grow(false),
                ])->from('md'),
                Panel::make([
                    Stack::make([
                        TextColumn::make('meta')
                            ->label('Dead Channels')
                            ->formatStateUsing(function ($state, $record): string {
                                if (empty($record->meta)) {
                                    return 'No dead channels found.';
                                }
                                $lines = collect($record->meta)
                                    ->map(fn ($ch) => "• {$ch['title']} — {$ch['url']}")
                                    ->join("\n");

                                return $lines;
                            })
                            ->html(false),
                    ]),
                ])->collapsible(),
            ])
            ->headerActions([])
            ->recordActions([
                DeleteAction::make()->button()->hiddenLabel()->size('sm'),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ]);
    }
}
