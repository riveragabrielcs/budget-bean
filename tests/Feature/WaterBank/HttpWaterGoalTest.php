<?php

namespace Tests\Feature\WaterBank;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HttpWaterGoalTest extends TestCase
{
    use RefreshDatabase;

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_prevents_overwatering_through_http_request()
    {
        // ARRANGE: Create authenticated user with goal and water
        $user = User::factory()->create();

        $goal = $user->savingsGoals()->create([
            'name' => 'Emergency Fund',
            'target_amount' => 100.00,
            'current_amount' => 0.00,
            'is_completed' => false,
        ]);

        // Add water to bank through HTTP (simulating real app flow)
        $this->actingAs($user)
            ->post(route('water-bank.add-manual'), [
                'amount' => 2000.00,
                'description' => 'Initial water'
            ])
            ->assertRedirect();

        // Verify initial water bank balance
        $response = $this->actingAs($user)
            ->get(route('water-bank.status'))
            ->assertJson(['success' => true]);

        $this->assertEquals(2000.00, $response->json('water_bank.balance'));

        // ACT: Water the goal with excess amount through HTTP
        $this->actingAs($user)
            ->post(route('water-bank.water-goal', $goal), [
                'amount' => 200.00,
                'source' => 'water_bank'
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        // ASSERT: Check that only needed amount was used
        $goal->refresh();
        $this->assertTrue($goal->is_completed);
        $this->assertEquals(100.00, $goal->current_amount);

        // Check water bank balance through HTTP API
        $response = $this->actingAs($user)
            ->get(route('water-bank.status'))
            ->assertJson(['success' => true]);

        $finalBalance = $response->json('water_bank.balance');

        // This should be 1900 (2000 - 100), not 1800 (2000 - 200)
        $this->assertEquals(1900.00, $finalBalance,
            'HTTP request should only use needed amount, not full requested amount'
        );
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_handles_insufficient_water_through_http()
    {
        // ARRANGE: User with goal but insufficient water
        $user = User::factory()->create();

        $goal = $user->savingsGoals()->create([
            'name' => 'Expensive Goal',
            'target_amount' => 1000.00,
            'current_amount' => 0.00,
            'is_completed' => false,
        ]);

        // Add only small amount to bank
        $this->actingAs($user)
            ->post(route('water-bank.add-manual'), [
                'amount' => 50.00,
                'description' => 'Small water'
            ]);

        // ACT & ASSERT: Try to water with more than available
        $this->actingAs($user)
            ->post(route('water-bank.water-goal', $goal), [
                'amount' => 500.00,
                'source' => 'water_bank'
            ])
            ->assertRedirect()
            ->assertSessionHas('error'); // Should redirect with error, not crash

        // Verify no changes were made
        $goal->refresh();
        $this->assertEquals(0.00, $goal->current_amount);

        $response = $this->actingAs($user)->get(route('water-bank.status'));
        $this->assertEquals(50.00, $response->json('water_bank.balance'));
    }
}
