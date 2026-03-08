<?php
// Application currency helper
// Returns symbol and formats amounts for views
function app_currency_symbol() {
    return '₱';
}

function format_currency($amount, $decimals = 2) {
    if ($amount === null || $amount === '') return '—';
    return app_currency_symbol() . number_format((float)$amount, (int)$decimals, '.', ',');
}

?>