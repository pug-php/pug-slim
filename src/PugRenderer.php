<?php

namespace Slim\Pug;

use ArrayAccess;
use Psr\Http\Message\ResponseInterface;
use Pug\Pug;
use Slim\App;

/**
 * Class PugRenderer.
 *
 * Render Pug view scripts into a PSR-7 Response object.
 */
class PugRenderer
{
    /**
     * @var Pug
     */
    private $adapter;

    public function __construct($templatePath = null, $options = [], $attributes = [])
    {
        if (is_array($templatePath)) {
            $attributes = $options;
            $options = $templatePath;
            $templatePath = isset($options['templates.path']) ? $options['templates.path'] : null;
        }

        $className = isset($options['renderer']) ? $options['renderer'] : Pug::class;

        if ($options instanceof ArrayAccess) {
            $options = (array) $options;
        }

        $this->adapter = new $className($options);

        if ($templatePath) {
            $this->setTemplatePath($templatePath);
        }

        $this->adapter->share($attributes);
    }

    /**
     * Create a pug renderer and append it to the slim app given in parameter.
     *
     * @param App    $app
     * @param string $templatePath
     * @param array  $options
     * @param array  $attributes
     *
     * @return App
     */
    public static function create(App $app = null, $templatePath = null, array $options = [], array $attributes = [])
    {
        if (!$app) {
            $app = new App();
        }
        $container = $app->getContainer();
        $templatePath = $templatePath ?: (isset($container['templates.path']) ? $container['templates.path'] : null);
        $container['renderer'] = new static($templatePath, $options, $attributes);

        return $app;
    }

    /**
     * Return the adapter option or the given default value, or null if no default given.
     *
     * @param string $name
     * @param null   $default
     *
     * @return mixed
     */
    public function getOption($name, $default = null)
    {
        // @codeCoverageIgnoreStart
        try {
            return method_exists($this->adapter, 'hasOption') && !$this->adapter->hasOption($name)
                ? $default
                : $this->adapter->getOption($name);
        } catch (\InvalidArgumentException $exception) {
            return $default;
        }
        // @codeCoverageIgnoreEnd
    }

    /**
     * Set an option of the adapter.
     *
     * @param string $name
     * @param mixed  $value
     *
     * @return $this
     */
    public function setOption($name, $value)
    {
        $method = method_exists($this->adapter, 'setCustomOption')
            ? [$this->adapter, 'setCustomOption']
            : [$this->adapter, 'setOption'];
        call_user_func($method, $name, $value);

        return $this;
    }

    /**
     * Set options of the adapter.
     *
     * @param array $options
     *
     * @return $this
     */
    public function setOptions(array $options)
    {
        foreach ($options as $name => $value) {
            $this->setOption($name, $value);
        }

        return $this;
    }

    /**
     * Get the attributes for the renderer.
     *
     * @return array
     */
    public function getAttributes()
    {
        return array_merge(
            $this->getOption('globals') ?: [],
            $this->getOption('shared_variables') ?: []
        );
    }

    /**
     * Set the attributes for the renderer.
     *
     * @param array $attributes
     */
    public function setAttributes(array $attributes)
    {
        $this->adapter->share($attributes);
    }

    /**
     * Add an attribute.
     *
     * @param string $key
     * @param mixed  $value
     */
    public function addAttribute($key, $value)
    {
        $this->adapter->share($key, $value);
    }

    /**
     * Retrieve an attribute.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function getAttribute($key)
    {
        $attributes = $this->getAttributes();

        return isset($attributes[$key]) ? $attributes[$key] : null;
    }

    /**
     * Get the template path.
     *
     * @return string
     */
    public function getTemplatePath()
    {
        return $this->getOption('base_dir');
    }

    /**
     * Set the template path.
     *
     * @param string $templatePath
     *
     * @return $this
     */
    public function setTemplatePath($templatePath)
    {
        $templatePath = rtrim($templatePath, '/\\');
        $this->setOptions([
            'path'     => $templatePath,
            'paths'    => [$templatePath],
            'basedir'  => $templatePath,
            'base_dir' => $templatePath,
        ]);

        return $this;
    }

    /**
     * Renders a template.
     *
     * Fetches the template and wraps it in a response object.
     *
     * @param ResponseInterface $response
     * @param string            $template
     * @param array             $data
     *
     * @throws \InvalidArgumentException if it contains template as a key
     * @throws \RuntimeException         if `$templatePath . $template` does not exist
     *
     * @return ResponseInterface
     */
    public function render(ResponseInterface $response, $template, array $data = [])
    {
        $output = $this->fetch($template, $data);
        $response->getBody()->write($output);

        return $response;
    }

    /**
     * Fetches a template and returns the result as a string.
     *
     * @param string $template
     * @param array  $data
     *
     * @throws \InvalidArgumentException if it contains template as a key
     * @throws \RuntimeException         if `$templatePath . $template` does not exist
     *
     * @return mixed
     */
    public function fetch($template, array $data = [])
    {
        if (!method_exists($this->adapter, 'renderFile')) {
            $file = $this->getTemplatePath();
            $file = $file ? $file : '';
            $lastChar = substr($file, -1);
            // @codeCoverageIgnoreStart
            if ($lastChar !== '/' && $lastChar !== '\\') {
                $firstChar = substr($template, 0, 1);
                if ($firstChar !== '/' && $firstChar !== '\\') {
                    $file .= '/';
                }
            }
            // @codeCoverageIgnoreEnd
            $file .= $template;

            return $this->adapter->render(file_get_contents($file), $file, $data);
        }

        // @codeCoverageIgnoreStart
        if ($this->adapter instanceof \Tale\Pug\Renderer && !($this->adapter instanceof \Phug\Renderer)) {
            $this->adapter->compile(''); // Init ->files
        }
        // @codeCoverageIgnoreEnd

        return $this->adapter->renderFile($template, $data);
    }
}
