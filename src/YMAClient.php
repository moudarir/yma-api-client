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
     * @var string
     */
    private $outputFormat;

    /**
     * @var array
     */
    private $params = [];

    /**
     * @var string
     */
    private $version = Statics::API_VERSION;

    /**
     * @var mixed|ResponseInterface
     */
    private $request;

    /**
     * @var string
     */
    private $content;

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
        $this->client   = new Client(['base_uri' => $endpoint ?: Statics::ENDPOINT]);
    }

    /**
     * @param string $method GET | POST
     * @param string $uri
     * @return self
     * @throws GuzzleException
     */
    public function request (string $method, string $uri): self {
        $uri            = $this->version.'/'.$uri;
        $this->request  = $this->client->request($method, $uri, $this->params);
        $this->setContent();
        $this->setOutputFormat();

        return $this;
    }

    /**
     * @return mixed|ResponseInterface
     */
    public function getRequest () {
        return $this->request;
    }

    /**
     * @return array|string
     */
    public function getResponse () {
        switch ($this->outputFormat) {
            case 'json':
                $response = json_decode($this->content, true);
                break;
            case 'xml':
                $response = $this->content;
                break;
            case 'serialized':
                $response = unserialize($this->content);
                break;
            default:
                $response = [];
                break;
        }

        return $response;
    }

    /**
     * @param string $format
     * @return self
     */
    public function setFormat (string $format = 'json'): self {
        $this->format = $format;
        return $this;
    }

    /**
     * @return string
     */
    public function getFormat (): string {
        return $this->format;
    }

    /**
     * @return self
     */
    private function setOutputFormat (): self {
        $contentTypes   = $this->request->getHeader('Content-Type');
        $contentType    = '';
        $format         = 'json';

        if (!empty($contentTypes)) {
            foreach ($contentTypes as $type) {
                $arr = explode(';', $type);
                $contentType = $arr[0];
                break;
            }

            switch ($contentType) {
                case 'application/xml':
                case 'text/xml':
                    $format = 'xml';
                    break;
                case 'application/vnd.php.serialized':
                    $format = 'serialized';
                    break;
                case 'application/json':
                case 'application/javascript':
                default:
                    $format = 'json';
                    break;
            }
        }

        $this->outputFormat = $format;

        return $this;
    }

    /**
     * @return string
     */
    public function getOutputFormat (): string {
        return $this->outputFormat;
    }

    /**
     * @param array $params
     * @return self
     */
    public function setParams (array $params = []): self {
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
        $this->params   = !empty($params) ? array_merge($params, $default) : $default;
        return $this;
    }

    /**
     * @return array
     */
    public function getParams (): array {
        return $this->params;
    }

    /**
     * @param string $version
     * @return self
     */
    public function setVersion (string $version = null): self {
        if (!is_null($version)) {
            $length = strlen($version);
            if ($length <= 3) {
                $letter = Statics::getCharsFromPosition($version);

                if (strtolower($letter) === 'v') {
                    $this->version = $version;
                } else {
                    if ($length !== 3) {
                        $this->version = 'v'.$version;
                    }
                }
            }
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getVersion (): string {
        return $this->version;
    }

    /**
     * @return string
     */
    public function getContent (): string {
        return $this->content;
    }

    /**
     * @return self
     */
    private function setContent (): self {
        $requestBody = $this->request->getBody();
        if (isset($this->params['stream']) && $this->params['stream'] === true) {
            $content = '';
            while (!$requestBody->eof()) {
                $content .= $requestBody->read(1024);
            }
            $requestBody->close();
        } else {
            $content = $requestBody->getContents();
        }

        $this->content = $content;

        return $this;
    }

}