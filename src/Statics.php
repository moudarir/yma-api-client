<?php
namespace Moudarir\YMAClient;

class Statics {

    const API_VERSION       = 'v1';
    const ENDPOINT          = 'https://api.profile-check.net';
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
     * @param bool $strict
     * @return bool
     */
    public static function checkString (string $source, string $string, bool $strict = false): bool {
        if ($strict)
            return strcmp($source, $string) === 0;
        else
            return strpos($source, $string) !== false;
    }

    /**
     * getStringFromPosition()
     *
     * @param string $string
     * @param int $start
     * @param int $length
     * @return string|bool
     */
    public static function getCharsFromPosition (string $string, int $start = 0, int $length = 1):? string {
        return substr($string, $start, $length);
    }
}