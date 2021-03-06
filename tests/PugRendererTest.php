<?php

namespace Slim\Pug\Tests;

use Slim\Http\Body;
use Slim\Http\Headers;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Http\Stream;
use Slim\Http\Uri;
use Slim\Pug\PugRenderer;

class PugRendererTest extends AbstractTestCase
{
    public function testPugRenderer()
    {
        $rand = mt_rand(0, 99999999);
        $tempIn = sys_get_temp_dir() . '/streamIn-' . $rand . '.txt';
        $tempOut = sys_get_temp_dir() . '/streamOut-' . $rand . '.txt';
        touch($tempIn);
        touch($tempOut);
        $headers = new Headers();
        $uri = Uri::createFromString('/home/bob');
        $body = new Stream(fopen($tempIn, 'r'));
        $container = $this->getApp()->getContainer();
        $request = new Request('GET', $uri, $headers, [], [], $body);
        /** @var \Slim\Router $router */
        $router = $container->get('router');
        $route = $router->lookupRoute('route0');
        $route->prepare($request, [
            'name' => 'bob',
        ]);
        $response = new Response(200, null, new Body(fopen($tempOut, 'w')));
        $route->run($request, $response);

        self::assertSame(
            '<!DOCTYPE html><html>' .
            '<head><title>Home page</title></head>' .
            '<body><header><h1>Home page</h1></header><section>Hello bob</section><footer>Bye</footer></body>' .
            '</html>',
            str_replace(["\r", "\n"], '', file_get_contents($tempOut))
        );
    }

    public function testCreate()
    {
        self::assertInstanceOf(PugRenderer::class, PugRenderer::create()->getContainer()->renderer);
    }

    public function testGetTemplatePath()
    {
        $path = rtrim($this->getPug()->getTemplatePath(), DIRECTORY_SEPARATOR);

        self::assertSame($path, $this->getApp()->getContainer()['templates.path']);
    }

    public function testAttributes()
    {
        $this->getPug()->setAttributes([
            'foo' => 'bar',
        ]);
        $this->getPug()->addAttribute('biz', 42);
        $attributes = $this->getPug()->getAttributes();

        if ($attributes === []) {
            $this->markTestSkipped('setAttributes() not available in lowest versions');
        }

        self::assertSame([
            'foo' => 'bar',
            'biz' => 42,
        ], $attributes);
        self::assertSame('bar', $this->getPug()->getAttribute('foo'));
        self::assertSame(42, $this->getPug()->getAttribute('biz'));
        self::assertSame(null, $this->getPug()->getAttribute('bar'));
    }

    public function testAdapter()
    {
        $renderer = new PugRenderer(__DIR__, [
            'cache'         => 'foo',
            'upToDateCheck' => false,
        ]);

        self::assertSame('foo', $renderer->getOption('cache'));
        self::assertFalse($renderer->getOption('upToDateCheck'));
    }
}
