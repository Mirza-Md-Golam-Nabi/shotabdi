<?php
namespace App\Filament\Resources;

use App\Filament\Forms\ProductForm;
use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use App\Models\Stock;
use App\Models\StockIn;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ForceDeleteAction;
use Filament\Tables\Actions\ForceDeleteBulkAction;
use Filament\Tables\Actions\RestoreAction;
use Filament\Tables\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema(ProductForm::fields());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordUrl(function (Model $record) {
                return route('filament.admin.pages.details-product', [
                    'product_id' => $record->id,
                ]);
            })
            ->columns([
                TextColumn::make('name')
                    ->searchable(),

                TextColumn::make('stock.quantity')
                    ->label('বর্তমান স্টক')
                    ->sortable()
                    ->formatStateUsing(fn($state, $record) =>
                        $record->id == 1
                        ? floor(($state / 30)) . ' খাঁচি'
                        : $state . ' বস্তা'
                    ),

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
            ->filters([
                TrashedFilter::make(),
            ])
            ->actions([
                EditAction::make()
                    ->iconButton()
                    ->using(function (Product $record, array $data) {
                        $record->update($data);
                        return $record;
                    })
                    ->after(function (Product $record, array $data) {
                        $product_id = $record->id;
                        $quantity   = $data['quantity'];

                        StockIn::where('product_id', $product_id)
                            ->update([
                                'quantity' => $quantity,
                            ]);

                        Stock::where('product_id', $product_id)
                            ->update([
                                'quantity'  => $quantity,
                                'available' => $quantity,
                            ]);
                    }),
                DeleteAction::make()
                    ->iconButton(),
                ForceDeleteAction::make(),
                RestoreAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageProducts::route('/'),
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
