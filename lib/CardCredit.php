<?php

/**
 * Created by PhpStorm.
 * User: Maike
 * Date: 20/12/2017
 * Time: 11:00
 */

namespace MocLibs\Payment\Moip;

class CardCredit
{
    public $numero;
    public $nome;
    public $cvc;
    public $mes;
    public $ano;
    public $parcelas;
    public $descricao;

    public function __construct(Array $card = [])
    {
        $this->descricao = '';

        $self = $this;

        array_walk($card, function ($value, $key) use (&$self) {
            $self->{$key} = $value;
        });
    }
}