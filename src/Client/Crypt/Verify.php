<?php
namespace Botble\GeniePayment\Client\Crypt;

use Botble\GeniePayment\Client\Client;

class Verify
{
    public function validateSignature(Client $client, string $sign, int $amount, string $currency)
    {
        return ($sign == sha1('amount='.$amount. '&currency='.$currency.'&apiKey='.$client->getApiKey()));
    }
}