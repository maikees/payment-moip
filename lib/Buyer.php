<?php

namespace MocLibs\Payment\Moip;

use Carbon\Carbon;

class Buyer {
    public $telefone;
    public $nome;
    public $email;
    public $dtNascimento;
    public $cpf;
    public $enderecos;

    public function __construct(Array $card = [])
    {
        $this->telefone = "00000000";

        $self = $this;

        array_walk($card, function ($value, $key) use (&$self) {
            $self->{$key} = $value;
        });

        return $this;
    }

    public function setAuthLaravel($user) {
        $this->nome = $user->name;
        $this->email = $user->email;
        $this->dtNascimento = $user->data_nascimento;
        $this->cpf = $user->cpf;
        $this->telefone = $user->telefone ?: $this->telefone;

        $address = [
            'endereco' => $user->endereco,
            'bairro' => $user->bairro,
            'numero' => $user->numero,
            'municipio' => $user->municipio,
            'estado' => $user->estado,
            'cep' => $user->cep
        ];

        $this->enderecos['BILLING'] = new Address($address);
        $this->enderecos['SHIPPING'] = new Address($address);
    }

    public function getAddressBilling() {
        return $this->enderecos['BILLING'];
    }

    public function setAddressBilling(Address $address) {
        $this->enderecos['BILLING'] = $address;

        return $this;
    }

    public function setAddressShipping($address) {
        $this->enderecos['SHIPPING'] = $address;

        return $this;
    }

    public function getAddressShipping() {
        return $this->enderecos['SHIPPING'];
    }

    public function getDtNascimentoAmericano() {
        return Carbon::createFromFormat('d/m/Y', $this->dtNascimento)->format('Y-m-d');
    }
}
