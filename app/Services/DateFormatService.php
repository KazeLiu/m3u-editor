<?php

namespace App\Services;

use App\Settings\GeneralSettings;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use DateTimeInterface;

class DateFormatService
{
    private string $format;

    public function __construct(GeneralSettings $settings)
    {
        $this->format = $settings->date_format ?? 'Y-m-d H:i:s';
    }

    /**
     * Return the configured date format string.
     */
    public function getFormat(): string
    {
        return $this->format;
    }

    /**
     * Format a date value using the configured format.
     * Accepts a Carbon instance, DateTimeInterface, or a date string.
     * Returns $fallback when the value is null/empty.
     */
    public function format(CarbonInterface|DateTimeInterface|string|null $date, string $fallback = 'Never'): string
    {
        if (! $date) {
            return $fallback;
        }

        return Carbon::parse($date)->format($this->format);
    }
}
