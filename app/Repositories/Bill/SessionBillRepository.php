<?php

namespace App\Repositories\Bill;

use App\Data\BillData;
use App\DTOs\BillDTO;
use App\Models\User;
use App\Services\BillDateFormatter;
use Illuminate\Support\Collection;

class SessionBillRepository implements BillRepositoryInterface
{
    public function __construct(
        private BillDateFormatter $dateFormatter
    ) {}

    /**
     * Retrieve bills for a user from the session.
     *
     * @param User $user
     * @return Collection<BillDTO>
     */
    public function forUser(User $user): Collection
    {
        return collect(session('guest_bills', []))
            ->map(fn($raw) => $this->mapToDTO($raw));
    }

    /**
     * Create a new bill for the user.
     */
    public function create(User $user, BillData $data): BillDTO
    {
        $bills = session('guest_bills', []);

        $newBill = [
            'id' => $this->getNextId($bills),
            'name' => $data->name,
            'amount' => $data->amount,
            'date' => $data->date,
            'description' => $data->description,
            'created_at' => now()->toDateTimeString(),
        ];

        $bills[] = $newBill;
        session(['guest_bills' => $bills]);

        return $this->mapToDTO($newBill);
    }

    /**
     * Update an existing bill.
     */
    public function update(User $user, int $billId, BillData $data): BillDTO
    {
        $bills = session('guest_bills', []);
        $index = collect($bills)->search(fn($bill) => $bill['id'] === $billId);

        if ($index === false) {
            throw new \Exception("Bill not found");
        }

        $bills[$index] = array_merge($bills[$index], [
            'name' => $data->name,
            'amount' => $data->amount,
            'date' => $data->date,
            'description' => $data->description,
        ]);

        session(['guest_bills' => $bills]);

        return $this->mapToDTO($bills[$index]);
    }

    /**
     * Delete a bill.
     */
    public function delete(User $user, int $billId): bool
    {
        $bills = session('guest_bills', []);
        $filtered = collect($bills)->reject(fn($bill) => $bill['id'] === $billId);

        if ($filtered->count() === count($bills)) {
            return false; // Bill not found
        }

        session(['guest_bills' => $filtered->values()->toArray()]);
        return true;
    }

    /**
     * Find a specific bill by ID for the user.
     */
    public function findForUser(User $user, int $billId): ?BillDTO
    {
        $bills = session('guest_bills', []);
        $bill = collect($bills)->first(fn($bill) => $bill['id'] === $billId);

        return $bill ? $this->mapToDTO($bill) : null;
    }

    /**
     * Get next available ID for session bills.
     */
    private function getNextId(array $bills): int
    {
        if (empty($bills)) {
            return 1;
        }

        return collect($bills)->max('id') + 1;
    }

    /**
     * Map array to DTO.
     */
    private function mapToDTO(array $raw): BillDTO
    {
        return new BillDTO(
            id: $raw['id'],
            name: $raw['name'],
            amount: $raw['amount'],
            date: $this->dateFormatter->format($raw['date']),
            description: $raw['description'] ?? null,
            created_at: $raw['created_at'] ?? now()->toDateTimeString(),
        );
    }
}
