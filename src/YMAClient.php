<?php
namespace Moudarir\YMAClient;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;

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
     * @var string
     */
    private $version = '';

    /**
     * @var mixed|ResponseInterface
     */
    private $request;

    /**
     * YMAClient constructor.
     *
     * @param string $username
     * @param string $password
     * @param string $apiKey
     * @param string|null $endpoint
     */
    public function __construct (string $username, string $password, string $apiKey, string $endpoint = null) {
        $this->username = $username;
        $this->password = $password;
        $this->apiKey   = $apiKey;
        $this->client   = new Client(['base_uri' => is_null($endpoint) ? Statics::ENDPOINT : $endpoint]);
    }

    /**
     * request()
     *
     * @param string $method GET | POST
     * @param string $uri
     * @return YMAClient
     * @throws GuzzleException
     */
    public function request (string $method, string $uri): YMAClient {
        $options        = $this->setOptions();
        $version        = $this->getVersion();
        $uri            = $version.'/'.$uri;
        $this->request  = $this->client->request($method, $uri, $options);

        return $this;
    }

    /**
     * formatResponse()
     *
     * @return mixed|\stdClass
     */
    public function getResponse () {
        $format = new Format($this->request, $this->params);
        return $format->getResponse();
    }

    /**
     * @param string $format
     * @return YMAClient
     */
    public function setFormat (string $format = 'json'): YMAClient {
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
    public function setParams (array $params): YMAClient {
        $this->params = is_array($params) ? $params : [];
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
     * @return array
     */
    private function setOptions (): array {
        $formats        = Statics::FORMATS;
        $acceptFormat   = isset($formats[$this->format]) ? $formats[$this->format] : $formats[Statics::DEFAULT_FORMAT];
        $default        = [
            'auth'      => [$this->username, $this->password],
            'headers'   => [
                'Origin'    => $_SERVER['HTTP_HOST'],
                'User-Agent'=> null,
                'Accept'    => $acceptFormat,
                'X-API-KEY' => $this->apiKey
            ]
        ];
        $this->options  = !empty($this->params) ? array_merge($default, $this->params) : $default;
        return $this->options;
    }

    /**
     * getOptions()
     *
     * @return array
     */
    public function getOptions (): array {
        return $this->options;
    }

    /**
     * @param string $version
     * @return YMAClient
     */
    public function setVersion (string $version): YMAClient {
        $this->version = $version;
        return $this;
    }

    /**
     * @return string
     */
    public function getVersion (): string {
        if ($this->version === '') {
            $version = Statics::API_VERSION;
        } else {
            $length = strlen($this->version);
            if ($length > 3) {
                $version = Statics::API_VERSION;
            } else {
                $letter = Statics::getCharsFromPosition($this->version);

                if (strtolower($letter) === 'v') {
                    $version = $this->version;
                } else {
                    if ($length === 3) {
                        $version = Statics::API_VERSION;
                    } else {
                        $version = 'v'.$this->version;
                    }
                }
            }
        }

        return $version;
    }

}