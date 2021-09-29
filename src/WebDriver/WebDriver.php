<?php

/*
 * This file is part of the Headless package.
 *
 * (c) Mark Fluehmann mark.fluehmann@gmail.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace timatanga\Headless\WebDriver;

use timatanga\Headless\Exceptions\WebDriverException;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Firefox\FirefoxOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class WebDriver
{

    /**
     * Web driver instance
     *
     * @var Facebook\WebDriver\Remote\RemoteWebDriver
     */
    protected $driver;

    /**
     * Remote server process
     *
     * @var Symfony\Process
     */
    protected $process;

    /**
     * Browser options
     *
     * @var array
     */
    protected $options;

    /**
     * Path to remote server binary
     *
     * @var Symfony\Process
     */
    protected $path;

    /**
     * Path to the web driver directory.
     *
     * @var string
     */
    protected $driverDir = __DIR__.'/../../bin/';

    /**
     * Webdriver options
     *
     * @var Array
     */
    protected $webdriverOptions = [
        'chrome' =>		'Facebook\WebDriver\Chrome\ChromeOptions',
        'firefox' =>    'Facebook\WebDriver\Chrome\FirefoxOptions',
    ];


    /**
     * Create browser instance
     * 
     * @param  string   $browser        browser type, default: chrome
     * @param  array    $options      	browser arguments
     * @param  string   $path      		path to server binary
     */
    public function __construct( $browser = 'chrome', array $options = [], $path = null )
    {
    	// browser options
    	$this->options = $options;

    	// get remote server binary path
    	$this->path = !is_null($path) ? $path : $this->findBinary();

    	// create remote server process
    	$this->process = $this->createProcess();

    	// start remote server process
    	$this->startProcess();

    	// create web driver
    	$this->driver = $this->createDriver($browser);
    }


    /**
     * Stop remote server process
     * 
     * @return void
     */
    public function stop()
    {
        $this->driver->quit();

        $this->process->stop();
    }


	/**
     * Create web driver
     * 
     * @param string 	$browser 	remote server url
     * @param string 	$url 		remote server url
     * @return 
     */
	protected function createDriver( $browser = 'chrome', $url = 'http://localhost:9515', $timeout = 30000 )
	{
        try {
        	// check browser compatibility
        	if (! array_key_exists($browser, $this->webdriverOptions) )
	            throw new WebDriverException('Browser is not supported: ' . $browser);

        	// create browser option instance
			$optionClass = $this->webdriverOptions[$browser];

			// build browser options
			$options = $this->setOptions($optionClass);

        	$capabilities = DesiredCapabilities::{$browser}();
			$capabilities->setCapability($optionClass::CAPABILITY, $options);

            $driver = retry(3, function () use($url, $capabilities, $timeout) {
            	return RemoteWebDriver::create( $url, $capabilities, $timeout);
			}, 50);

        	return $driver;

        } catch ( \Exception $e ) {
            throw new WebDriverException($e->getMessage());

        }
	}


	/**
     * Set web driver options
     * 
     * @param string 	$browser 	browser type, default: crome
     * @return 
     */
	protected function setOptions( string $optionClass )
	{
		$options = new $optionClass;

		$options->addArguments($this->options);

		return $options;
	}


	/**
     * Get web driver instance
     * 
     * @return Facebook\WebDriver\Remote\RemoteWebDriver
     */
	public function getDriver()
	{
		return $this->driver;
	}


	/**
     * Create remote server process
     * 
     * @param  string  	$port 			server process listening port
     * @param  array  	$arguments
     * @param  int  	$timeout 		timeout in seconds, default: 60
     * @return \Symfony\Component\Process\Process
     */
    public function createProcess( $port = '9515', array $arguments = [], $timeout = 30 )
	{
		$process = new Process(
            array_merge([$this->path], ['--port='.$port], $arguments), null, $this->browserEnvironment()
        );

		$process->setTimeout($timeout);

		return $process;
	}


	/**
     * Start remote server process
     * 
     * @return void
     */
    public function startProcess()
	{
		try {
		    $this->process->start();

		} catch (ProcessFailedException $exception) {
		    throw new WebDriverException($exception->getMessage());

		}
	}


    /**
     * Set remote web driver environment variables.
     *
     * @return array
     */
    protected function browserEnvironment()
    {
        if ( PHP_OS_FAMILY == 'Darwin' || PHP_OS_FAMILY == 'Windows' )
            return [];

        return ['DISPLAY' => $_ENV['DISPLAY'] ?? ':0'];
    }


    /**
     * Find remote server binary
     * 
     * @param String|Array  $path
     * @param String        $filename
     * @return {Array}
     */
    protected function findBinary()
    {
        // evaluate underlying operating system
        $os = PHP_OS_FAMILY;

        $path = $this->driverDir.'chromedriver-'.$os;

        if (! is_file($path) )
            throw new WebDriverException('Unable to find webdriver binary');

        return $path;
    }
}