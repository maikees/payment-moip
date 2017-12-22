<?php

require_once "../vendor/autoload.php";

use MocLibs\Payment\Moip\Moip;

$moip = new Moip([
    'key' => 'AO7UIS9GBLQMNA0GGCEGUTFKC9QOVCCJNXTT6DSY',
    'token' => '7O4XXHO78OK1UTFWNJ805QOAQXH8OX5S',
    'sandbox' => true
]);

try {
    $orderById = $moip->refund('ORD-JYP0NIFSDDXS');

    echo '<pre>';
    print_r($orderById);
    echo '</pre>';
} catch (Exception $e) {
    var_dump($e);
}
?>