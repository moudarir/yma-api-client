<?php
namespace Moudarir\YMAClient;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class YMAClient {

    /**
     * @var Client
     */
    private $client;

    /**
     * @var string
     */
    private $username;

    /**
     * @var string
     */
    private $password;

    /**
     * @var string
     */
    private $apiKey;

    /**
     * @var string
     */
    private $format;

    /**
     * @var array
     */
    private $params = [];

    /**
     * @var array
     */
    private $options;

    /**
     * YMAClient constructor.
     *
     * @param string $username
     * @param string $password
     * @param string $apiKey
     * @param string $environment
     */
    public function __construct ($username, $password, $apiKey, $environment = 'dev') {
        $this->username = $username;
        $this->password = $password;
        $this->apiKey   = $apiKey;

        $this->client   = new Client([
            'base_uri' => $environment === 'prod' ? Statics::BASE_URI_PROD : Statics::BASE_URI_DEV
        ]);

        $this->setOptions();
    }

    /**
     * request()
     *
     * @param string $method GET | POST
     * @param string $uri
     * @return mixed|\Psr\Http\Message\ResponseInterface
     */
    public function request ($method, $uri) {
        try {
            return $this->client->request($method, $uri, $this->options);
        } catch (GuzzleException $exception) {
            return null;
        }
    }

    /**
     * formatResponse()
     *
     * @param mixed|\Psr\Http\Message\ResponseInterface $request
     * @return mixed|\stdClass
     */
    public function getResponse ($request) {
        try {
            $format = new Format($request, $this->params);
            return $format->getResponse();
        } catch (\Exception $e) {
            $response = [
                'error'     => true,
                'code'      => $e->getCode(),
                'message'   => $e->getMessage()
            ];

            return (object)$response;
        }
    }

    /**
     * @param string $format
     * @return YMAClient
     */
    public function setFormat ($format = 'json') {
        $this->format = $format;
        return $this;
    }

    /**
     * @return string
     */
    public function getFormat () {
        return $this->format;
    }

    /**
     * @param array $params
     * @return YMAClient
     */
    public function setParams (array $params) {
        $this->params = $params;
        return $this;
    }

    /**
     * @return array
     */
    public function getParams (): array {
        return $this->params;
    }

    /**
     * setOptions()
     *
     * @return YMAClient
     */
    private function setOptions () {
        $formats        = Statics::FORMATS;
        $acceptFormat   = isset($formats[$this->format]) ? $formats[$this->format] : $formats[Statics::DEFAULT_FORMAT];
        $default        = [
            'auth'      => [$this->username, $this->password],
            'headers'   => [
                'Accept'    => $acceptFormat,
                'X-Api-Key' => $this->apiKey
            ]
        ];
        $this->options  = !empty($this->params) ? array_merge($default, $this->params) : $default;
        return $this;
    }

    /**
     * getOptions()
     *
     * @return array
     */
    public function getOptions (): array {
        return $this->options;
    }

}