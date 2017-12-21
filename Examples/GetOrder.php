<?php

require_once "../vendor/autoload.php";

use MocLibs\Payment\Moip\Moip;

$moip = new Moip(function ($moip) {
    $moip->setKey('125UIS9GBLQMNA0GGCEGUTFKC9QOVCCJNXTT6DSY'); //You key on moip
    $moip->setToken('85AXXHO78OK1UTFWNJ805QOAQXH8OX5S'); //You token on moip
    $moip->setSandBox(true);
});

try {
    $orderById = $moip->getOrder('ORD-D392N0DTPLAS');

    echo '<pre>';
    print_r($orderById);
    echo '</pre>';
} catch (Exception $e) {
    var_dump($e);
}
?>