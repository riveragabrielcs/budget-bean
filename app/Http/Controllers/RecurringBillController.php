<?php

namespace App\Http\Controllers;

use App\Models\RecurringBill;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class RecurringBillController extends Controller
{
    /**
     * Display the user's recurring bills.
     */
    public function index(): Response
    {
        $user = auth()->user();

        $recurringBills = $user->recurringBills()
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($bill) {
                return [
                    'id' => $bill->id,
                    'name' => $bill->name,
                    'amount' => $bill->amount,
                    'bill_date' => $bill->bill_date,
                    'formatted_bill_date' => $bill->formatted_bill_date,
                    'next_due_date' => $bill->next_due_date,
                    'description' => $bill->description,
                    'created_at' => $bill->created_at->format('M d, Y'),
                ];
            });

        $stats = [
            'total_bills' => $recurringBills->count(),
            'total_monthly_amount' => $recurringBills->sum('amount'),
            'average_bill_amount' => $recurringBills->count() > 0
                ? $recurringBills->avg('amount')
                : 0,
        ];

        return Inertia::render('Bills/MyRecurringBills', [
            'recurringBills' => $recurringBills,
            'stats' => $stats,
        ]);
    }

    /**
     * Store a newly created recurring bill.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01|max:9999999.99',
            'bill_date' => 'nullable|integer|min:1|max:31',
            'description' => 'nullable|string|max:500',
        ]);

        auth()->user()->recurringBills()->create($validated);

        return redirect()->route('bills.index')->with('success', 'Your recurring bill has been added! 💳');
    }

    /**
     * Update the specified recurring bill.
     */
    public function update(Request $request, RecurringBill $recurringBill): RedirectResponse
    {
        // Ensure the bill belongs to the authenticated user
        if ($recurringBill->user_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'amount' => 'sometimes|required|numeric|min:0.01|max:9999999.99',
            'bill_date' => 'sometimes|nullable|integer|min:1|max:31',
            'description' => 'sometimes|nullable|string|max:500',
        ]);

        $recurringBill->update($validated);

        return redirect()->route('bills.index')->with('success', 'Your recurring bill has been updated! ✏️');
    }

    /**
     * Remove the specified recurring bill.
     */
    public function destroy(RecurringBill $recurringBill): RedirectResponse
    {
        // Ensure the bill belongs to the authenticated user
        if ($recurringBill->user_id !== auth()->id()) {
            abort(403);
        }

        $billName = $recurringBill->name;
        $recurringBill->delete();

        return redirect()->route('bills.index')->with('success', "Your {$billName} recurring bill has been deleted.");
    }
}
