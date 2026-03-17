<?php

namespace Slim\Pug\Tests;

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
