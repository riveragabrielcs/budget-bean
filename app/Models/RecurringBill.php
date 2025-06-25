<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecurringBill extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'amount',
        'bill_date',
        'description',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'bill_date' => 'integer', // Day of month (1-31)
        ];
    }

    /**
     * Get the user that owns the recurring bill.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get formatted bill date for display.
     */
    public function getFormattedBillDateAttribute(): ?string
    {
        if (!$this->bill_date) {
            return null;
        }

        $suffix = match($this->bill_date % 10) {
            1 => $this->bill_date % 100 === 11 ? 'th' : 'st',
            2 => $this->bill_date % 100 === 12 ? 'th' : 'nd',
            3 => $this->bill_date % 100 === 13 ? 'th' : 'rd',
            default => 'th'
        };

        return $this->bill_date . $suffix;
    }

    /**
     * Get next due date for this bill.
     */
    public function getNextDueDateAttribute(): ?string
    {
        if (!$this->bill_date) {
            return null;
        }

        $today = now();
        $currentMonth = $today->month;
        $currentYear = $today->year;

        // If the bill date hasn't passed this month, it's due this month
        if ($this->bill_date >= $today->day) {
            $dueDate = now()->setDay($this->bill_date);
        } else {
            // Otherwise, it's due next month
            $dueDate = now()->addMonth()->setDay($this->bill_date);
        }

        return $dueDate->format('M j, Y');
    }
}
