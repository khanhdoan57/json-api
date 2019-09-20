<?php

/**
* @author HackerBoy.com <admin@hackerboy.com>
* @package hackerboy/json-api
*
* Parser - extending from art4/json-api-client
*/
namespace HackerBoy\JsonApi\Helpers;

use Art4\JsonApiClient\Helper\Parser;

class Validator {

	/**
     * Checks if a string is a valid JSON API response body
     *
     * @param string $jsonString
     *
     * @return bool true, if $jsonString contains valid JSON API, else false
     */
    public static function isValidResponseString($jsonString)
    {
        return Parser::isValidResponseString($jsonString);
    }
    /**
     * Checks if a string is a valid JSON API request body
     *
     * @param string $jsonString
     *
     * @return bool true, if $jsonString contains valid JSON API, else false
     */
    public static function isValidRequestString($jsonString)
    {
        return Parser::isValidRequestString($jsonString);
    }

}