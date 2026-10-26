<?php

namespace Modules\Operations\Services;

/**
 * ISO 6346 container numbers: 4 letters (owner + category) + 6 digits + check digit.
 */
class ContainerNumber
{
    public static function clean(?string $number): ?string
    {
        $clean = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', (string) $number));

        return $clean === '' ? null : $clean;
    }

    public static function isWellFormed(?string $number): bool
    {
        return (bool) preg_match('/^[A-Z]{3}[UJZ]\d{7}$/', (string) self::clean($number));
    }

    public static function isValid(?string $number): bool
    {
        $number = self::clean($number);
        if (!self::isWellFormed($number)) {
            return false;
        }

        return self::checkDigit(substr($number, 0, 10)) === (int) $number[10];
    }

    public static function checkDigit(string $first10): int
    {
        // Letter values skip multiples of 11.
        $values = [];
        $value = 10;
        foreach (range('A', 'Z') as $letter) {
            if ($value % 11 === 0) {
                $value++;
            }
            $values[$letter] = $value++;
        }

        $sum = 0;
        foreach (str_split($first10) as $i => $char) {
            $n = ctype_digit($char) ? (int) $char : $values[$char];
            $sum += $n * (2 ** $i);
        }

        return ($sum % 11) % 10;
    }
}
