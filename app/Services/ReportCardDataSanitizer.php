<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class ReportCardDataSanitizer
{
    public function sanitize(array $data): array
    {
        $sanitized = [];

        foreach ($data as $key => $value) {
            $sanitized[$key] = $this->sanitizeValue($value);
        }

        return $sanitized;
    }

    private function sanitizeValue($value)
    {
        if ($value === null) {
            Log::debug('Received null value, returning empty string');
            return '';
        }

        if (is_array($value)) {
            return $this->sanitize($value);
        }

        if (!is_string($value)) {
            Log::warning('Non-string input received', ['input' => $value, 'type' => gettype($value)]);
            return '';
        }

        // Check if the string is valid UTF-8
        if (!mb_check_encoding($value, 'UTF-8')) {
            Log::warning('Invalid UTF-8 string received', ['input' => $value]);
            $value = mb_convert_encoding($value, 'UTF-8', 'auto');
        }

        // Remove invalid characters
        return iconv('UTF-8', 'UTF-8//IGNORE', $value);
    }
}
