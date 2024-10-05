<?php

use App\Models\User;
use Carbon\Carbon;

if (!function_exists('toUserDate')) {
    function toUserDate(string|Carbon $date, ?User $user = null, string $timezone = 'UTC'): string
    {
        if ($user) {
            $timezone = $user->timezone;
        }

        if (is_string($date)) {
            return Carbon::parse($date, 'UTC')->setTimezone($timezone)->isoFormat('L');
        }

        return $date->setTimezone($timezone)->isoFormat('L');
    }
}

if (!function_exists('toUserTime')) {
    function toUserTime(string|Carbon $date, ?User $user = null, string $timezone = 'UTC'): string
    {
        if ($user) {
            $timezone = $user->timezone;
        }

        if (is_string($date)) {
            return Carbon::parse($date, 'UTC')->setTimezone($timezone)->isoFormat('LT');
        }

        return $date->setTimezone($timezone)->isoFormat('LT');
    }
}

if (!function_exists('toUserDateTime')) {
    function toUserDateTime(string|Carbon $date, ?User $user = null, string $timezone = 'UTC'): string
    {
        if ($user) {
            $timezone = $user->timezone;
        }

        if (is_string($date)) {
            return Carbon::parse($date, 'UTC')->setTimezone($timezone)->isoFormat('L LT');
        }

        return $date->setTimezone($timezone)->isoFormat('L LT');
    }
}

if (!function_exists('fromUserDate')) {
    function fromUserDate(string|Carbon $date, ?User $user = null, string $timezone = null): string
    {
        if ($user) {
            $timezone = $user->timezone;
        }

        if (is_string($date)) {
            return Carbon::parse($date, $timezone)->setTimezone('UTC')->toDateString();
        }

        return $date->setTimezone('UTC')->toDateTimeString();
    }
}

if (!function_exists('fromUserTime')) {
    function fromUserTime(string|Carbon $date, ?User $user = null, string $timezone = null): string
    {
        if ($user) {
            $timezone = $user->timezone;
        }

        if (is_string($date)) {
            return Carbon::parse($date, $timezone)->setTimezone('UTC')->toTimeString();
        }

        return $date->setTimezone('UTC')->toDateTimeString();
    }
}

if (!function_exists('fromUserDateTime')) {
    function fromUserDateTime(string|Carbon $date, ?User $user = null, string $timezone = null): string
    {
        if ($user) {
            $timezone = $user->timezone;
        }

        if (is_string($date)) {
            return Carbon::parse($date, $timezone)->setTimezone('UTC')->toDateTimeString();
        }

        return $date->setTimezone('UTC')->toDateTimeString();
    }
}
