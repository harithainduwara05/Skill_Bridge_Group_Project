<?php

function sriLankaDistricts()
{
    return [
        'Ampara',
        'Anuradhapura',
        'Badulla',
        'Batticaloa',
        'Colombo',
        'Galle',
        'Gampaha',
        'Hambantota',
        'Jaffna',
        'Kalutara',
        'Kandy',
        'Kegalle',
        'Kilinochchi',
        'Kurunegala',
        'Mannar',
        'Matale',
        'Matara',
        'Monaragala',
        'Mullaitivu',
        'Nuwara Eliya',
        'Polonnaruwa',
        'Puttalam',
        'Ratnapura',
        'Trincomalee',
        'Vavuniya',
    ];
}

function resolveInternshipLocation($workMode, $location, $other)
{
    $workMode = trim((string) $workMode);
    $location = trim((string) $location);
    $other = trim((string) $other);

    if ($workMode === 'Remote') {
        return ['Remote', ''];
    }

    if ($location === '' && $workMode === '') {
        return ['', ''];
    }

    if ($location === '') {
        return ['', 'Please select a work location.'];
    }

    if ($location === 'Other') {
        $length = function_exists('mb_strlen') ? mb_strlen($other, 'UTF-8') : strlen($other);
        if ($length < 2 || $length > 100 || preg_match('/\p{L}/u', $other) !== 1 || preg_match('/^[\p{L} ,]+$/u', $other) !== 1) {
            return ['', 'Enter a city and country using 2-100 letters, spaces, and commas only.'];
        }
        return [$other, ''];
    }

    if (in_array($location, sriLankaDistricts(), true)) {
        return [$location, ''];
    }

    return ['', 'Please select a valid work location.'];
}
