<?php

namespace Tests\Feature\WaterBank;

use App\Data\AddManualWaterData;
use App\Data\WaterGoalData;
use App\Enums\FundingSourceEnum;
use App\Models\User;
use App\Services\WaterBankService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WaterGoalServiceTest extends TestCase
{
    use RefreshDatabase;

    private WaterBankService $waterBankService;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Bind the concrete repository implementations for testing
        $this->app->bind(
            \App\Repositories\SavingsGoal\SavingsGoalRepositoryInterface::class,
            \App\Repositories\SavingsGoal\DBSavingsGoalRepository::class
        );

        $this->app->bind(
            \App\Repositories\WaterBank\WaterBankRepositoryInterface::class,
            \App\Repositories\WaterBank\DBWaterBankRepository::class
        );

        $this->waterBankService = app(WaterBankService::class);
        $this->user = User::factory()->create();
    }

    /**
     * Helper: Create a savings goal for testing.
     */
    private function createGoal(string $name, float $target, float $current = 0.00): \App\Models\SavingsGoal
    {
        return $this->user->savingsGoals()->create([
            'name' => $name,
            'target_amount' => $target,
            'current_amount' => $current,
            'is_completed' => false,
        ]);
    }

    /**
     * Helper: Add water to bank.
     */
    private function addWaterToBank(float $amount, string $description = 'Test water'): void
    {
        $this->waterBankService->addManualWater(
            $this->user,
            new AddManualWaterData($amount, $description)
        );
    }

    /**
     * Helper: Get current water bank balance.
     */
    private function getWaterBankBalance(): float
    {
        return $this->waterBankService->getWaterBankStatus($this->user)['balance'];
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_returns_excess_water_to_bank_when_goal_is_overwatered()
    {
        // ARRANGE: Set up the scenario using helper methods
        $goal = $this->createGoal('Emergency Fund', 100.00);
        $this->addWaterToBank(2000.00, 'Initial water');

        // Verify initial state
        $this->assertEquals(2000.00, $this->getWaterBankBalance());

        // ACT: Water the goal with $200 (more than needed)
        $result = $this->waterBankService->waterGoal(
            $this->user,
            $goal->id,
            new WaterGoalData(200.00, FundingSourceEnum::WATER_BANK)
        );

        // ASSERT: Check the expected outcomes

        // 1. Goal should be completed with exactly the target amount
        $goal->refresh();
        $this->assertTrue($goal->is_completed);
        $this->assertEquals(100.00, $goal->current_amount);

        // 2. Water bank should only be reduced by what was actually needed ($100)
        // Expected: $2000 - $100 = $1900 (NOT $1800!)
        $this->assertEquals(1900.00, $this->getWaterBankBalance(),
            'Water bank should only be reduced by the amount actually needed for the goal'
        );

        // 3. The service should indicate how much was actually used
        $this->assertArrayHasKey('actual_amount_used', $result);
        $this->assertEquals(100.00, $result['actual_amount_used']);

        // 4. The service should indicate how much was saved (not taken from bank)
        $this->assertArrayHasKey('amount_returned', $result);
        $this->assertEquals(100.00, $result['amount_returned']); // This is amount NOT taken from bank
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_does_not_return_water_when_goal_needs_full_amount()
    {
        // ARRANGE: Goal needs exactly what we're watering
        $goal = $this->user->savingsGoals()->create([
            'name' => 'Vacation Fund',
            'target_amount' => 500.00,
            'current_amount' => 300.00, // Needs exactly $200 more
            'is_completed' => false,
        ]);

        $this->waterBankService->addManualWater(
            $this->user,
            new AddManualWaterData(1000.00, 'Initial water')
        );

        // ACT: Water with exactly the needed amount
        $result = $this->waterBankService->waterGoal(
            $this->user,
            $goal->id,
            new WaterGoalData(200.00, FundingSourceEnum::WATER_BANK)
        );

        // ASSERT: No excess should be returned
        $goal->refresh();
        $this->assertTrue($goal->is_completed);
        $this->assertEquals(500.00, $goal->current_amount);

        $finalWaterBank = $this->waterBankService->getWaterBankStatus($this->user);
        $this->assertEquals(800.00, $finalWaterBank['balance']); // 1000 - 200

        $this->assertEquals(200.00, $result['actual_amount_used']);
        $this->assertEquals(0.00, $result['amount_returned']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_throws_exception_when_user_has_insufficient_water()
    {
        // ARRANGE: Create goal and add insufficient water
        $goal = $this->createGoal('Expensive Car', 10000.00);
        $this->addWaterToBank(100.00, 'Small amount of water');

        // Verify initial state
        $this->assertEquals(100.00, $this->getWaterBankBalance());

        // ACT & ASSERT: Expect InsufficientWaterException when trying to use more than available
        $this->expectException(\App\Exceptions\InsufficientWaterException::class);

        $this->waterBankService->waterGoal(
            $this->user,
            $goal->id,
            new WaterGoalData(500.00, FundingSourceEnum::WATER_BANK) // Trying to use $500 but only have $100
        );

        // These assertions should not be reached if exception is thrown correctly
        // But let's verify the state wasn't changed
        $goal->refresh();
        $this->assertEquals(0.00, $goal->current_amount, 'Goal should not be modified when exception occurs');
        $this->assertEquals(100.00, $this->getWaterBankBalance(), 'Water bank should not be modified when exception occurs');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_throws_exception_when_goal_does_not_exist()
    {
        // ARRANGE: Add water but don't create the goal
        $this->addWaterToBank(1000.00, 'Plenty of water');
        $nonExistentGoalId = 999; // Goal that doesn't exist

        // ACT & ASSERT: Expect SavingsGoalNotFoundException
        $this->expectException(\App\Exceptions\SavingsGoalNotFoundException::class);

        $this->waterBankService->waterGoal(
            $this->user,
            $nonExistentGoalId,
            new WaterGoalData(100.00, FundingSourceEnum::WATER_BANK)
        );

        // Water bank should not be modified when exception occurs
        $this->assertEquals(1000.00, $this->getWaterBankBalance(), 'Water bank should not be modified when goal not found');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_throws_exception_when_trying_to_water_another_users_goal()
    {
        // ARRANGE: Create another user with their own goal
        $otherUser = User::factory()->create();
        $otherUsersGoal = $otherUser->savingsGoals()->create([
            'name' => 'Other Users Goal',
            'target_amount' => 500.00,
            'current_amount' => 0.00,
            'is_completed' => false,
        ]);

        // Current user has water
        $this->addWaterToBank(1000.00, 'My water');

        // ACT & ASSERT: Current user tries to water other user's goal
        $this->expectException(\App\Exceptions\SavingsGoalNotFoundException::class);

        $this->waterBankService->waterGoal(
            $this->user, // Current user
            $otherUsersGoal->id, // But other user's goal!
            new WaterGoalData(100.00, FundingSourceEnum::WATER_BANK)
        );

        // Neither user's state should be modified
        $this->assertEquals(1000.00, $this->getWaterBankBalance(), 'Current user water should be unchanged');
        $otherUsersGoal->refresh();
        $this->assertEquals(0.00, $otherUsersGoal->current_amount, 'Other users goal should be unchanged');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_handles_partial_watering_correctly()
    {
        // ARRANGE: Goal needs more than what we're providing
        $goal = $this->user->savingsGoals()->create([
            'name' => 'Car Fund',
            'target_amount' => 1000.00,
            'current_amount' => 0.00,
            'is_completed' => false,
        ]);

        $this->waterBankService->addManualWater(
            $this->user,
            new AddManualWaterData(2000.00, 'Initial water')
        );

        // ACT: Water with less than needed
        $result = $this->waterBankService->waterGoal(
            $this->user,
            $goal->id,
            new WaterGoalData(300.00, FundingSourceEnum::WATER_BANK)
        );

        // ASSERT: Goal should not be completed, full amount should be used
        $goal->refresh();
        $this->assertFalse($goal->is_completed);
        $this->assertEquals(300.00, $goal->current_amount);

        $finalWaterBank = $this->waterBankService->getWaterBankStatus($this->user);
        $this->assertEquals(1700.00, $finalWaterBank['balance']); // 2000 - 300

        $this->assertEquals(300.00, $result['actual_amount_used']);
        $this->assertEquals(0.00, $result['amount_returned']);
    }
}
