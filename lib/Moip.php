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

    /**
     * Moip constructor.
     * @param null $params
     */
    public function __construct($params = null)
    {
        if (is_callable($params)) {
            $params($this);
        }

        if (is_array($params)) {
            if (isset($params['key'])) {
                $this->setKey($params['key']);
            }

            if (isset($params['token'])) {
                $this->setToken($params['token']);
            }

            if(isset($params['sandbox'])){
                $this->setSandBox(true);
            }
        }

        $this->initEmptyObjects();

        return $this;
    }

    /**
     * @param bool $sandbox
     */
    public function setSandBox(bool $sandbox)
    {
        $this->sandbox = $sandbox;

        $this->initMoip();
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
            ->setPhone("00", $buyer->telefone);

        if ($billing) {
            $customer = $customer->addAddress('BILLING',
                $billing->endereco, $billing->numero,
                $billing->bairro, $billing->municipio, $billing->estado,
                $billing->cep, 8);
        }

        if ($shipping) {
            $customer = $customer->addAddress('SHIPPING',
                $shipping->endereco, $shipping->numero,
                $shipping->bairro, $shipping->municipio, $shipping->estado,
                $shipping->cep, 8);
        }

        $customer->create();

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
        $this->order = $this->moip->orders()->setOwnId(uniqid());

        $total = 0;
        $order = $this->order;

        array_walk($this->products, function ($p, $key) use (&$order, &$total) {
            $order->addItem($p->nome, (int)$p->quantidade, $p->detalhes, intval(round($p->valor * 100)));

            $total = $p->quantidade * $p->valor;
        });

        $this->order = $this->order
            ->setCustomer($customer)
            ->create();

        if ($this->paymentType == self::PaymentTypeBoleto) {
            throw new MocLibException('Método de geração de boleto ainda não criado');
        }

        if ($this->paymentType == self::PaymentTypeCartao) {
            return $this->sendPaymentOnCreditCard($this->order, $customer);
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
    private function sendPaymentOnCreditCard($customer)
    {
        $payment = $this->order->payments()
            ->setCreditCard(
                (string)$this->cardCredit->mes,
                (string)$this->cardCredit->ano,
                (string)$this->cardCredit->numero,
                (string)$this->cardCredit->cvc,
                $customer,
                true
            )
            ->setInstallmentCount($this->cardCredit->parcelas)
            ->execute();

        return $payment;
    }

    /**
     * @return mixed
     */
    private function getOrders()
    {
        $payments = $this->moip->orders();

        return $payments;
    }

    /**
     * @param $id
     * @return mixed
     */
    public function getOrder($id)
    {
        return $this->getOrders()->get($id);
    }

    public function getCurrentOrder() {
        return $this->order;
    }

    /**
     * @param $id
     * @return mixed
     */
    public function getPayment($id)
    {
        return $this->moip->payments()->get($id);
    }

    /**
     * @param $id
     * @return mixed
     */
    public function refund($id) {
        return $this->getOrder($id)->refunds()->creditCardFull();
    }
}