<?php

namespace App\Repositories\Bill;

use App\Data\BillData;
use App\DTOs\BillDTO;
use App\Models\User;
use Illuminate\Support\Collection;

interface BillRepositoryInterface
{
    /**
     * Retrieve bills for a user.
     *
     * @return Collection<BillDTO>
     */
    public function forUser(User $user): Collection;

    /**
     * Create a new bill for the user.
     *
     * @param User $user
     * @param BillData $data
     * @return BillDTO
     */
    public function create(User $user, BillData $data): BillDTO;

    /**
     * Update an existing bill.
     *
     * @param User $user
     * @param int $billId
     * @param BillData $data
     */
    public function update(User $user, int $billId, BillData $data): BillDTO;

    /**
     * Delete a bill.
     *
     * @param User $user
     * @param int $billId
     * @return bool
     */
    public function delete(User $user, int $billId): bool;

    /**
     * Find a specific bill by ID for the user.
     *
     * @param User $user
     * @param int $billId
     * @return BillDTO|null
     */
    public function findForUser(User $user, int $billId): ?BillDTO;
}
