<?php

namespace App\Services\Compliance;

class CsvNormalizer
{
    /** Normalize gender → M/F/O */
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

    /** Normalize employment_type → Permanent/Temporary/Contract */
    public static function normalizeEmploymentType(?string $value): ?string
    {
        if ($value === null || trim($value) === '') return null;
        $v = strtolower(trim($value));
        return match (true) {
            in_array($v, ['permanent', 'perm', 'regular', 'p'])  => 'Permanent',
            in_array($v, ['temporary', 'temp', 'casual', 't'])   => 'Temporary',
            in_array($v, ['contract', 'contractor', 'c', 'contractual']) => 'Contract',
            default => ucfirst(strtolower(trim($value))),
        };
    }

    /** Normalize attendance status → present/absent/leave/holiday/half_day */
    public static function normalizeAttendanceStatus(?string $value): string
    {
        if ($value === null || trim($value) === '') return 'present';
        $v = strtolower(trim($value));
        return match (true) {
            in_array($v, ['p', 'present', 'attended', '1'])                    => 'present',
            in_array($v, ['a', 'absent', 'lwp', 'lop', '0'])                   => 'absent',
            in_array($v, ['l', 'leave', 'on_leave', 'on leave', 'cl', 'el', 'pl', 'sl']) => 'leave',
            in_array($v, ['h', 'holiday', 'nh', 'national_holiday', 'ph'])     => 'holiday',
            in_array($v, ['hd', 'half_day', 'half day', 'halfday'])            => 'half_day',
            default => 'present',
        };
    }

    /** Normalize employee status → active/inactive */
    public static function normalizeStatus(?string $value): string
    {
        if ($value === null || trim($value) === '') return 'active';
        $v = strtolower(trim($value));
        return in_array($v, ['inactive', 'terminated', 'resigned', 'left', 'exit', '0', 'false'])
            ? 'inactive'
            : 'active';
    }

    /** Parse date string → Y-m-d or null */
    public static function normalizeDate(?string $value): ?string
    {
        if ($value === null || trim($value) === '') return null;
        $value = trim($value);
        $formats = ['d/m/Y', 'd-m-Y', 'd.m.Y', 'Y-m-d', 'm/d/Y', 'd/m/y', 'Y/m/d'];
        foreach ($formats as $fmt) {
            $dt = \DateTime::createFromFormat($fmt, $value);
            if ($dt && $dt->format($fmt) === $value) {
                return $dt->format('Y-m-d');
            }
        }
        $ts = strtotime($value);
        return $ts !== false ? date('Y-m-d', $ts) : null;
    }

    /** Normalize mobile: strip non-digits, keep last 10 digits */
    public static function normalizeMobile(?string $value): ?string
    {
        if ($value === null || trim($value) === '') return null;
        $digits = preg_replace('/\D/', '', trim($value));
        if (strlen($digits) === 12 && str_starts_with($digits, '91')) {
            $digits = substr($digits, 2);
        }
        return strlen($digits) >= 10 ? substr($digits, -10) : ($digits ?: null);
    }

    /** Normalize UAN: must be 12 digits */
    public static function normalizeUAN(?string $value): ?string
    {
        if ($value === null || trim($value) === '') return null;
        $digits = preg_replace('/\D/', '', trim($value));
        return strlen($digits) === 12 ? $digits : (trim($value) ?: null);
    }

    /** Normalize PF account number: trim and uppercase */
    public static function normalizePF(?string $value): ?string
    {
        if ($value === null || trim($value) === '') return null;
        return strtoupper(trim($value));
    }

    /** Normalize ESI/ESIC IP number: digits only */
    public static function normalizeESI(?string $value): ?string
    {
        if ($value === null || trim($value) === '') return null;
        $clean = preg_replace('/\D/', '', trim($value));
        return $clean ?: trim($value) ?: null;
    }

    /** Normalize PAN: uppercase, validate format AAAAA9999A */
    public static function normalizePAN(?string $value): ?string
    {
        if ($value === null || trim($value) === '') return null;
        $pan = strtoupper(preg_replace('/\s+/', '', trim($value)));
        return preg_match('/^[A-Z]{5}[0-9]{4}[A-Z]$/', $pan) ? $pan : $pan;
    }

    /** Normalize Aadhaar: digits only, 12 digits */
    public static function normalizeAadhaar(?string $value): ?string
    {
        if ($value === null || trim($value) === '') return null;
        $digits = preg_replace('/\D/', '', trim($value));
        return strlen($digits) === 12 ? $digits : (trim($value) ?: null);
    }

    /** Normalize IFSC: uppercase, validate format AAAA0XXXXXX */
    public static function normalizeIFSC(?string $value): ?string
    {
        if ($value === null || trim($value) === '') return null;
        $ifsc = strtoupper(preg_replace('/\s+/', '', trim($value)));
        return $ifsc ?: null;
    }

    /** Normalize float */
    public static function normalizeFloat(?string $value, float $default = 0.0): float
    {
        if ($value === null || trim($value) === '') return $default;
        $clean = preg_replace('/[^\d.\-]/', '', trim($value));
        return is_numeric($clean) ? (float) $clean : $default;
    }

    /** Normalize integer */
    public static function normalizeInt(?string $value, int $default = 0): int
    {
        if ($value === null || trim($value) === '') return $default;
        $clean = preg_replace('/[^\d\-]/', '', trim($value));
        return is_numeric($clean) ? (int) $clean : $default;
    }

    /** Normalize boolean from CSV (yes/no/1/0/true/false) */
    public static function normalizeBool(?string $value, bool $default = false): bool
    {
        if ($value === null || trim($value) === '') return $default;
        $v = strtolower(trim($value));
        return in_array($v, ['1', 'yes', 'y', 'true', 't']) ? true : false;
    }

    /** Normalize time to HH:MM:SS or null */
    public static function normalizeTime(?string $value): ?string
    {
        if ($value === null || trim($value) === '') return null;
        $value = trim($value);
        // Already HH:MM or HH:MM:SS
        if (preg_match('/^\d{1,2}:\d{2}(:\d{2})?$/', $value)) {
            return strlen($value) === 5 ? $value . ':00' : $value;
        }
        $ts = strtotime($value);
        return $ts !== false ? date('H:i:s', $ts) : null;
    }

    /** Validate IFSC format */
    public static function validateIFSC(?string $value): bool
    {
        if (empty($value)) return false;
        return (bool) preg_match('/^[A-Z]{4}0[A-Z0-9]{6}$/', strtoupper(trim($value)));
    }

    /** Validate PAN format */
    public static function validatePAN(?string $value): bool
    {
        if (empty($value)) return false;
        return (bool) preg_match('/^[A-Z]{5}[0-9]{4}[A-Z]$/', strtoupper(trim($value)));
    }

    /** Validate Aadhaar (12 digits) */
    public static function validateAadhaar(?string $value): bool
    {
        if (empty($value)) return false;
        $digits = preg_replace('/\D/', '', trim($value));
        return strlen($digits) === 12;
    }
}
