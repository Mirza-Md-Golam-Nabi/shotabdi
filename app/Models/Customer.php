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

    public function isOther(): bool
    {
        return $this->type === CustomerEnum::Others;
    }

    public function isBank(): bool
    {
        return $this->type === CustomerEnum::Bank;
    }

    public static function selectOption(string $sort = 'asc', int | array | null $type = null): Collection
    {
        return self::query()
            ->when($type, function ($q) use ($type) {
                if (is_array($type)) {
                    $q->whereIn('type', $type);
                } else {
                    $q->where('type', $type);
                }
            })
            ->orderBy('name', $sort)
            ->pluck('name', 'id');
    }

    public static function selectRemain(string $sort = 'asc', ?int $type = null): Collection
    {
        $exclude = ExcludeCustomerId::pluck('customer_id')->toArray();
        return self::query()
            ->when($exclude->isNotEmpty(), fn($q) => $q->whereNotIn('id', $exclude))
            ->when($type, function ($q) use ($type) {
                $q->where('type', $type);
            })
            ->orderBy('name', $sort)
            ->pluck('name', 'id');
    }

    public static function bankOption(string $sort = 'asc'): array
    {
        return self::where('type', CustomerEnum::Bank)
            ->orderBy('name', $sort)
            ->pluck('name', 'id')
            ->toArray();
    }

    public static function customerWithoutBank(string $sort = 'asc'): array
    {
        return self::where('type', '!=', CustomerEnum::Bank)
            ->orderBy('name', $sort)
            ->pluck('name', 'id')
            ->toArray();
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
                $this->isBank() => OperationEnum::Add,
                $this->isCompany(), $this->isFarmer(), $this->isEggSeller(), $this->isNormal(), $this->isOther() => OperationEnum::Subtract,
                default => OperationEnum::Add,
            },
            CashFlowEnum::Expense => match (true) {
                $this->isBank() => OperationEnum::Subtract,
                $this->isCompany(), $this->isFarmer(), $this->isEggSeller(), $this->isNormal(), $this->isOther() => OperationEnum::Add,
                default => OperationEnum::Subtract,
            },
        };

        return $reverse
            ? $operation->reverse()->value
            : $operation->value;
    }

    public function updateBalance(int $balance, string $operation = 'add'): bool
    {
        return match ($operation) {
            'add'      => (bool) $this->increment('balance', $balance),
            'subtract' => (bool) $this->decrement('balance', $balance),
            default    => false,
        };
    }

    public function determineAmount(CashFlowEnum $type, int $amount)
    {
        $sign = $this->transactionOperation($type);
        return match ($sign) {
            'add'      => -$amount,
            'subtract' => $amount,
            default    => $amount,
        };
    }
}
