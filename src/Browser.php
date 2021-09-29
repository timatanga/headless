<?php

/*
 * This file is part of the Headless package.
 *
 * (c) Mark Fluehmann mark.fluehmann@gmail.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace timatanga\Headless;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverDimension;
use Facebook\WebDriver\WebDriverPoint;
use timatanga\Headless\Exceptions\WebDriverException;
use timatanga\Headless\Traits\SupportElements;
use timatanga\Headless\Traits\SupportFiles;
use timatanga\Headless\Traits\SupportScripts;
use timatanga\Headless\WebDriver\DriverInstaller;
use timatanga\Headless\WebDriver\WebDriver;

class Browser
{
    use SupportFiles, SupportElements, SupportScripts;

    /**
     * Handler to interact with browser
     *
     * @var Facebook\WebDriver\Remote\RemoteWebDriver
     */
    protected $browser;

    /**
     * WebDriver instance
     *
     * @var App\Modules\Headless\WebDriver
     */
    protected $driver;

    /**
     * Element resolver instance
     *
     * @var App\Modules\Headless\ElementResolver
     */
    protected $resolver;

    /**
     * Url to visit
     *
     * @var string
     */
    protected $url;

    /**
     * Temporary directory
     * 
     * @var string
     */
    protected $tmpDir;

    /**
     * Output directory path
     *
     * @var string
     */
    protected $outputDir;

    /**
     * Console output directory path
     *
     * @var string
     */
    protected $consoleOutput;

    /**
     * Preserve computed or returned messages
     *
     * @var array
     */
    protected $messages = [];

    /**
     * Default web driver options
     *
     * @var array
     */
    protected $defaults = [

        // disable undesired features        
        '--disable-background-networking',
        '--disable-background-timer-throttling',
        '--disable-default-apps',
        '--disable-extensions',
        '--disable-hang-monitor',
        '--disable-popup-blocking',
        '--disable-prompt-on-repost',
        '--disable-sync',
        '--disable-translate',
        '--metrics-recording-only',
        '--no-first-run',
        '--safebrowsing-disable-auto-update',

        //password settings
        '--password-store=basic',
        '--use-mock-keychain', // osX only

        // headless mode    
        '--headless',
        '--disable-gpu',
        '--incognito',
        '--font-render-hinting=none',
        '--hide-scrollbars',
        '--mute-audio',
    ];


    /**
     * Create browser instance
     * 
     * @param  string   $outputDir      directory to store browser contents
     * @param  string   $browser        browser type, default: chrome
     * @param  array    $options      	browser arguments
     */
    public function __construct( string $outputDir = null, $browser = 'chrome', array $options = [] )
    {
        // WebDriver
        $this->driver = (new WebDriver($browser, array_merge($this->defaults, $options)));

        // Facebook\WebDriver\Remote\RemoteWebDriver;
        $this->browser = $this->driver->getDriver();

        // ElementResolver
        $this->resolver = new ElementResolver($this->browser);

        // set temporary directory
        $this->tmpDir = sys_get_temp_dir();

        // default output directory
        $this->outputDir = is_dir($outputDir) ? $outputDir : $this->setOutputDir();

        // default console output directory
        $this->consoleOutput = $this->outputDir;
    }


    /**
     * Destruct browser instance
     * 
     * By destruct the browser instance the web driver remote server 
     * process as well as the webdriver itself get stopped/quit. 
     */
    public function __destruct()
    {
        $this->driver->stop();
    }


    /**
     * Static method to install Webdriver
     * 
     * In case of a successful installation, the install path will be return, else null
     * 
     * @return string|null
     */
    public static function install()
    {
        $installer = new DriverInstaller();

        return $installer->install();
    }


    /**
     * Set output directory
     *
     * @return string
     */
    public function setOutputDir()
    {
        if ( function_exists('storage_path') )
            return storage_path('app/headless');

        return $this->tmpDir;
    }


    /**
     * Browse to the given URL.
     *
     * @param  string  $url
     * @return $this
     */
    public function visit( string $url )
    {
        // If the URL does not start with http or https, https is prepended
        if ( !strpos($url, 'http://') && !strpos($url, 'https://') )
            $url = 'https://' . $url;

        $this->url = $url;

        $this->browser->navigate()->to($url);

        return $this;
    }


    /**
     * Browse to the blank page.
     *
     * @return $this
     */
    public function blank()
    {
        $this->browser->navigate()->to('about:blank');

        return $this;
    }


    /**
     * Refresh the page.
     *
     * @return $this
     */
    public function refresh()
    {
        $this->browser->navigate()->refresh();

        return $this;
    }


    /**
     * Navigate to the previous page.
     *
     * @return $this
     */
    public function back()
    {
        $this->browser->navigate()->back();

        return $this;
    }


    /**
     * Navigate to the next page.
     *
     * @return $this
     */
    public function forward()
    {
        $this->browser->navigate()->forward();

        return $this;
    }


    /**
     * Maximize the browser window.
     *
     * @return $this
     */
    public function maximize()
    {
        $this->browser->manage()->window()->maximize();

        return $this;
    }


    /**
     * Resize the browser window.
     *
     * @param  int  $width
     * @param  int  $height
     * @return $this
     */
    public function resize($width, $height)
    {
        $this->browser->manage()->window()->setSize(
            new WebDriverDimension($width, $height)
        );

        return $this;
    }


    /**
     * Make the browser window as large as the content.
     *
     * @return $this
     */
    public function fitContent()
    {
        $html = $this->browser->findElement(WebDriverBy::tagName('html'));

        if (! empty($html) ) {

            $currentSize = $html->getSize();

            $size = new WebDriverDimension($currentSize->getWidth(), $currentSize->getHeight());

            $this->browser->manage()->window()->setSize($size);
        }

        return $this;
    }


    /**
     * Disable fit on failures.
     *
     * @return $this
     */
    public function disableFitOnFailure()
    {
        $this->fitOnFailure = false;

        return $this;
    }


    /**
     * Enable fit on failures.
     *
     * @return $this
     */
    public function enableFitOnFailure()
    {
        $this->fitOnFailure = true;

        return $this;
    }


    /**
     * Move the browser window.
     *
     * @param  int  $x
     * @param  int  $y
     * @return $this
     */
    public function move($x, $y)
    {
        $this->browser->manage()->window()->setPosition(
            new WebDriverPoint($x, $y)
        );

        return $this;
    }


    /**
     * Pause for the given amount of milliseconds.
     *
     * @param  int  $milliseconds
     * @return $this
     */
    public function wait($milliseconds)
    {
        usleep($milliseconds * 1000);

        return $this;
    }


    /**
     * Close the browser.
     *
     * @return void
     */
    public function quit()
    {
        $this->browser->quit();
    }


    /**
     * Preserve computed or returned messages
     *
     * @return void
     */
    public function getMessages()
    {
        return $this->messages;
    }
}