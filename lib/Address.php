<?php

/**
 * Created by PhpStorm.
 * User: Maike
 * Date: 20/12/2017
 * Time: 11:00
 */
namespace MocLibs\Payment\Moip;

class Address {
    public $endereco;
    public $numero;
    public $bairro;
    public $municipio;
    public $estado;
    public $cep;

    public function __construct(Array $address= [])
    {
        $self = $this;

        array_walk($address, function ($value, $key) use (&$self){
            $self->{$key} = $value;
        });
    }
}