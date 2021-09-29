<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use timatanga\Headless\WebDriver\DetectChrome;

class DectectTest extends TestCase
{

    public function test_detect_chrome_version()
    {
        $path = DetectChrome::detect();

        if ( PHP_OS_FAMILY == 'Windows' )
            $this->assertTrue( strpos($path, 'chrome.exe') !== false );

        if ( PHP_OS_FAMILY == 'Darwin' )
            $this->assertTrue( $path == '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome' );

        if ( PHP_OS_FAMILY == 'Linux' || PHP_OS_FAMILY == 'Solaris' || PHP_OS_FAMILY == 'BSD')
            $this->assertTrue( strpos($path, '/usr/bin') !== false );

    }


}

