<?php // -->
/**
 * This file is part of the Cradle PHP Library.
 * (c) 2016-2018 Openovate Labs
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Cradle\Composer;

use Cradle\Curl\CurlHandler;

/**
 * Packagist API Class
 *
 * @vendor   Cradle
 * @package  Composer
 * @author   John Doe <john@doe.com>
 * @standard PSR-4
 */
class Packagist 
{
    /**
     * @const string API_HOST
     */
    const API_HOST = 'https://packagist.org';

    /**
     * @const string PACKAGE_LIST
     */
    const PACKAGE_LIST = '/packages/list.json';

    /**
     * @const string PACKAGE_SEARCH
     */
    const PACKAGE_SEARCH = '/search.json';

    /**
     * @const string GET_PACKAGE_META
     */
    const GET_PACKAGE_META = '/p/%s/%s.json';

    /**
     * Request query
     * 
     * @var array $query
     */
    protected $query = [];

    /**
     * Dynamically set properties.
     * 
     * @param string $name
     * @param *mixed $args
     * @return $this
     */
    public function __call($name, $args)
    {
        //if method starts with set
        if (strpos($name, 'set') === 0) {
            //choose separator
            $separator = '_';

            //transform method to column name
            $key = substr($name, 3);
            $key = preg_replace("/([A-Z0-9])/", $separator."$1", $key);
            $key = substr($key, strlen($separator));
            $key = strtolower($key);

            //if arg isn't set
            if (!isset($args[0])) {
                //default is null
                $args[0] = null;
            }

            // if key is query
            if ($key == 'query') {
                // set to q
                $key = 'q';
            }

            // set to property
            $this->query[$key] = $args[0];
        }

        return $this;
    }

    /**
     * List package names based on
     * the given query, tags, type
     * or vendor.
     * 
     * @return array
     */
    public function list()
    {
        // create request url
        $url = $this->buildRequest(
            self::API_HOST, 
            self::PACKAGE_LIST, 
            $this->query
        );

        // send request
        $response = CurlHandler::i()
            ->setUrl($url)
            ->setCustomRequest('GET')
            ->getJsonResponse();

        return $response;
    }

    /**
     * Search for packages based on
     * the given query, tags, type.
     * 
     * @return array
     */
    public function search($page = null, $range = null)
    {
        // if page is set
        if ($page) {
            // set page
            $this->query['page'] = $page;
        }

        // if range is set
        if ($range) {
            // set range
            $this->query['range'] = $range;
        }

        // create request url
        $url = $this->buildRequest(
            self::API_HOST, 
            self::PACKAGE_SEARCH, 
            $this->query
        );

        // send request
        $response = CurlHandler::i()
            ->setUrl($url)
            ->setCustomRequest('GET')
            ->getJsonResponse();

        return $response;
    }

    /**
     * Get package meta data.
     * 
     * @param string $package
     * @return array
     */
    public function get($package)
    {
        // split package name
        $package = explode('/', $package);

        // create request url
        $url = $this->buildRequest(
            self::API_HOST, 
            sprintf(
                self::GET_PACKAGE_META,
                $package[0],
                $package[1]
            ),
            $this->query
        );

        // send request
        $response = CurlHandler::i()
            ->setUrl($url)
            ->setCustomRequest('GET')
            ->getJsonResponse();

        return $response;
    }

    /**
     * Builds a request query based
     * on the given url, path and query.
     * 
     * @param string $url
     * @param string $path
     * @param array $query
     * @return $this
     */
    private function buildRequest($url, $path, $query = [])
    {
        // request url
        $url = $url . $path;

        // build query
        $query = http_build_query($query);

        // if we have query
        if (!empty($query)) {
            // set query
            $url = $url . '?' . $query;
        }

        return $url;
    }
}