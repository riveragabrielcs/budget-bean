<?php

namespace App\Services;

class BillDateFormatter
{
    /**
     * Format a bill date with ordinal suffix (1st, 2nd, 3rd, etc.).
     */
    public function format(?int $billDate): ?string
    {
        if (!$billDate) {
            return null;
        }

        $suffix = match($billDate % 10) {
            1 => $billDate % 100 === 11 ? 'th' : 'st',
            2 => $billDate % 100 === 12 ? 'th' : 'nd',
            3 => $billDate % 100 === 13 ? 'th' : 'rd',
            default => 'th'
        };

        return $billDate . $suffix;
    }
}
