<?php

/**
 * Created by PhpStorm.
 * User: Maike
 * Date: 20/12/2017
 * Time: 11:00
 */
namespace MocLibs\Payment\Moip;

class Product {
    public $nome;
    public $quantidade;
    public $detalhes;
    public $valor;

    public function __construct(Array $product = [])
    {
        $self = $this;

        array_walk($product, function ($value, $key) use (&$self){
            $self->{$key} = $value;
        });
    }
}