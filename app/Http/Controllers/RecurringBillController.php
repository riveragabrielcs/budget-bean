<?php

namespace App\Http\Controllers;

use App\Exceptions\BillNotFoundException;
use App\Http\Requests\Bill\StoreBillRequest;
use App\Http\Requests\Bill\UpdateBillRequest;
use App\Http\Resources\BillResource;
use App\Services\BillService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RecurringBillController extends Controller
{
    public function __construct(
        private BillService $billService
    ) {}

    /**
     * Display the user's recurring bills.
     */
    public function index(Request $request): Response|JsonResponse
    {
        $user = auth()->user();
        $bills = $this->billService->getBillsForUser($user);
        $stats = $this->billService->calculateBillStats($user);

        // Format bills for frontend
        $formattedBills = $bills->map(function ($bill) {
            return [
                'id' => $bill->id,
                'name' => $bill->name,
                'amount' => $bill->amount,
                'bill_date' => $bill->date ? (int) str_replace(['st', 'nd', 'rd', 'th'], '', $bill->date) : null,
                'formatted_bill_date' => $bill->date,
                'next_due_date' => $this->billService->calculateNextDueDate($bill->date),
                'description' => $bill->description,
                'created_at' => $bill->created_at,
            ];
        });

        if ($request->expectsJson()) {
            return response()->json([
                'data' => [
                    'bills' => BillResource::collection($bills),
                    'stats' => $stats
                ]
            ]);
        }

        return Inertia::render('Bills/MyRecurringBills', [
            'recurringBills' => $formattedBills,
            'stats' => $stats,
        ]);
    }

    /**
     * Store a newly created recurring bill.
     */
    public function store(StoreBillRequest $request): RedirectResponse|JsonResponse
    {
        try {
            $user = auth()->user();
            $bill = $this->billService->createBill($user, $request);

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Your recurring bill has been added! 💳',
                    'data' => new BillResource($bill)
                ], 201);
            }

            return redirect()->route('bills.index')
                ->with('success', 'Your recurring bill has been added! 💳');

        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Failed to create bill.',
                    'error' => $e->getMessage()
                ], 500);
            }

            return back()->withErrors(['error' => 'Failed to create bill: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified recurring bill.
     */
    public function show(Request $request, int $billId): JsonResponse|RedirectResponse
    {
        try {
            $user = auth()->user();
            $bill = $this->billService->findBillForUser($user, $billId);

            if (!$bill) {
                throw new BillNotFoundException("Bill with ID {$billId} not found");
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'data' => new BillResource($bill)
                ]);
            }

            // For non-JSON requests, redirect to index
            return redirect()->route('bills.index');

        } catch (BillNotFoundException $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $e->getMessage()
                ], 404);
            }

            return redirect()->route('bills.index')
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Update the specified recurring bill.
     */
    public function update(UpdateBillRequest $request, int $billId): RedirectResponse|JsonResponse
    {
        try {
            $user = auth()->user();
            $bill = $this->billService->updateBill($user, $billId, $request);

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Your recurring bill has been updated! ✏️',
                    'data' => new BillResource($bill)
                ]);
            }

            return redirect()->route('bills.index')
                ->with('success', 'Your recurring bill has been updated! ✏️');

        } catch (BillNotFoundException $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $e->getMessage()
                ], 404);
            }

            return redirect()->route('bills.index')
                ->withErrors(['error' => $e->getMessage()]);

        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Failed to update bill.',
                    'error' => $e->getMessage()
                ], 500);
            }

            return back()->withErrors(['error' => 'Failed to update bill: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified recurring bill.
     */
    public function destroy(Request $request, int $billId): RedirectResponse|JsonResponse
    {
        try {
            $user = auth()->user();
            $result = $this->billService->deleteBill($user, $billId);

            $message = "Your {$result['name']} recurring bill has been deleted.";

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $message,
                    'data' => $result
                ]);
            }

            return redirect()->route('bills.index')
                ->with('success', $message);

        } catch (BillNotFoundException $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $e->getMessage()
                ], 404);
            }

            return redirect()->route('bills.index')
                ->withErrors(['error' => $e->getMessage()]);

        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Failed to delete bill.',
                    'error' => $e->getMessage()
                ], 500);
            }

            return back()->withErrors(['error' => 'Failed to delete bill: ' . $e->getMessage()]);
        }
    }
}
