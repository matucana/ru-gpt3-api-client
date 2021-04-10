<?php


namespace Matucana\RuGpt3Api;


use Capsule\Factory\StreamFactory;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Laminas\Diactoros\Request;
use Laminas\Diactoros\Uri;

class Client
{
    private ClientInterface $httpClient;

    public RequestInterface $request;

    private const BASE_URL = 'https://api.aicloud.sbercloud.ru/public/v1/public_inference/gpt3';

    private const USER_AGENT = 'ru-gpt3-api-client/1.0';


    public function __construct(ClientInterface $httpClient)
    {
       $this->httpClient = $httpClient;
        $this->request = (new Request())
            ->withMethod('POST')
            ->withAddedHeader('User-Agent', self::USER_AGENT)
            ->withAddedHeader('Accept', 'application/json')
            ->withAddedHeader('Content-Type', 'application/json');
    }

    public function predict(string $text)
    {
        $request = $this->request->withUri(new Uri($this->generateUrl('predict')));
        $stream = (new StreamFactory())->createStream(json_encode(["text" => $text]));
        $request = $request->withBody($stream);
        return $this->validateResponse($this->sendRequest($request));
    }

    public function generateUrl(string $methodApi): string
    {
        return self::BASE_URL.'/'.$methodApi;
    }

    public function sendRequest(RequestInterface $request):ResponseInterface
    {
        return $this->httpClient->sendRequest($request);
    }

    public function validateResponse(ResponseInterface $response): string
    {
        if ($response->getStatusCode() === 200) {
            return $response->getBody()->getContents();
        } else {
            throw new \Exception($response->getBody()->getContents());
        }
    }
}