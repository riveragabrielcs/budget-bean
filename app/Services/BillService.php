<?php

namespace App\Services;

use App\Data\BillData;
use App\DTOs\BillDTO;
use App\Exceptions\BillNotFoundException;
use App\Http\Requests\Bill\StoreBillRequest;
use App\Http\Requests\Bill\UpdateBillRequest;
use App\Models\User;
use App\Repositories\Bill\BillRepositoryInterface;
use Illuminate\Support\Collection;

class BillService
{
    public function __construct(
        private BillRepositoryInterface $billRepo
    ) {}

    /**
     * Create a new recurring bill.
     */
    public function createBill(?User $user, StoreBillRequest $request): BillDTO
    {
        $validated = $request->validated();

        $billData = new BillData(
            name: $validated['name'],
            amount: $validated['amount'],
            date: $validated['bill_date'] ?? null,
            description: $validated['description'] ?? null
        );

        return $this->billRepo->create($user, $billData);
    }

    /**
     * Update an existing bill.
     *
     * @throws BillNotFoundException
     */
    public function updateBill(?User $user, int $billId, UpdateBillRequest $request): BillDTO
    {
        $validated = $request->validated();

        // Get existing bill to preserve unchanged fields
        $existingBill = $this->billRepo->findForUser($user, $billId);

        if (!$existingBill) {
            throw new BillNotFoundException("Bill with ID {$billId} not found");
        }

        $billData = new BillData(
            name: $validated['name'] ?? $existingBill->name,
            amount: $validated['amount'] ?? $existingBill->amount,
            date: $validated['bill_date'] ?? $existingBill->date,
            description: $validated['description'] ?? $existingBill->description
        );

        return $this->billRepo->update($user, $billId, $billData);
    }

    /**
     * Delete a bill.
     *
     * @throws BillNotFoundException
     */
    public function deleteBill(?User $user, int $billId): array
    {
        // Get bill info before deletion for response message
        $bill = $this->billRepo->findForUser($user, $billId);

        if (!$bill) {
            throw new BillNotFoundException("Bill with ID {$billId} not found");
        }

        $billName = $bill->name;
        $deleted = $this->billRepo->delete($user, $billId);

        if (!$deleted) {
            throw new \RuntimeException('Failed to delete bill');
        }

        return [
            'name' => $billName,
            'deleted' => true
        ];
    }

    /**
     * Get all bills for user.
     */
    public function getBillsForUser(?User $user): Collection
    {
        return $this->billRepo->forUser($user);
    }

    /**
     * Find a specific bill for user.
     */
    public function findBillForUser(?User $user, int $billId): ?BillDTO
    {
        return $this->billRepo->findForUser($user, $billId);
    }

    /**
     * Format bill for API response.
     */
    public function formatForApiResponse(BillDTO $bill): array
    {
        return [
            'id' => $bill->id,
            'name' => $bill->name,
            'amount' => $bill->amount,
            'formatted_bill_date' => $bill->date,
            'description' => $bill->description,
            'type' => 'recurring_bill',
            'created_at' => $bill->created_at,
        ];
    }

    /**
     * Calculate bill statistics.
     */
    public function calculateBillStats(?User $user): array
    {
        $bills = $this->getBillsForUser($user);

        return [
            'total_bills' => $bills->count(),
            'total_monthly_amount' => $bills->sum('amount'),
            'average_bill_amount' => $bills->count() > 0 ? $bills->avg('amount') : 0,
        ];
    }

    /**
     * Calculate next due date for a bill.
     */
    public function calculateNextDueDate(?string $formattedDate): ?string
    {
        if (!$formattedDate) {
            return null;
        }

        // Extract numeric day from formatted date (e.g., "15th" -> 15)
        $billDate = (int) str_replace(['st', 'nd', 'rd', 'th'], '', $formattedDate);

        if ($billDate < 1 || $billDate > 31) {
            return null;
        }

        $today = now();

        // If the bill date hasn't passed this month, it's due this month
        if ($billDate >= $today->day) {
            $dueDate = now()->setDay($billDate);
        } else {
            // Otherwise, it's due next month
            $dueDate = now()->addMonth()->setDay($billDate);
        }

        return $dueDate->format('M j, Y');
    }
}
