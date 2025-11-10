<?php
namespace App\Filament\Pages;

use App\Enums\FeedDisburseEnum;
use App\Models\FeedDisburse as FeedDisburseModel;
use Carbon\Carbon;
use Filament\Pages\Page;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class FeedDisburse extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.feed-disburse';

    protected static bool $shouldRegisterNavigation = false;

    protected ?string $heading = 'Feed Disburse List';

    public function table(Table $table): Table
    {
        return $table
            ->query(function () {
                $tenDaysAgo = Carbon::now()->subDays(10)->toDateString();

                return FeedDisburseModel::with('customer', 'product')
                    ->where(function ($query) use ($tenDaysAgo) {
                        $query->where('status', FeedDisburseEnum::Pending)
                            ->orWhere(function ($query) use ($tenDaysAgo) {
                                $query->whereIn('status', [FeedDisburseEnum::Delivered, FeedDisburseEnum::Cancel])
                                    ->whereDate('next_date', '>=', $tenDaysAgo);
                            });
                    })
                    ->orderByRaw("
                        CASE
                            WHEN status = ? THEN 1
                            WHEN status IN (?, ?) THEN 2
                            ELSE 3
                        END
                    ", [
                        FeedDisburseEnum::Pending,
                        FeedDisburseEnum::Delivered,
                        FeedDisburseEnum::Cancel,
                    ])
                    ->orderBy('next_date', 'asc');
            })
            ->striped()
            ->recordUrl(function (Model $record) {
                return route('filament.admin.pages.details-customer', [
                    'customer_id' => $record->customer_id,
                ]);
            })
            ->columns([
                TextColumn::make('customer.name')
                    ->label('নাম')
                    ->searchable()
                    ->size(TextColumn\TextColumnSize::ExtraSmall)
                    ->extraAttributes([
                        'style' => 'padding: 0.50rem 0.5rem !important;',
                    ])
                    ->extraHeaderAttributes([
                        'style' => 'padding: 0.50rem 0.5rem !important;',
                    ])
                    ->sortable(),

                TextColumn::make('product.name')
                    ->label('প্রোডাক্ট')
                    ->searchable()
                    ->size(TextColumn\TextColumnSize::ExtraSmall)
                    ->extraAttributes([
                        'style' => 'padding: 0.50rem 0.5rem !important;',
                    ])
                    ->extraHeaderAttributes([
                        'style' => 'padding: 0.50rem 0.5rem !important;',
                    ])
                    ->sortable(),

                TextColumn::make('status')
                    ->label('স্ট্যাটাস')
                    ->badge()
                    ->color(fn(FeedDisburseEnum $state) => $state->color())
                    ->formatStateUsing(fn(FeedDisburseEnum $state) => $state->label()),

                TextColumn::make('next_date')
                    ->label('পরবর্তী তারিখ')
                    ->alignCenter()
                    ->searchable()
                    ->size(TextColumn\TextColumnSize::ExtraSmall)
                    ->extraAttributes([
                        'style' => 'padding: 0.50rem 0.5rem !important;',
                    ])
                    ->extraHeaderAttributes([
                        'style' => 'padding: 0.50rem 0.5rem !important;',
                    ])
                    ->sortable(),

            ])
            ->defaultSort('customer.name', 'asc')
            ->filters([])
            ->actions([
                Action::make('skip')
                    ->icon('heroicon-o-x-circle')
                    ->label(false)
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('আপনি কি নিশ্চিত?')
                    ->modalDescription('এই কাস্টমারের স্ট্যাটাস "Skipped" করা হবে। আপনি কি চালিয়ে যেতে চান?')
                    ->visible(fn(FeedDisburseModel $record) => $record->status === FeedDisburseEnum::Pending)
                    ->action(function (FeedDisburseModel $record) {
                        $record->update([
                            'status' => FeedDisburseEnum::Cancel,
                        ]);

                        \Filament\Notifications\Notification::make()
                            ->title('স্ট্যাটাস সফলভাবে আপডেট হয়েছে!')
                            ->success()
                            ->send();
                    }),
            ], position: ActionsPosition::BeforeColumns)
            ->bulkActions([]);
    }

}
