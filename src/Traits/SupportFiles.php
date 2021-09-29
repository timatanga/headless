<?php

/*
 * This file is part of the Headless package.
 *
 * (c) Mark Fluehmann mark.fluehmann@gmail.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace timatanga\Headless\Traits;

use timatanga\Headless\Exceptions\BrowserException;
use timatanga\Headless\Exceptions\WebDriverException;
use timatanga\Headless\WebDriver\Viewport;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverDimension;
use Facebook\WebDriver\Remote\WebDriverBrowserType;

/**
 * This files belongs to the Headless Package
 * 
 * It supports browser operations in regard to file based outputs, like:
 * - consoleLog ( $filename )       stores the console log to filename
 * - setHtml ( $html )              set html source
 * - getPage                        returns html page dump
 * - savePage( $filename )          dump html page to disk
 * - screenshot( $filename )        takes a screen capture, stores as image
 * - pageshot( $filename )          takes a page capture, stores as image 
 */
trait SupportFiles
{

    /**
     * Html source
     *
     * @var string
     */
    protected $html = null;


    /**
     * Modes to render and handle pdf
     *
     * @var string
     */
    protected $modes = [
        'stream',       // return the document as a string
        'inline',       // send document inline to the browser
        'download',     // send document to the browser and force a file download
        'save',         // save document to disk with the name given by $filename
    ];


    /**
     * The browsers that support retrieving logs.
     *
     * @var array
     */
    protected $supportsRemoteLogs = [
        WebDriverBrowserType::CHROME,
        WebDriverBrowserType::PHANTOMJS,
    ];


    /**
     * Store the console output with the given name.
     *
     * @param  string  $filename
     * @return $this
     */
    public function consoleLog( string $filename )
    {
        // build renndered file location
        $location = $this->buildLocation($filename, 'txt');

        if (! in_array($this->browser->getCapabilities()->getBrowserName(), $this->supportsRemoteLogs) )
            return $this;

        $console = $this->browser->manage()->getLog('browser');

        if (! empty($console))
            file_put_contents( $location, json_encode($console, JSON_PRETTY_PRINT) );

        return $this;
    }


    /**
     * Set html source
     *
     * @param  string  $html       valid html content
     * @return $this
     */
    public function setHtml( string $html )
    {
        $this->html = utf8_encode($html);

        return $this;
    }


    /**
     * Dump page content
     *
     * @return string
     */
    public function getPage()
    {
        if (! is_null($this->html) )
            return $this->html;

        if (! is_null($this->url) )
            return $this->browser->getPageSource();
    }


    /**
     * Save Page to local disk with the given filename
     * Page can either be the stored html source or a visited url
     *
     * @param  string  $filename       save page as filename 
     * @return string 
     */
    public function savePage( string $filename )
    {
        // build renndered file location
        $location = $this->buildLocation($filename, 'html');

        if (! is_null($this->html) )
            $source = $this->html;

        if (! is_null($this->url) )
            $source = $this->browser->getPageSource();

        if (! empty($source) )
            file_put_contents( $location, $source );

        return $location;
    }


    /**
     * Take a screenshot and store it with the given name.
     *
     * @param  string  $filename
     * @return $this
     */
    public function screenshot( $filename )
    {
        // build renndered file location
        $location = $this->buildLocation($filename, 'png');

        $this->browser->takeScreenshot( $location );

        return $location;
    }


    /**
     * Take a capture of full page and store it with the given name.
     *
     * @param  string  $filename
     * @return $this
     */
    public function pageshot( $filename )
    {
        // build renndered file location
        $location = $this->buildLocation($filename, 'png');

        $this->setViewportToPage();

        $this->browser->takeScreenshot( $location );

        return $location;
    }


    /**
     * Set viewport to fullpage size
     *
     * @return Viewport
     */
    private function setViewportToPage()
    {
        $html = $this->browser->findElement(WebDriverBy::tagName('html'));

        $htmlSize = $html->getSize();

        $scrollHeight = $this->browser->executeScript("return document.querySelector('html').scrollHeight");
        $scrollWidth = $this->browser->executeScript("return document.querySelector('html').scrollWidth");

        $width = $scrollWidth > $htmlSize->getWidth() ? $scrollWidth : $htmlSize->getWidth();
        $height = $scrollHeight > $htmlSize->getHeight() ? $scrollHeight : $htmlSize->getHeight();

        $size = new WebDriverDimension($width, $height);

        $this->browser->manage()->window()->setSize($size);
    }


   /**
    * Build unique filename with the given extension
    * 
    * @param  {string} $extension (pdf / html / jpg )
    * @return {String} $filename
    */
    private function uniqueName( string $extension )
    {
        return md5(date('Y-m-d H:i:s:u')) . '.' . $extension;
    }


    /**
     * Build location for rendered file
     * 
     * @param  string       $filename
     * @return string       $extension
     * @throws exception
     */
    private function buildLocation( string $filename, string $extension = null )
    {
        if (! isset($filename) )
            $filename = $this->uniqueName($extension);

        if ( !is_null($extension) && !strpos($filename, $extension) )
            $filename .= '.'. $extension;

        $location = $this->outputDir . DIRECTORY_SEPARATOR . $filename;

        $directoryPath = dirname($location);

        if (! is_dir($directoryPath))
            mkdir($directoryPath, 0777, true);

        return $location;
    }


    /**
     * Clean directory, except given file
     * 
     * @param string  $filename   file not to delete
     * @param bool    $include    inlude given file for deletion
     */ 
    private function cleanDirectory( $filename = null, $include = false )
    {
        // set directory
        $directory = dirname($filename);

        // List of name of files inside specified folder
        $files = glob($directory.'/*'); 

        // Deleting all the files in the list
        foreach($files as $file) {
           
            if( is_file($file) && ( $include || $file !== $filename ) )
            
                // Delete the given file
                unlink($file); 
        }
    }
    
}