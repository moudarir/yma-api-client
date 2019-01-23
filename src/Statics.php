<?php
namespace Moudarir\YMAClient;

class Statics {

    const API_VERSION       = 'v1';
    const BASE_URI_DEV      = 'http://api.server.yma';
    const BASE_URI_PROD     = 'https://api.profile-check.net';
    const TIMEZONE          = 'GMT';
    const ALLOWED_METHODS   = ['get', 'delete', 'post', 'put', 'options', 'patch', 'head'];
    const DEFAULT_FORMAT    = 'json';
    const FORMATS           = [
        'json'          => 'application/json',
        'jsonp'         => 'application/javascript',
        'serialized'    => 'application/vnd.php.serialized',
        'xml'           => 'application/xml'
    ];

    /**
     * isEmptyObject()
     *
     * @param \stdClass $obj
     * @return bool
     */
    public static function isEmptyObject (\stdClass $obj): bool {
        return empty((array)$obj);
    }

    /**
     * checkStringInUri()
     *
     * @param string $source
     * @param string $string
     * @param boolean $strict
     * @return boolean
     */
    public static function checkString ($source, $string, $strict = false): bool {
        if ($strict)
            return strcmp($source, $string) === 0;
        else
            return strpos($source, $string) !== false;
    }

    /**
     * getStringFromPosition()
     *
     * @param string $string
     * @param integer $start
     * @param integer $length
     * @return boolean|string
     */
    public static function getStringFromPosition($string, $start = 0, $length = 1) {
        return substr($string, $start, $length);
    }
}