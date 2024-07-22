<?php
namespace Ethsam\EasyHcaptcha;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class HcaptchaVerifier
{
    private $secretKey;
    private $client;

    public function __construct(ParameterBagInterface $params, HttpClientInterface $client)
    {
        $this->secretKey = $params->get('hcaptcha_secret_key');
        $this->client = $client;
    }

    public function verify($captchaResponse)
    {
        $response = $this->client->request('POST', 'https://api.hcaptcha.com/siteverify', [
            'body' => [
                'secret' => $this->secretKey,
                'response' => $captchaResponse,
            ],
        ]);

        $responseData = $response->toArray();

        return $responseData['success'];
    }
}
