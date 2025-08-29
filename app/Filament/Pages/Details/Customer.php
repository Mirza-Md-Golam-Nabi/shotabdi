<?php
namespace App\Filament\Pages\Details;

use App\Models\Customer as CustomerModel;
use App\Models\Transaction;
use Filament\Pages\Page;
use Illuminate\Support\Collection;

class Customer extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.details.customer';

    protected static ?string $slug = 'details-customer';

    protected static bool $shouldRegisterNavigation = false;

    protected ?string $heading = '';

    public ?Collection $transactions = null;

    public CustomerModel $customer;

    public string $route;

    public function mount()
    {
        $customer_id = request()->query('customer_id');

        $this->route = 'filament.admin.pages.details-product';

        $this->customer = CustomerModel::find($customer_id);

        $this->transactions = Transaction::with(
            'stock_in.product',
            'stock_out.product'
        )
            ->where('customer_id', $customer_id)
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        $current = $this->customer->balance;
        foreach ($this->transactions as $tran) {
            $tran->product_id = $tran->stock_in
            ? $tran->stock_in->product_id
            : (
                $tran->stock_out
                ? $tran->stock_out->product_id
                : null
            );
            $tran->current_balance = $current;
            $current += $tran->effectOnBalance($this->customer);
        }

    }
}
