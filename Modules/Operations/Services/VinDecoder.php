<?php

namespace Modules\Operations\Services;

/**
 * Offline VIN checks: length, illegal letters, check digit, model year and make
 * from the manufacturer code. Model is decoded in the browser via NHTSA when online.
 */
class VinDecoder
{
    private const TRANSLIT = [
        'A' => 1, 'B' => 2, 'C' => 3, 'D' => 4, 'E' => 5, 'F' => 6, 'G' => 7, 'H' => 8,
        'J' => 1, 'K' => 2, 'L' => 3, 'M' => 4, 'N' => 5, 'P' => 7, 'R' => 9,
        'S' => 2, 'T' => 3, 'U' => 4, 'V' => 5, 'W' => 6, 'X' => 7, 'Y' => 8, 'Z' => 9,
    ];

    private const WEIGHTS = [8, 7, 6, 5, 4, 3, 2, 10, 0, 9, 8, 7, 6, 5, 4, 3, 2];

    private const YEAR_CODES = 'ABCDEFGHJKLMNPRSTVWXY123456789';

    /** World manufacturer identifiers (first 3, else first 2 characters). */
    private const WMI = [
        '2T1' => 'TOYOTA', '2T3' => 'TOYOTA', '4T1' => 'TOYOTA', '4T3' => 'TOYOTA', '4T4' => 'TOYOTA', '5TD' => 'TOYOTA',
        '5TF' => 'TOYOTA', '5TE' => 'TOYOTA', '5YF' => 'TOYOTA', '3TM' => 'TOYOTA', 'JTD' => 'TOYOTA', 'JTE' => 'TOYOTA',
        'JTM' => 'TOYOTA', 'JTN' => 'TOYOTA', 'JTK' => 'TOYOTA', 'VNK' => 'TOYOTA', '5TB' => 'TOYOTA', '4TB' => 'TOYOTA',
        '2T2' => 'LEXUS', 'JTH' => 'LEXUS', 'JTJ' => 'LEXUS', '58A' => 'LEXUS',
        '1HG' => 'HONDA', '2HG' => 'HONDA', '2HK' => 'HONDA', '5FN' => 'HONDA', '5J6' => 'HONDA', '7FA' => 'HONDA', 'JHM' => 'HONDA',
        '2HN' => 'ACURA', '5J8' => 'ACURA', '19U' => 'ACURA', 'JH4' => 'ACURA',
        'KMH' => 'HYUNDAI', '5NP' => 'HYUNDAI', '5NM' => 'HYUNDAI', '5XY' => 'HYUNDAI', 'KM8' => 'HYUNDAI',
        'KNA' => 'KIA', 'KND' => 'KIA', '3KP' => 'KIA', '5XX' => 'KIA',
        '1N4' => 'NISSAN', '3N1' => 'NISSAN', '5N1' => 'NISSAN', 'JN8' => 'NISSAN', 'JN1' => 'NISSAN', '1N6' => 'NISSAN',
        'JNK' => 'INFINITI', 'JNR' => 'INFINITI', '5N3' => 'INFINITI',
        '1FA' => 'FORD', '1FM' => 'FORD', '1FT' => 'FORD', '2FM' => 'FORD', '3FA' => 'FORD', '1ZV' => 'FORD',
        '2LM' => 'LINCOLN', '5LM' => 'LINCOLN', '1LN' => 'LINCOLN',
        '1G1' => 'CHEVROLET', '2GN' => 'CHEVROLET', '3GN' => 'CHEVROLET', '1GN' => 'CHEVROLET', '1GC' => 'CHEVROLET',
        '1GK' => 'GMC', '2GK' => 'GMC', '3GK' => 'GMC', '1GT' => 'GMC',
        '1GY' => 'CADILLAC', '5GA' => 'BUICK',
        '2C3' => 'DODGE', '2C4' => 'DODGE', '1C3' => 'CHRYSLER', '2A4' => 'CHRYSLER',
        '1C4' => 'JEEP', '1J4' => 'JEEP', '1J8' => 'JEEP', '1C6' => 'RAM', '3C6' => 'RAM',
        'WDD' => 'MERCEDES-BENZ', 'WDC' => 'MERCEDES-BENZ', 'WDB' => 'MERCEDES-BENZ', '4JG' => 'MERCEDES-BENZ', '55S' => 'MERCEDES-BENZ', 'W1K' => 'MERCEDES-BENZ', 'W1N' => 'MERCEDES-BENZ',
        'WBA' => 'BMW', 'WBS' => 'BMW', '5UX' => 'BMW', '5YM' => 'BMW', 'WBX' => 'BMW',
        'WAU' => 'AUDI', 'WA1' => 'AUDI',
        'WVW' => 'VOLKSWAGEN', 'WVG' => 'VOLKSWAGEN', '3VW' => 'VOLKSWAGEN', '1VW' => 'VOLKSWAGEN',
        'WP0' => 'PORSCHE', 'WP1' => 'PORSCHE',
        'SAL' => 'LAND ROVER',
        'JM1' => 'MAZDA', 'JM3' => 'MAZDA', '3MZ' => 'MAZDA',
        'JF1' => 'SUBARU', 'JF2' => 'SUBARU', '4S3' => 'SUBARU', '4S4' => 'SUBARU',
        'JA4' => 'MITSUBISHI', 'JA3' => 'MITSUBISHI',
        'WMW' => 'MINI', '5Y2' => 'PONTIAC', 'YV1' => 'VOLVO', 'YV4' => 'VOLVO', '5YJ' => 'TESLA', '7SA' => 'TESLA',
    ];

    public static function clean(?string $vin): string
    {
        return strtoupper(preg_replace('/[^A-Za-z0-9]/', '', (string) $vin));
    }

    /**
     * @return array{vin:string, valid:bool, errors:array, warnings:array, suggestion:?string, year:?int, make:?string}
     */
    public static function decode(?string $raw): array
    {
        $vin = self::clean($raw);
        $errors = [];
        $warnings = [];
        $suggestion = null;

        if ($vin === '') {
            return ['vin' => '', 'valid' => false, 'errors' => ['VIN is empty.'], 'warnings' => [], 'suggestion' => null, 'year' => null, 'make' => null];
        }

        if (strlen($vin) !== 17) {
            $errors[] = 'A VIN has 17 characters; this one has ' . strlen($vin) . '.';
        }

        if (preg_match('/[IOQ]/', $vin)) {
            $errors[] = 'VINs never contain the letters I, O or Q.';
            $suggestion = strtr($vin, ['I' => '1', 'O' => '0', 'Q' => '0']);
        }

        $year = null;
        $make = null;
        if (strlen($vin) === 17 && empty($errors)) {
            if (!self::checkDigitMatches($vin)) {
                $warnings[] = 'Check digit (9th character) does not match. Re-check the VIN; non-North-American vehicles may not use it.';
            }
            $year = self::modelYear($vin);
        }

        if (strlen($vin) >= 3) {
            $make = self::WMI[substr($vin, 0, 3)] ?? null;
        }

        return [
            'vin' => $vin,
            'valid' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings,
            'suggestion' => $suggestion,
            'year' => $year,
            'make' => $make,
        ];
    }

    public static function checkDigitMatches(string $vin): bool
    {
        $sum = 0;
        foreach (str_split($vin) as $i => $char) {
            $value = ctype_digit($char) ? (int) $char : (self::TRANSLIT[$char] ?? null);
            if ($value === null) {
                return false;
            }
            $sum += $value * self::WEIGHTS[$i];
        }
        $check = $sum % 11;

        return $vin[8] === ($check === 10 ? 'X' : (string) $check);
    }

    /**
     * Position 10 encodes the year on a 30-year cycle. For North American VINs a
     * letter in position 7 means the 2010+ cycle, a digit means 1980–2009.
     */
    public static function modelYear(string $vin): ?int
    {
        $index = strpos(self::YEAR_CODES, $vin[9]);
        if ($index === false) {
            return null;
        }

        $base = 1980 + $index;
        $candidates = [$base, $base + 30];
        $maxYear = (int) date('Y') + 1;

        if (ctype_alpha($vin[6]) && $base + 30 <= $maxYear) {
            return $base + 30;
        }
        if (ctype_digit($vin[6])) {
            return $base;
        }

        return collect($candidates)->filter(fn ($y) => $y <= $maxYear)->max();
    }
}
