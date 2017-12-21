<?php
/**
 * Created by PhpStorm.
 * User: Maike
 * Date: 19/12/2017
 * Time: 15:04
 */

namespace MocLibs\Payment\Moip;

use Moip\Auth\BasicAuth;

class Moip
{
    private $token;
    private $key;
    private $products;
    private $cardCredit;
    private $sandbox;

    private $moip;

    private $paymentType;

    const PaymentTypeBoleto = 'Boleto';
    const PaymentTypeCartao = 'Cartao';

    public function setSandBox(bool $sandbox)
    {
        $this->sandbox = $sandbox;

        $this->initMoip();
    }

    /**
     * Moip constructor.
     */
    public function __construct()
    {
        $this->initEmptyObjects();

        return $this;
    }

    /**
     * @return $this
     */
    private function initEmptyObjects()
    {
        $this->cardCredit = new CardCredit();
        $this->initMoip();

        return $this;
    }

    private function initMoip()
    {
        $endpoint = $this->sandbox ? \Moip\Moip::ENDPOINT_SANDBOX : \Moip\Moip::ENDPOINT_PRODUCTION;
        $this->moip = new \Moip\Moip(new BasicAuth($this->token, $this->key), $endpoint);
    }

    /**
     * @return mixed
     */
    public function getPaymentType()
    {
        return $this->paymentType;
    }

    /**
     * @param mixed $paymentType
     * @return $this
     */
    public function setPaymentType($paymentType)
    {
        $this->paymentType = $paymentType;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param $token
     * @return $this
     */
    public function setToken(String $token)
    {
        $this->token = $token;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getKey(): String
    {
        return $this->key;
    }

    /**
     * @param String $key
     * @return $this
     */
    public function setKey(String $key)
    {
        $this->key = $key;
        return $this;
    }

    /**
     * @param Request $r
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|null
     */
    public function newPayment(Buyer $buyer)
    {
        $billing = $buyer->getAddressBilling();
        $shipping = $buyer->getAddressShipping();

        $customer = $this->moip->customers()->setOwnId(uniqid())
            ->setFullname($buyer->nome)
            ->setEmail($buyer->email)
            ->setBirthDate($buyer->getDtNascimentoAmericano())
            ->setTaxDocument($buyer->cpf)
            ->setPhone("00", $buyer->telefone)
            ->addAddress('BILLING',
                $billing->endereco, $billing->numero,
                $billing->bairro, $billing->municipio, $billing->estado,
                $billing->cep, 8)
            ->addAddress('SHIPPING',
                $shipping->endereco, $shipping->numero,
                $shipping->bairro, $shipping->municipio, $shipping->estado,
                $shipping->cep, 8)
            ->create();

        return $this->newOrder($customer);
    }

    /**
     * @param Product $product
     * @return $this
     */
    public function setProduct(Product $product)
    {
        $this->products[] = $product;
        return $this;
    }

    /**
     * @param $customer
     * @return mixed|null
     */
    private function newOrder($customer)
    {
        $order = $this->moip->orders()->setOwnId(uniqid());

        $total = 0;

        array_walk($this->products, function ($p, $key) use (&$order, &$total) {
            $order->addItem($p->nome, (int)$p->quantidade, $p->detalhes, intval(round($p->valor*100)));

            $total = $p->quantidade * $p->valor;
        });
        $order = $order
            ->setCustomer($customer)
            ->create();

        if ($this->paymentType == self::PaymentTypeBoleto) {
            throw new MocLibException('Método de geração de boleto ainda não criado');
            //            return $this->gera_boleto($order);
        }

        if ($this->paymentType == self::PaymentTypeCartao) {
            return $this->sendPaymentOnCreditCard($order, $customer);
        }

        return null;
    }

    /**
     * @param CardCredit $creditCard
     * @return $this
     */
    public function setCreditCard(CardCredit $creditCard)
    {
        $this->cardCredit = $creditCard;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCreditCard()
    {
        return $this->cardCredit;
    }

    /**
     * @param $order
     * @param $customer
     * @return mixed
     */
    private function sendPaymentOnCreditCard($order, $customer)
    {
        $payment = $order->payments()
            ->setCreditCard(
                (string) $this->cardCredit->mes,
                (string) $this->cardCredit->ano,
                (string) $this->cardCredit->numero,
                (string) $this->cardCredit->cvc,
                $customer,
                true
            )
            ->setInstallmentCount($this->cardCredit->parcelas)
            ->execute();

        return $payment;
    }

    /**
     * @return \Moip\Resource\Payment
     */
    public function checkPayments()
    {
        $payment = $this->moip->payments();

        return $payment;
    }
}