<?php

namespace App\Repositories\Contracts;

use App\DTOs\BillDTO;
use App\Models\User;
use Illuminate\Support\Collection;

interface BillRepositoryInterface
{
    /**
     * @return Collection<BillDTO>
     */
    public function forUser(User $user): Collection;

    /**
     * Create a new bill for the user.
     */
    public function create(User $user, array $data): BillDTO;

    /**
     * Update an existing bill.
     */
    public function update(User $user, int $billId, array $data): BillDTO;

    /**
     * Delete a bill.
     */
    public function delete(User $user, int $billId): bool;

    /**
     * Find a specific bill by ID for the user.
     */
    public function findForUser(User $user, int $billId): ?BillDTO;
}
