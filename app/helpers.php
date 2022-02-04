<?php

if (! function_exists('generateAccountNumber')) {
    function generateAccountNumber(): int
    {
        return rand(1000000000, 9999999999);
    }
}
