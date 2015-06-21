<?php
/**
 * Webiny Htpl (https://github.com/Webiny/Htpl/)
 *
 * @copyright Copyright Webiny LTD
 */
namespace Webiny\Hrc;

/**
 * Class Request - this class is the holder for the information regarding the http request.
 *
 * @package Webiny\Hrc
 */
class Request
{
    /**
     * @var string Current url (just the path, without the protocol, domain and query params).
     */
    private $url;

    /**
     * @var array|null List of Http Request headers.
     */
    private $headers = [];

    /**
     * @var array|null List of Http Request headers.
     */
    private $cookies = [];

    /**
     * @var array|null List of Http Request query parameters.
     */
    private $queryParams = [];


    /**
     * Base constructor.
     *
     * @param string|null $url         Current url (just the path, without the protocol, domain and query params).
     * @param array|null  $headers     List of Http Request headers.
     * @param array|null  $cookies     List of Http Request headers.
     * @param array|null  $queryParams List of Http Request query parameters.
     */
    public function __construct($url = null, array $headers = null, array $cookies = null, array $queryParams = null)
    {
        $this->url = empty($url) ? $this->getCurrentUrl() : $url;
        $this->headers = empty($headers) ? $this->getCurrentHeaders() : $headers;
        $this->cookies = empty($cookies) ? $_COOKIE : $cookies;
        $this->queryParams = empty($queryParams) ? $_GET : $queryParams;

        // sort parameters so we always have them in same order
        ksort($this->headers);
        ksort($this->cookies);
        ksort($this->queryParams);
    }

    /**
     * Set the url.
     *
     * @param string $url Url, without the protocol, domain and query params.
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * Get the current url.
     *
     * @return null|string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set the headers.
     *
     * @param array $headers List of Http Request headers.
     */
    public function setHeaders(array $headers)
    {
        $this->headers = $headers;
    }

    /**
     * Get the headers.
     *
     * @return array|null
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Set the cookies.
     *
     * @param array $cookies List of Http Request cookies.
     */
    public function setCookies(array $cookies)
    {
        $this->cookies = $cookies;
    }

    /**
     * Get the cookies.
     *
     * @return array|null
     */
    public function getCookies()
    {
        return $this->cookies;
    }

    /**
     * Set the query params.
     *
     * @param array $queryParams List of Http Request query params.
     */
    public function setQueryParams(array $queryParams)
    {
        $this->queryParams = $queryParams;
    }

    /**
     * Get the query params.
     *
     * @return array|null
     */
    public function getQueryParams()
    {
        return $this->queryParams;
    }

    /**
     * Check if the url matches the given pattern.
     *
     * @param string $pattern Pattern to match.
     *
     * @return bool|null|string Url value if pattern matches, otherwise false.
     */
    public function matchUrl($pattern)
    {
        if ($this->matchValue($this->getUrl(), $pattern)) {
            return $this->getUrl();
        }

        return false;
    }

    /**
     * Check if the given header exists, and optionally if the pattern matches the header value.
     *
     * @param string      $name    Header name to match.
     * @param string|null $pattern Optional pattern that needs to match the header value.
     *
     * @return bool|string  False if the header doesn't exist, or the pattern doesn't match.
     *                      If the header exists and pattern matched, the header value is returned.
     */
    public function matchHeader($name, $pattern = null)
    {
        if (isset($this->getHeaders()[$name])) {
            if (!empty($pattern)) {
                if ($this->matchValue($this->getHeaders()[$name], $pattern)) {
                    return $this->getHeaders()[$name];
                }
            } else {
                if (empty($this->getHeaders()[$name])) {
                    return true;
                } else {
                    return $this->getHeaders()[$name];
                }
            }
        }

        return false;
    }

    /**
     * Check if the given cookie exists, and optionally if the pattern matches the cookie value.
     *
     * @param string      $name    Cookie name to match.
     * @param string|null $pattern Optional pattern that needs to match the cookie value.
     *
     * @return bool|string  False if the cookie doesn't exist, or the pattern doesn't match.
     *                      If the cookie exists and pattern matched, the cookie value is returned.
     */
    public function matchCookie($name, $pattern = null)
    {
        if (isset($this->getCookies()[$name])) {
            if (!empty($pattern)) {
                if ($this->matchValue($this->getCookies()[$name], $pattern)) {
                    return $this->getCookies()[$name];
                }
            } else {
                if (empty($this->getCookies()[$name])) {
                    return true;
                } else {
                    return $this->getCookies()[$name];
                }
            }
        }

        return false;
    }

    /**
     * Check if the given query param exists, and optionally if the pattern matches the query param value.
     *
     * @param string      $name    Query param name to match.
     * @param string|null $pattern Optional pattern that needs to match the query param value.
     *
     * @return bool|string  False if the query param doesn't exist, or the pattern doesn't match.
     *                      If the query param exists and pattern matched, the query param value is returned.
     */
    public function matchQueryParam($name, $pattern = null)
    {
        if (isset($this->getQueryParams()[$name])) {
            if (!empty($pattern)) {
                if ($this->matchValue($this->getQueryParams()[$name], $pattern)) {
                    return $this->getQueryParams()[$name];
                }
            } else {
                if (empty($this->getQueryParams()[$name])) {
                    return true;
                } else {
                    return $this->getQueryParams()[$name];
                }
            }
        }

        return false;
    }

    /**
     * Returns a list of current header from Http Request.
     *
     * @return array
     */
    private function getCurrentHeaders()
    {
        $headers = [];
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }

        return $headers;
    }

    /**
     * Returns the current url, just the path section.
     *
     * @return string
     */
    private function getCurrentUrl()
    {
        return strtok($_SERVER['REQUEST_URI'], '?');
    }

    /**
     * Method that tries to match the given pattern to the given value.
     *
     * @param string $value   Value where the pattern match will be performed.
     * @param string $pattern Pattern to match.
     *
     * @return bool True if pattern matched the value, otherwise false.
     */
    private function matchValue($value, $pattern)
    {
        if (strpos($pattern, '*') !== false) {
            $pattern = preg_quote($pattern, '#');
            $pattern = str_replace('\*', '(.+)', $pattern);

            return preg_match('#^' . $pattern . '$#', $value);
        } elseif (strpos($pattern, '(') !== false || strpos($pattern, '[') !== false || strpos($pattern, '\\') !== false
        ) {
            return preg_match('#^' . $pattern . '$#', $value);
        } else {
            return $value == $pattern;
        }
    }
}