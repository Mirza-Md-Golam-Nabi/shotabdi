<?php
namespace App\Filament\Pages\Customers;

use App\Enums\CustomerEnum;
use App\Models\Customer;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class Type extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string $view = 'filament.pages.customers.type';

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $slug = 'customer-list';

    public ?string $c_type_id = null;

    public function mount()
    {
        $this->c_type_id = request()->query('type');

        $customerEnum = CustomerEnum::tryFrom($this->c_type_id);

        if (! $customerEnum) {
            redirect(route('filament.admin.pages.dashboard'));
            return;
        }
    }

    public function getSummaryProperty()
    {
        return Customer::selectRaw('
            SUM(CASE WHEN balance > 0 THEN balance ELSE 0 END) as positive_balance,
            SUM(CASE WHEN balance < 0 THEN balance ELSE 0 END) as negative_balance,
            COUNT(CASE WHEN balance > 0 THEN 1 END) as positive_customers,
            COUNT(CASE WHEN balance < 0 THEN 1 END) as negative_customers
        ')
            ->where('type', $this->c_type_id)
            ->first();
    }

    public function getHeading(): string
    {
        $heading = CustomerEnum::tryFrom($this->c_type_id)?->bangla() . " গ্রাহক";

        return __($heading);
    }

    public function getTitle(): string
    {
        return $this->getHeading();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(function () {
                return Customer::where('type', $this->c_type_id);
            })
            ->recordUrl(function (Model $record) {
                return route('filament.admin.pages.details-customer', [
                    'customer_id' => $record->id,
                ]);
            })
            ->columns([
                TextColumn::make('name')
                    ->label('নাম')
                    ->searchable()
                    ->extraAttributes([
                        'style' => 'padding: 0.25rem 0 !important;',
                    ])
                    ->sortable(),

                TextColumn::make('balance')
                    ->label('বর্তমান হিসাব')
                    ->alignment('right')
                    ->formatStateUsing(fn($state) => format_number($state) ?? 0)
                    ->extraAttributes([
                        'style' => 'padding: 0.25rem 0 !important;',
                    ])
                    ->sortable(),

            ])
            ->defaultSort('name', 'asc')
            ->filters([])
            ->actions([])
            ->bulkActions([]);
    }
}
