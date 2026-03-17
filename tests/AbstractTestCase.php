<?php

namespace Slim\Pug\Tests;

use DI\ContainerBuilder;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Slim\App;
use Slim\Factory\AppFactory;
use Slim\Http\Body as Slim3Body;
use Slim\Http\Headers as Slim3Headers;
use Slim\Http\Request as Slim3Request;
use Slim\Http\Response as Slim3Response;
use Slim\Http\Stream as Slim3Stream;
use Slim\Http\Uri as Slim3Uri;
use Slim\Psr7\Headers;
use Slim\Psr7\Request;
use Slim\Psr7\Stream;
use Slim\Psr7\Uri;
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

        // Slim 4
        if (class_exists(AppFactory::class)) {
            // Instantiate PHP-DI ContainerBuilder
            $containerBuilder = new ContainerBuilder();

            // Build PHP-DI Container instance
            $container = $containerBuilder->build();

            // Instantiate the app
            AppFactory::setContainer($container);
            $app = AppFactory::create();

            PugRenderer::create($app, $options['templates.path']);

            $renderer = $app->getContainer()->get('renderer');

            $this->app = $app;
            $this->pug = $renderer;

            $app->get('/hello/{name}', function ($request, $response, $args) use ($renderer) {
                return $renderer->render($response, '/home.pug', $args);
            });

            // Add Routing Middleware
            $app->addRoutingMiddleware();

            // Add Body Parsing Middleware
            $app->addBodyParsingMiddleware();

            return;
        }

        // Slim 3
        $app = PugRenderer::create(new App($options));

        $app->get('/hello/{name}', function ($request, $response, $args) {
            return $this->renderer->render($response, '/home.pug', $args);
        });

        $this->app = $app;
        $this->pug = $app->getContainer()['renderer'];
    }

    protected function fetch(string $path): string
    {
        $app = $this->getApp();

        $rand = mt_rand(0, 99999999);
        $tempIn = sys_get_temp_dir() . '/streamIn-' . $rand . '.txt';
        touch($tempIn);

        // Slim 4
        if (class_exists(Headers::class)) {
            // Run App & Emit Response
            $response = $app->handle(new Request(
                'GET',
                new Uri('https', 'domain.com', 443, $path),
                new Headers(),
                [],
                [],
                new Stream(fopen($tempIn, 'r+')),
            ));

            $body = $response->getBody();

            if ($body->isSeekable()) {
                $body->rewind();
            }

            return $body->read($body->getSize());
        }

        // Slim 3
        $tempOut = sys_get_temp_dir() . '/streamOut-' . $rand . '.txt';
        touch($tempOut);
        $headers = new Slim3Headers();
        $uri = Slim3Uri::createFromString($path);
        $body = new Slim3Stream(fopen($tempIn, 'r'));
        $container = $app->getContainer();
        $request = new Slim3Request('GET', $uri, $headers, [], [], $body);
        /** @var \Slim\Router $router */
        $router = $container->get('router');
        $route = $router->lookupRoute('route0');
        $route->prepare($request, [
            'name' => 'bob',
        ]);
        $response = new Slim3Response(200, null, new Slim3Body(fopen($tempOut, 'w')));
        $route->run($request, $response);

        return $this->getContents($path, $tempOut);
    }

    private function getContents(string $path, string $file): string
    {
        $contents = file_get_contents($file);

        if ($contents === false) {
            throw new RuntimeException("Unable to fetch '$path'");
        }

        return $contents;
    }
}
