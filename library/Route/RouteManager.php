<?php

namespace Bootlace\Route;

use Bootlace\Route\DataGenerator\DataGeneratorInterface;
use Bootlace\Route\Dispatcher\DispatcherInterface;
use Bootlace\Route\Exception\InvalidCacheFileFormatException;
use Bootlace\Route\Exception\InvalidRouteFileException;
use Bootlace\Route\RouteParser\RouteParserInterface;

/**
 * Class RouteManager.
 *
 * @package Bootlace\Route
 */
class RouteManager
{
    /* @var string $_dispatchingStrategy */
    private $_dispatchingStrategy = "GroupCountBased";

    /* @var string $_parsingStrategy */
    private $_parsingStrategy = "Std";

    /* @var DispatcherInterface $_dispatcher */
    protected $_dispatcher;

    /* @var DataGeneratorInterface $_dataGenerator */
    protected $_dataGenerator;

    /* @var RouteParserInterface $_routeParser */
    protected $_routeParser;

    /* @var RouteCollector $_routeCollector */
    protected $_routeCollector;

    /* @var string $_cacheFile */
    private $_cacheFile;

    /* @var string $_routeFile */
    private $_routeFile;

    /* @var string $_baseURL */
    private $_baseURL = '';

    /**
     * RouteManager constructor.
     *
     * @param null|string $dispatchingStrategy
     */
    function __construct(?string $dispatchingStrategy = null)
    {
        if (!is_null($dispatchingStrategy)) {
            $this->_dispatchingStrategy = $dispatchingStrategy;
        }
    }

    /**
     * Lazy initialization of $_dispatcher.
     *
     * @param array $data
     * @return DispatcherInterface
     */
    public function getDispatcher(array $data): DispatcherInterface
    {
        if (is_null($this->_dispatcher)) {
            $class = "Bootlace\\Route\\Dispatcher\\$this->_dispatchingStrategy";
            $this->_dispatcher = new $class($data);
        }
        return $this->_dispatcher;
    }

    /**
     * Lazy initialization of $_dataGenerator.
     *
     * @return DataGeneratorInterface
     */
    public function getDataGenerator(): DataGeneratorInterface
    {
        if (is_null($this->_dataGenerator)) {
            $class = "Bootlace\\Route\\DataGenerator\\$this->_dispatchingStrategy";
            $this->_dataGenerator = new $class();
        }
        return $this->_dataGenerator;
    }

    /**
     * Lazy initialization of $_routeParser.
     *
     * @return RouteParserInterface
     */
    public function getRouteParser(): RouteParserInterface
    {
        if (is_null($this->_routeParser)) {
            $class = "Bootlace\\Route\\RouteParser\\$this->_parsingStrategy";
            $this->_routeParser = new $class();
        }
        return $this->_routeParser;
    }

    /**
     * Lazy initialisation of $_routeCollector.
     *
     * @return RouteCollector
     */
    public function getRouteCollector(): RouteCollector
    {
        if (is_null($this->_routeCollector)) {
            $this->_routeCollector = new RouteCollector($this->getRouteParser(), $this->getDataGenerator());
        }
        return $this->_routeCollector;
    }

    /**
     * Sets the route file destination.
     *
     * @param string $file
     * @return RouteManager
     */
    public function setRouteFile(string $file): RouteManager
    {
        $this->_routeFile = $file;
        return $this;
    }

    /**
     * Sets the cache file destination.
     *
     * @param string $file
     * @return RouteManager
     */
    public function setCacheFile(string $file): RouteManager
    {
        $this->_cacheFile = $file;
        return $this;
    }

    /**
     * @param string $baseURL
     * @return RouteManager
     */
    public function setBaseURL(string $baseURL): RouteManager
    {
        $this->_baseURL = $baseURL;
        return $this;
    }

    /**
     * Generates the dispatched data.
     *
     * @return array
     * @throws \Exception
     */
    protected function generateRouteCollector()
    {
        $routeCollector = $this->getRouteCollector();
        /** @noinspection PhpIncludeInspection */
        $routeData = require $this->_routeFile;
        foreach ($routeData as $route) {
            $routeCollector->addRoute($route[0], $this->_baseURL . '/' . $route[1], $route[2]);
        }
        return $routeCollector->getData();
    }

    /**
     * Caches the dispatched data.
     *
     * @param array $data
     */
    private function cacheDispatchData(array $data)
    {
        file_put_contents(
            $this->_cacheFile,
            '<?php return ' . var_export($data, true) . ';'
        );
    }

    /**
     * @param string $method
     * @param string $path
     * @return array
     */
    public function dispatch(string $method, string $path)
    {
        if (!file_exists($this->_routeFile)) {
            throw new InvalidRouteFileException($this->_routeFile);
        }
        if (!file_exists($this->_cacheFile)) {
            throw new InvalidRouteFileException($this->_cacheFile);
        }

        if (filemtime($this->_cacheFile) > filemtime($this->_routeFile)) {
            /** @noinspection PhpIncludeInspection */
            $dispatchData = require $this->_cacheFile;
            if (!is_array($dispatchData)) {
                throw new InvalidCacheFileFormatException($this->_cacheFile);
            }
        } else {
            $dispatchData = $this->generateRouteCollector();
            $this->cacheDispatchData($dispatchData);
        }
        return $this->getDispatcher($dispatchData)->dispatch($method, $path);
    }
}