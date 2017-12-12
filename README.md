# Pug for Slim

[![License](https://poser.pugx.org/pug/slim/license.svg)](https://packagist.org/packages/pug/slim)
[![Latest Stable Version](https://poser.pugx.org/pug/slim/v/stable.png)](https://packagist.org/packages/pug/slim)
[![Build Status](https://travis-ci.org/pug-php/pug-slim.svg?branch=master)](https://travis-ci.org/pug-php/pug-slim)
[![Code Climate](https://codeclimate.com/github/pug-php/pug-slim/badges/gpa.svg)](https://codeclimate.com/github/pug-php/pug-slim)
[![Test Coverage](https://codeclimate.com/github/pug-php/pug-slim/badges/coverage.svg)](https://codeclimate.com/github/pug-php/pug-slim/coverage)
[![Issue Count](https://codeclimate.com/github/pug-php/pug-slim/badges/issue_count.svg)](https://codeclimate.com/github/pug-php/pug-slim)
[![StyleCI](https://styleci.io/repos/95650139/shield?branch=master)](https://styleci.io/repos/95650139)

For details about the template engine see [phug-lang.com](https://www.phug-lang.com)

## Installation

Install with [Composer](http://getcomposer.org):

```bash
composer require pug/slim
```

## Usage with Slim 3

```php
use Slim\App;
use Slim\Views\PugRenderer;

include 'vendor/autoload.php';

$slimOptions = []; // here you can pass Slim settings
$app = PugRenderer::create(new App($slimOptions), './templates');

$app->get('/hello/{name}', function ($request, $response, $args) {
    return $this->renderer->render($response, 'hello.pug', $args);
});

$app->run();
```

PS: If you don't pass an application to the `create` method, we
will automatically initialize one, so you can just do:

```php
use Slim\Views\PugRenderer;

include 'vendor/autoload.php';

$app = PugRenderer::create(null, './templates');
```


## Usage with any PSR-7 Project

```php
//Construct the View
$pugView = new PugRenderer('./path/to/templates', [
  'option' => 'foobar',
]);

//Render a Template
$response = $pugView->render(new Response(), '/path/to/template.pug', $yourData);
```

## Template Variables

You can add variables to your renderer that will be available to all templates you render.

```php
// via the constructor
$templateVariables = [
  'title' => 'Title',
];
$pugView = new PugRenderer('./path/to/templates', [], $templateVariables);

// or setter
$pugView->setAttributes($templateVariables);

// or individually
$pugView->addAttribute($key, $value);
```

Data passed in via `->render()` takes precedence over attributes.
```php
$templateVariables = [
  'title' => 'Title',
];
$pugView = new PhpRenderer('./path/to/templates', $templateVariables);

//...

$pugView->render($response, $template, [
    'title' => 'My Title',
]);
// In the view above, the $title will be "My Title" and not "Title"
```

By default, [pug-php](https://github.com/pug-php/pug) is used.
But you can specify an other engine:
```php
$app = PugRenderer::create(null, null, [
  'renderer' => \Phug\Renderer::class,
]);
```
PS: Phug is automatically installed with default install since Pug-php 3
use it internally. But you can also install different renderer engine,
for example tale-pug:
```bash
composer require talesoft/tale-pug
```
```php
$app = PugRenderer::create(null, null, [
  'renderer' => \Tale\Pug\Renderer::class,
]);
```
Note that in this case, you have no guarantee that all options
will work. 

## References

 * Pug-php 3 / Phug official documentation [www.phug-lang.com](https://www.phug-lang.com)
 * Pug-php 3 / Phug live editor [pug-demo.herokuapp.com](https://pug-demo.herokuapp.com)
 * To learn more about Pug go to [pugjs.org](https://pugjs.org)
 * Here is an online HTML to Pug converter [html2pug.herokuapp.com](https://html2pug.herokuapp.com/)
 
## Credits

This project is forked from https://github.com/MarcelloDuarte/pug-slim
And we added to it phug, pug-php 3, tale-jade and tale-pug support.
