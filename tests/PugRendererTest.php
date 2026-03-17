<?php

namespace Slim\Pug\Tests;

use ArrayAccess;
use DI\Container;
use Slim\App;
use Slim\Pug\PugRenderer;

class PugRendererTest extends AbstractTestCase
{
    public function testPugRenderer()
    {
        self::assertSame(
            '<!DOCTYPE html><html>' .
            '<head><title>Home page</title></head>' .
            '<body><header><h1>Home page</h1></header><section>Hello bob</section><footer>Bye</footer></body>' .
            '</html>',
            str_replace(["\r", "\n"], '', $this->fetch('/hello/bob'))
        );
    }

    public function testCreate()
    {
        self::assertInstanceOf(App::class, PugRenderer::create());
    }

    public function testGetTemplatePath()
    {
        $path = rtrim($this->getPug()->getTemplatePath(), DIRECTORY_SEPARATOR);
        $container = $this->getApp()->getContainer();
        $v4 = (defined(App::class . '::VERSION') && ((int) App::VERSION) >= 4);

        self::assertSame(__DIR__ . '/templates', $path);
        self::assertInstanceOf(
            $v4 ? Container::class : ArrayAccess::class,
            $container
        );
        self::assertSame(
            __DIR__ . '/templates',
            $v4 ? $container->get('renderer')->getOption('path') : $container['templates.path']
        );
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
