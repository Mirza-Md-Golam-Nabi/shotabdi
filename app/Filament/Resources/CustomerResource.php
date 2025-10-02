<?php
namespace App\Filament\Resources;

use App\Enums\CustomerEnum;
use App\Filament\Forms\CustomerForm;
use App\Filament\Resources\CustomerResource\Pages;
use App\Models\Customer;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema(CustomerForm::fields());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordUrl(function (Model $record) {
                return route('filament.admin.pages.details-customer', [
                    'customer_id' => $record->id,
                ]);
            })
            ->columns([
                TextColumn::make('name')
                    ->label('নাম')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('balance')
                    ->label('বর্তমান হিসাব')
                    ->sortable(),
                TextColumn::make('mobile')
                    ->label('ফোন নাম্বার')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('type')
                    ->label('ধরণ')
                    ->searchable(query: function ($query, string $search): void {
                        $matching = collect(CustomerEnum::cases())
                            ->filter(fn($case) => str_contains($case->bangla(), $search))
                            ->map(fn($case) => $case->value);

                        $query->whereIn('type', $matching);
                    })
                    ->badge()
                    ->formatStateUsing(fn(CustomerEnum $state) => $state->bangla())
                    ->color(fn(CustomerEnum $state) => $state->color()),
                TextColumn::make('address')
                    ->label('ঠিকানা')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('name', 'asc')
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->iconButton(),
                Tables\Actions\DeleteAction::make()
                    ->iconButton(),
                Tables\Actions\ForceDeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageCustomers::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
