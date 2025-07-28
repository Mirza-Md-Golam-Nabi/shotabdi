<?php

if (!function_exists('numberFormat')) {
    function numberFormat(array $array, int $index): ?string
    {
        if (isset($array[$index]['amount'])) {
            return number_format($array[$index]['amount']);
        }

        return null;
    }
}

if (!function_exists('enToBn')) {
    function enToBn($number)
    {
        $search  = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        $replace = ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'];
        return str_replace($search, $replace, $number);
    }
}
