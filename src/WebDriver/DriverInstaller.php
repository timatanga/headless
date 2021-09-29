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

use timatanga\Headless\Exceptions\BinaryNotFoundException;
use timatanga\Headless\Exceptions\ProcessFailedException;
use timatanga\Headless\Exceptions\WebDriverException;
use timatanga\Headless\WebDriver\DetectChrome;
use timatanga\Headless\WebDriver\Process;

class DriverInstaller
{
    /**
     * Detected Operating System
     *
     * @var string
     */
    protected $os; 

    /**
     * Detected Platform
     *
     * @var string
     */
    protected $platform; 

    /**
     * Detected Chrome Binary Path
     *
     * @var string
     */
    protected $chromePath; 

    /**
     * Detected Chrome Version
     *
     * @var string
     */
    protected $chromeVersion; 

    /**
     * Stream Context Options
     *
     * @var array
     */
    protected $streamOptions = []; 

    /**
     * URL to the latest release version for a major Chrome version.
     *
     * @var string
     */
    protected $versionUrl = 'https://chromedriver.storage.googleapis.com/LATEST_RELEASE_%d';

    /**
     * URL to the ChromeDriver download.
     *
     * @var string
     */
    protected $downloadUrl = 'https://chromedriver.storage.googleapis.com/%s/chromedriver_%s.zip';

    /**
     * Path to the web driver directory.
     *
     * @var string
     */
    protected $driverDir = __DIR__.'/../../bin/';


    /**
     * Class constructor
     * 
     * @param  string   $path           path to server binary
     */
    public function __construct()
    {  
        $this->os = DetectChrome::os();

        $this->platform = DetectChrome::platform();

        $this->chromePath = "'" . DetectChrome::detect() . "'";

        $this->chromeVersion = $this->detectChromeVersion();
    }


    /**
     * Install chromedriver
     *
     * In case of a successful installation the binary directory is returns, else null
     * 
     * @param array  $options
     * @return string||null
     */
    public function install( array $options = [] )
    {
        // extract stream context options
        $this->streamOptions = $this->setStreamOptions($options);

        // get latest webdriver version according to chrome version
        if (! $latestVersion = $this->getLastestVersion($this->chromeVersion) )
            throw new WebDriverException('Failed to resolve latest webdriver version');

        // download webdriver archive
        if (! $archive = $this->download($latestVersion, $this->platform) )
            throw new WebDriverException('Failed to download webdriver');

        // extract archive
        if (! $binary = $this->extract($archive) )
            throw new WebDriverException('Failed to extract webdriver archive');

        // rename binary
        $this->rename($binary);

        if ( is_file( $this->driverDir.'chromedriver-'.$this->os) )
            return 'Successfully installed webdriver at: ' . $this->driverDir.'chromedriver-'.$this->os;

        return null;
    }


    /**
     * Detect installed chrome/chromium version
     *
     * @return int|bool
     */
    public function detect()
    {
        return $this->chromeVersion;
    }


    /**
     * Extract stream context options
     *
     * @param array $options
     * @return array
     */
    protected function setStreamOptions( array $options = [] )
    {
        $options = [];

        if ( isset($options['proxy']) ) 
            $options['http'] = ['proxy' => $options['proxy'], 'request_fulluri' => true];

        if ( isset($options['ssl-no-verify']) )
            $options['ssl'] = ['verify_peer' => false, 'verify_peer_name' => false];

        return $options;
    }


    /**
     * Detect the installed Chrome / Chromium major version.
     *
     * @return int|bool
     */
    protected function detectChromeVersion()
    {
        $process = new Process($this->chromePath, ['--version']);

        $version = $process->execute();

        preg_match('/(\d+)(\.\d+){3}/', $version, $matches);

        if (! isset($matches[1]) )
            return null;

        return $matches[1];
    }


    /**
     * Get the latest ChromeDriver version.
     *
     * @param string $chromeVersion
     * @return string
     */
    protected function getLastestVersion( string $chromeVersion )
    {
        $url = sprintf($this->versionUrl, $chromeVersion);

        $streamContext = stream_context_create($this->streamOptions);

        return trim(file_get_contents($url, false, $streamContext));
    }


    /**
     * Download the ChromeDriver archive.
     *
     * @param  string  $version
     * @param  string  $platform
     * @return string
     */
    protected function download( $version, $platform )
    {
        $url = sprintf($this->downloadUrl, $version, $platform);

        $streamContext = stream_context_create($this->streamOptions);

        $archive = file_get_contents($url, false, $streamContext);

        file_put_contents($location = $this->driverDir.'chromedriver.zip', $archive);

        return $location;
    }


    /**
     * Extract the ChromeDriver binary from the archive and delete the archive.
     *
     * @param  string  $archive
     * @return string
     */
    protected function extract( string $archive )
    {
        $zip = new \ZipArchive;

        $zip->open($archive);

        $zip->extractTo($this->driverDir);

        $binary = $zip->getNameIndex(0);

        $zip->close();

        unlink($archive);

        return $binary;
    }


    /**
     * Rename the ChromeDriver binary and make it executable.
     *
     * @param  string  $binary
     * @return void
     */
    protected function rename( string $binary )
    {
        $driverName = str_replace('chromedriver', 'chromedriver-'.$this->os, $binary);

        rename($this->driverDir.$binary, $this->driverDir.$driverName);

        chmod($this->driverDir.$driverName, 0755);
    }

}

