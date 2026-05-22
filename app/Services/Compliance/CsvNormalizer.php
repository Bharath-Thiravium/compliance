<?php

namespace App\Services\Compliance;

class CsvNormalizer
{
    /**
     * Normalize gender to DB ENUM('M','F','O').
     * Accepts: Male, Female, M, F, male, MALE, female, Other, etc.
     */
    public static function normalizeGender(?string $value): ?string
    {
        if ($value === null || trim($value) === '') return null;

        $v = strtolower(trim($value));

        return match (true) {
            in_array($v, ['m', 'male', 'man', 'boy'])           => 'M',
            in_array($v, ['f', 'female', 'woman', 'girl'])      => 'F',
            in_array($v, ['o', 'other', 'others', 'na', 'n/a']) => 'O',
            default                                              => null,
        };
    }

    /**
     * Parse a date string to Y-m-d or null.
     * Handles: d/m/Y, m/d/Y, d-m-Y, Y-m-d, d.m.Y, etc.
     */
    public static function normalizeDate(?string $value): ?string
    {
        if ($value === null || trim($value) === '') return null;

        $value = trim($value);

        // Try common formats explicitly before Carbon to avoid m/d vs d/m ambiguity
        $formats = ['d/m/Y', 'd-m-Y', 'd.m.Y', 'Y-m-d', 'm/d/Y', 'd/m/y', 'Y/m/d'];
        foreach ($formats as $fmt) {
            $dt = \DateTime::createFromFormat($fmt, $value);
            if ($dt && $dt->format($fmt) === $value) {
                return $dt->format('Y-m-d');
            }
        }

        // Fallback to strtotime
        $ts = strtotime($value);
        return $ts !== false ? date('Y-m-d', $ts) : null;
    }

    /**
     * Normalize mobile: strip non-digits, keep last 10 digits for Indian numbers.
     */
    public static function normalizeMobile(?string $value): ?string
    {
        if ($value === null || trim($value) === '') return null;

        $digits = preg_replace('/\D/', '', trim($value));

        // Strip country code prefix (91 for India)
        if (strlen($digits) === 12 && str_starts_with($digits, '91')) {
            $digits = substr($digits, 2);
        }

        return strlen($digits) >= 10 ? substr($digits, -10) : ($digits ?: null);
    }

    /**
     * Normalize UAN: must be 12 digits.
     */
    public static function normalizeUAN(?string $value): ?string
    {
        if ($value === null || trim($value) === '') return null;

        $digits = preg_replace('/\D/', '', trim($value));
        return strlen($digits) === 12 ? $digits : (trim($value) ?: null);
    }

    /**
     * Normalize PF account number: trim and uppercase.
     */
    public static function normalizePF(?string $value): ?string
    {
        if ($value === null || trim($value) === '') return null;
        return strtoupper(trim($value));
    }

    /**
     * Normalize ESI/ESIC IP number: digits only.
     */
    public static function normalizeESI(?string $value): ?string
    {
        if ($value === null || trim($value) === '') return null;
        $clean = preg_replace('/\D/', '', trim($value));
        return $clean ?: trim($value) ?: null;
    }

    /**
     * Normalize a numeric value to float, returning 0.0 for empty/invalid.
     */
    public static function normalizeFloat(?string $value, float $default = 0.0): float
    {
        if ($value === null || trim($value) === '') return $default;
        $clean = preg_replace('/[^\d.\-]/', '', trim($value));
        return is_numeric($clean) ? (float) $clean : $default;
    }

    /**
     * Normalize an integer value.
     */
    public static function normalizeInt(?string $value, int $default = 0): int
    {
        if ($value === null || trim($value) === '') return $default;
        $clean = preg_replace('/[^\d\-]/', '', trim($value));
        return is_numeric($clean) ? (int) $clean : $default;
    }
}
