<?php

namespace Tests\Feature\WaterBank;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GardenWaterTest extends TestCase
{
    use RefreshDatabase;

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_prevents_overwatering_through_garden_add_savings_route()
    {
        // ARRANGE: Create authenticated user with goal and water
        $user = User::factory()->create();

        $goal = $user->savingsGoals()->create([
            'name' => 'Emergency Fund',
            'target_amount' => 100.00,
            'current_amount' => 0.00,
            'is_completed' => false,
        ]);

        // Add water to bank through the manual add route
        $this->actingAs($user)
            ->post(route('water-bank.add-manual'), [
                'amount' => 2000.00,
                'description' => 'Initial water'
            ])
            ->assertRedirect();

        // ACT: Use the GARDEN route to add savings (this is what frontend uses)
        $this->actingAs($user)
            ->post(route('garden.add-savings', $goal), [
                'amount' => 200.00,
                'source' => 'water_bank'  // Using water bank
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        // ASSERT: Check that only needed amount was used
        $goal->refresh();
        $this->assertTrue($goal->is_completed);
        $this->assertEquals(100.00, $goal->current_amount);

        // Check water bank balance - should be 1900 (2000 - 100), NOT 1800 (2000 - 200)
        $response = $this->actingAs($user)
            ->get(route('water-bank.status'))
            ->assertJson(['success' => true]);

        $finalBalance = $response->json('water_bank.balance');

        $this->assertEquals(1900.00, $finalBalance,
            'Garden route should only use needed amount, not full requested amount'
        );
    }
}
