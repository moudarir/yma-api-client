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
    }

    /**
     * formatResponse()
     *
     * @param boolean $asArray
     * @return mixed|\stdClass|array
     */
    public function formatResponse ($asArray = false) {
        $contents   = $this->getContents();
        $format     = $this->getFormat();

        if ($format === 'json') {
            $contentsObject = json_decode($contents);
        } elseif ($format === 'xml') {
            $parser         = new Reader(new Document());
            $contentsObject = $parser->extract($contents)->getOriginalContent();
        } elseif ($format === 'serialized') {
            $contentsObject = unserialize($contents);
        } else {
            $contentsObject = new \stdClass();
        }

        return $asArray ? json_decode(json_encode($contentsObject), true) : $contentsObject;
    }

    /**
     * @return string
     */
    private function getContents (): string {
        $requestBody = $this->request->getBody();
        if (isset($this->params['stream']) && $this->params['stream'] === true) {
            $contents = '';
            while (!$requestBody->eof() ) {
                $contents .= $requestBody->read(1024);
            }
            $requestBody->close();
        } else {
            $contents = $requestBody->getContents();
        }

        return $contents;
    }

    /**
     * @return string
     */
    private function getFormat (): string {
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

        return $format;
    }

}