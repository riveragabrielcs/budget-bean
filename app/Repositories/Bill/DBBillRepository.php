<?php

namespace App\Repositories\Bill;

use App\Data\BillData;
use App\DTOs\BillDTO;
use App\Models\RecurringBill;
use App\Models\User;
use App\Services\BillDateFormatter;
use Illuminate\Support\Collection;

class DBBillRepository implements BillRepositoryInterface
{
    public function __construct(
        private BillDateFormatter $dateFormatter
    ) {}

    /**
     *  Retrieve bills for a user from the database.
     *
     * @param User $user
     * @return Collection<BillDTO>
     */
    public function forUser(User $user): Collection
    {
        return $user->recurringBills()
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($bill) => $this->mapToDTO($bill));
    }

    /**
     * Create a new bill for the user.
     */
    public function create(User $user, BillData $data): BillDTO
    {
        $bill = $user->recurringBills()->create([
            'name' => $data->name,
            'amount' => $data->amount,
            'bill_date' => $data->date,
            'description' => $data->description,
        ]);

        return $this->mapToDTO($bill);
    }

    /**
     * Update an existing bill.
     */
    public function update(User $user, int $billId, BillData $data): BillDTO
    {
        $bill = $user->recurringBills()->findOrFail($billId);

        $bill->update([
            'name' => $data->name,
            'amount' => $data->amount,
            'bill_date' => $data->date,
            'description' => $data->description,
        ]);

        return $this->mapToDTO($bill->fresh());
    }

    /**
     * Delete a bill.
     */
    public function delete(User $user, int $billId): bool
    {
        $bill = $user->recurringBills()->findOrFail($billId);
        return $bill->delete();
    }

    /**
     * Find a specific bill by ID for the user.
     */
    public function findForUser(User $user, int $billId): ?BillDTO
    {
        $bill = $user->recurringBills()->find($billId);

        return $bill ? $this->mapToDTO($bill) : null;
    }

    /**
     * Map Eloquent model to DTO.
     */
    private function mapToDTO(RecurringBill $bill): BillDTO
    {
        return new BillDTO(
            id: $bill->id,
            name: $bill->name,
            amount: $bill->amount,
            date: $this->dateFormatter->format($bill->bill_date),
            description: $bill->description,
            created_at: $bill->created_at->toDateTimeString()
        );
    }
}
