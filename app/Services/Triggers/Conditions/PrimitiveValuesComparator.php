<?php namespace App\Services\Triggers\Conditions;

use Str;

class PrimitiveValuesComparator {

    /**
     * Compare string haystack and needle using specified operator.
     *
     * @param mixed $haystack
     * @param mixed $needle
     * @param string $operator
     *
     * @return bool
     */
    public function compare($haystack, $needle, $operator)
    {
        switch ($operator) {
            case 'contains':
                return Str::contains($haystack, $needle);
            case 'not_contains':
                return ! Str::contains($haystack, $needle);
            case 'starts_with':
                return Str::startsWith($haystack, $needle);
            case 'ends_with':
                return Str::endsWith($haystack, $needle);
            case 'equals':
            case 'is':
                return $haystack === $needle;
            case 'not_equals':
            case 'not':
                return $haystack !== $needle;
            case 'more':
                return $haystack > $needle;
            case 'less':
                return $haystack < $needle;
            case 'matches_regex':
                return (bool) preg_match("/$needle/", $haystack);
            default:
                return false;
        }
    }
}
