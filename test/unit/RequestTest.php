<?php
/**
 * Webiny Hrc (https://github.com/Webiny/Hrc/)
 *
 * @copyright Copyright Webiny LTD
 */
namespace Webiny\Hrc\UnitTests;

use Webiny\Hrc\Request;

class RequestTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var Request
     */
    public $r;

    public function setUp()
    {
        $this->r = new Request('http://test.url/some-path/folder/', ['Pragma' => 'no-cache', 'Accept' => '*/*'],
            ['foo' => 'bar', 'one' => 'two'], ['p1' => 'v1', 'p2' => 'v2']);
    }

    public function testConstruct()
    {
        $this->assertInstanceOf('Webiny\Hrc\Request', $this->r);
    }

    public function testSetGetUrl()
    {
        $this->assertSame('http://test.url/some-path/folder/', $this->r->getUrl());
        $this->r->setUrl('http://test.url/some-path/folder/2/');
        $this->assertSame('http://test.url/some-path/folder/2/', $this->r->getUrl());
    }

    public function testSetGetHeaders()
    {
        $this->assertSame(['Accept' => '*/*', 'Pragma' => 'no-cache'], $this->r->getHeaders());
        $this->r->setHeaders(['b' => 'c', 'a' => 'b']);
        $this->assertSame(['a' => 'b', 'b' => 'c'], $this->r->getHeaders());
    }

    public function testSetGetCookies()
    {
        $this->assertSame(['foo' => 'bar', 'one' => 'two'], $this->r->getCookies());
        $this->r->setCookies(['cookie' => 'value', 'a' => 'b']);
        $this->assertSame(['a' => 'b', 'cookie' => 'value'], $this->r->getCookies());
    }

    public function testSetGetQueryParams()
    {
        $this->assertSame(['p1' => 'v1', 'p2' => 'v2'], $this->r->getQueryParams());
        $this->r->setQueryParams(['b' => 'c', 'a' => 'b']);
        $this->assertSame(['a' => 'b', 'b' => 'c'], $this->r->getQueryParams());
    }

    /**
     * @param $url
     * @param $pattern
     *
     * @dataProvider urlProvider
     */
    public function testMatchUrl($url, $pattern)
    {
        $this->r->setUrl($url);
        $this->assertSame($url, $this->r->matchUrl($pattern));
    }

    public function urlProvider()
    {
        return [
            ['http://test.url/some-path', '*'],
            ['http://test.url/some-path', 'http://test.url/some-*'],
            ['http://test.url/some-path', 'http://test.url/some-path'],
            ['http://test.url/some/path/2/', 'http://test.url/*/*/2/'],
            ['http://test.url/some/path/2/', 'http://test.url/([\w]+)/([\w]+)/([\d])/']
        ];
    }

    /**
     * @param $headers
     * @param $name
     * @param $pattern
     *
     * @dataProvider headerProvider
     */
    public function testMatchHeader($headers, $name, $pattern)
    {
        $this->r->setHeaders($headers);
        $this->assertNotFalse($this->r->matchHeader($name, $pattern));
    }

    public function headerProvider()
    {
        return [
            [['a' => 'b'], 'a', 'b'],
            [['someHeader'=>'someValue'], 'someHeader', '*'],
            [['someHeader'=>'someValue'], 'someHeader', 'someV*'],
            [['a' => 'b', 'c'=>'test value'], 'c', 'test ([\w]+)'],
        ];
    }

    /**
     * @param $cookies
     * @param $name
     * @param $pattern
     *
     * @dataProvider cookieProvider
     */
    public function testMatchCookie($cookies, $name, $pattern)
    {
        $this->r->setHeaders($cookies);
        $this->assertNotFalse($this->r->matchHeader($name, $pattern));
    }

    public function cookieProvider()
    {
        return [
            [['a' => 'b'], 'a', 'b'],
            [['someHeader'=>'someValue'], 'someHeader', '*'],
            [['someHeader'=>'someValue'], 'someHeader', 'someV*'],
            [['a' => 'b', 'c'=>'test value'], 'c', 'test ([\w]+)'],
        ];
    }

    /**
     * @param $queryParams
     * @param $name
     * @param $pattern
     *
     * @dataProvider queryParamProvider
     */
    public function testMatchQueryParam($queryParams, $name, $pattern)
    {
        $this->r->setHeaders($queryParams);
        $this->assertNotFalse($this->r->matchHeader($name, $pattern));
    }

    public function queryParamProvider()
    {
        return [
            [['a' => 'b'], 'a', 'b'],
            [['someHeader'=>'someValue'], 'someHeader', '*'],
            [['someHeader'=>'someValue'], 'someHeader', 'someV*'],
            [['a' => 'b', 'c'=>'test value'], 'c', 'test ([\w]+)'],
        ];
    }


}