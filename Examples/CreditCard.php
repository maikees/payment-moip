<?php

use MocLibs\Payment\Moip\Address;
use MocLibs\Payment\Moip\Buyer;
use MocLibs\Payment\Moip\CardCredit;
use MocLibs\Payment\Moip\Moip;
use MocLibs\Payment\Moip\Product;

require_once "../vendor/autoload.php";


$moip = new Moip();
$moip->setKey('2658S9GBLQMNA0GGCEGUTFKC9QOVCCJNXTT6DSY'); //You key on moip
$moip->setToken('5A8XXHO78OK1UTFWNJ805QOAQXH8OX5S'); //You token on moip
$moip->setSandBox(true); //If Teste (sandbox)

$moip->setPaymentType(Moip::PaymentTypeCartao);

$moip->setProduct(new Product([
    'nome' => 'Nome do produto.',
    'quantidade' => 1,
    'valor' => 50.90,
    'detalhes' => 'Detalhes do produto.',
]));

$moip->setCreditCard(new CardCredit([
    'numero' => '4444222233334444',
    'nome' => 'Nome no cartão',
    'cvc' => '233', //Cvc no verso do cartão
    'mes' => '06', //Mês de vencimento do cartão
    'ano' => '22', //Ano de vencimento no cartão
    'parcelas' => '2', //Quantidade de parcelas da compra
]));

$address = new Address([
    'endereco' => 'Rua Dom Pedro Primeiro',
    'bairro' => 'Nova York',
    'numero' => '49',
    'municipio' => 'Serra',
    'estado' => 'Espírito Santo',
    'cep' => '29175000'
]);

$pagamento = $moip->newPayment((new Buyer([
    'nome' => 'Nome do comprador',
    'email' => 'atendimento@mocsolucoes.com.br',
    'dtNascimento' => '08/06/1991', // Padrão brasileiro
    'telefone' => '30111312', //Telefone
    'cpf' => '58974436990',
]))->setAddressBilling($address)
    ->setAddressShipping($address)
);

$id = $pagamento->getId();
$status = $pagamento->getStatus();

var_dump($id);
var_dump($status);
var_dump($pagamento);
?>