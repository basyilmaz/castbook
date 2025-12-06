<?php

namespace App\Support;

use Illuminate\Support\Carbon;

class Format
{
    public static function money(float|int|null $value, string $currencySymbol = "\u{20BA}"): string
    {
        $amount = number_format((float) $value, 2, ',', '.');

        return trim(sprintf('%s %s', $currencySymbol, $amount));
    }

    public static function monthLabel(string $period): string
    {
        try {
            $label = Carbon::createFromFormat('Y-m', $period)
                ->locale('tr')
                ->isoFormat('MMMM YYYY');

            return mb_convert_case($label, MB_CASE_TITLE, 'UTF-8');
        } catch (\Throwable) {
            return $period;
        }
    }
}
