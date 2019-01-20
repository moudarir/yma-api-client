<?php
namespace Moudarir\YMAClient;

use Exception;
use Laravie\Parser\Xml\Document;
use Laravie\Parser\Xml\Reader;
use Psr\Http\Message\ResponseInterface;

class Format {

    /**
     * @var array
     */
    private $params;

    /**
     * @var ResponseInterface|null
     */
    private $request;

    /**
     * @var string
     */
    private $outputFormat;

    private $content;

    /**
     * Format constructor.
     *
     * @param ResponseInterface $request
     * @param array $params
     * @throws Exception
     */
    public function __construct (ResponseInterface $request, array $params) {
        if (is_null($request)) {
            throw new Exception('No Content.', 204);
        }

        $this->params   = $params;
        $this->request  = $request;

        $this->setContent();
        $this->setOutputFormat();
    }

    /**
     * formatResponse()
     *
     * @return mixed|\stdClass|array
     */
    public function getResponse () {
        if ($this->outputFormat === 'json') {
            $contents = json_decode($this->content);
        } elseif ($this->outputFormat === 'xml') {
            $contents = $this->fromXML();
        } elseif ($this->outputFormat === 'serialized') {
            $contents = unserialize($this->content);
        } else {
            $contents = new \stdClass();
        }

        return $contents;
    }

    /**
     * @return Format
     */
    private function setContent () {
        $requestBody = $this->request->getBody();
        if (isset($this->params['stream']) && $this->params['stream'] === true) {
            $content = '';
            while (!$requestBody->eof() ) {
                $content .= $requestBody->read(1024);
            }
            $requestBody->close();
        } else {
            $content = $requestBody->getContents();
        }

        $this->content = $content;
        return $this;
    }

    /**
     * @return string
     */
    public function getContent (): string {
        return $this->content;
    }

    /**
     * @return Format
     */
    private function setOutputFormat () {
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
     * @return mixed
     */
    private function fromXML () {
        $parser = new Reader(new Document());
        $result = $parser->extract($this->content)->getOriginalContent();

        return $result;
    }

    /**
     * @return mixed
     */
    public function toArray () {
        return json_decode(json_encode($this->content), true);
    }

}