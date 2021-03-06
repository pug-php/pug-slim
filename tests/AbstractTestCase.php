<?php

namespace Slim\Pug\Tests;

use PHPUnit\Framework\TestCase;
use Slim\App;
use Slim\Pug\PugRenderer;

abstract class AbstractTestCase extends TestCase
{
    /**
     * @var App
     */
    protected $app;

    /**
     * @var PugRenderer
     */
    protected $pug;

    protected function getApp()
    {
        if (!isset($this->app)) {
            $this->init();
        }

        return $this->app;
    }

    protected function getPug()
    {
        if (!isset($this->pug)) {
            $this->init();
        }

        return $this->pug;
    }

    protected function init()
    {
        $options = [
            'version'        => '0.0.0',
            'debug'          => false,
            'mode'           => 'testing',
            'templates.path' => __DIR__ . '/templates',
        ];

        if (class_exists('\\Tale\\Pug\\Renderer')) {
            $options['renderer'] = '\\Tale\\Pug\\Renderer';
        }

        $app = PugRenderer::create(new App($options));

        $app->get('/hello/{name}', function ($request, $response, $args) {
            return $this->renderer->render($response, '/home.pug', $args);
        });

        $this->app = $app;
        $this->pug = $app->getContainer()['renderer'];
    }
}
