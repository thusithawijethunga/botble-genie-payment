<?php
namespace Botble\GeniePayment\Client\Model;

use Botble\GeniePayment\Client\Options\Expires;

class Transaction
{
    /**
     * @var int
     */
    private $amount;

    /**
     * @var string
     */
    private $currency;

    /**
     * @var string
     */
    private $isPreAuthorization;

    /**
     * @var string
     */
    private $provider;

    /**
     * @var string
     */
    private $redirectUrl;

    /**
     * @var string
     */
    private $localId;

    /**
     * @var string
     */
    private $webhook;

    /**
     * @var string
     */
    private $expires;

    /**
     * @return int
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param int $amount
     */
    public function setAmount(int $amount)
    {
        $this->amount = $amount;
    }

    /**
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @param string $currency
     */
    public function setCurrency(string $currency)
    {
        $this->currency = $currency;
    }

    /**
     * @return string
     */
    public function getisPreAuthorization()
    {
        return $this->isPreAuthorization;
    }

    /**
     * @param string $isPreAuthorization
     */
    public function setIsPreAuthorization($isPreAuthorization)
    {
        $this->isPreAuthorization = $isPreAuthorization;
    }

    /**
     * @return mixed
     */
    public function getProvider()
    {
        return $this->provider;
    }

    /**
     * @param mixed $provider
     */
    public function setProvider($provider)
    {
        $this->provider = $provider;
    }

    /**
     * @return mixed
     */
    public function getRedirectUrl()
    {
        return $this->redirectUrl;
    }

    /**
     * @param mixed $redirectUrl
     */
    public function setRedirectUrl($redirectUrl)
    {
        $this->redirectUrl = $redirectUrl;
    }

    /**
     * @return mixed
     */
    public function getLocalId()
    {
        return $this->localId;
    }

    /**
     * @param mixed $localId
     */
    public function setLocalId($localId)
    {
        $this->localId = $localId;
    }

    /**
     * @return mixed
     */
    public function getWebhook()
    {
        return $this->webhook;
    }

    /**
     * @param mixed $webhook
     */
    public function setWebhook($webhook)
    {
        $this->webhook = $webhook;
    }

    /**
     * @return string
     */
    public function getExpires()
    {
        return $this->expires;
    }

    /**
     * @param string $expires
     */
    public function setExpires($expires)
    {
        $this->expires = $expires;
    }

    /**
     * @param array $json
     * @return $this
     * @throws \Exception
     */
    public function fromArray(array $json)
    {
        if (array_key_exists('amount', $json) && array_key_exists('currency', $json)) {
            $this->amount = $json['amount'];
            $this->currency = $json['currency'];
        } else {
            throw new \InvalidArgumentException('amount and currency are required to sign a transaction');
        }

        if (array_key_exists('isPreAuthorization', $json)) {
            $this->isPreAuthorization = $json['isPreAuthorization'];
        }

        if (array_key_exists('redirectUrl', $json)) {
            $this->isPreAuthorization = $json['redirectUrl'];
        }

        if (array_key_exists('provider', $json)) {
            $this->provider = $json['provider'];
        }

        if (array_key_exists('localId', $json)) {
            $this->localId = $json['localId'];
        }

        if (array_key_exists('webhook', $json)) {
            $this->webhook = $json['webhook'];
        }

        if (array_key_exists('validForHours', $json)) {
            
            if((int) $json['validForHours'] < 1 || (int) $json['validForHours'] > 2160) {
                throw new \InvalidArgumentException('The minimum validity is 1 hour
                 and the maximum validity is 90 days');
            }

            $this->expires = (new Expires(new \DateTime()))->getExpiryDateAsIsoString($json['validForHours']);
        }
        
        return $this;
    }
}