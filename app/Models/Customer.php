<?php
namespace App\Models;

use App\Enums\CashFlowEnum;
use App\Enums\CustomerEnum;
use App\Enums\OperationEnum;
use App\Models\StockIn;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

class Customer extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'mobile', 'address', 'balance', 'type'];

    protected $casts = [
        'type' => CustomerEnum::class,
    ];

    public function getCustomerTypeAttribute(): int
    {
        return $this->type->value;
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'customer_id', 'id');
    }

    public function deposits(): HasMany
    {
        return $this->hasMany(StockIn::class, 'customer_id', 'id');
    }

    public function isCompany(): bool
    {
        return $this->type === CustomerEnum::Company;
    }

    public function isFarmer(): bool
    {
        return $this->type === CustomerEnum::Farmer;
    }

    public function isEggSeller(): bool
    {
        return $this->type === CustomerEnum::EggSeller;
    }

    public function isNormal(): bool
    {
        return $this->type === CustomerEnum::Normal;
    }

    public static function selectOption(string $sort = 'asc'): Collection
    {
        return self::orderBy('name', $sort)->pluck('name', 'id');
    }

    public function stockInOperation()
    {
        return $this->isCompany() ? true : false;
    }

    public function stockOutOperation()
    {
        return $this->isCompany() ? true : false;
    }

    public function transactionOperation(CashFlowEnum $type, bool $reverse = false): string
    {
        $operation = match ($type) {
            CashFlowEnum::Deposit => match (true) {
                $this->isCompany(), $this->isEggSeller() => OperationEnum::Add,
                $this->isFarmer(), $this->isNormal()     => OperationEnum::Subtract,
                default => OperationEnum::Add,
            },
            CashFlowEnum::Expense => match (true) {
                $this->isCompany(), $this->isEggSeller() => OperationEnum::Subtract,
                $this->isFarmer(), $this->isNormal()     => OperationEnum::Add,
                default => OperationEnum::Subtract,
            },
        };

        return $reverse
            ? $operation->reverse()->value
            : $operation->value;
    }
}
