<?php

if (! function_exists('generateAccountNumber')) {
    /**
     * @return int
     */
    function generateAccountNumber(): int
    {
        return rand(1000000000, 9999999999);
    }

    /**
     * @param $amount
     * @return string
     */
    function moneyFormat($amount): string
    {
        return number_format($amount, 2);
    }
}
