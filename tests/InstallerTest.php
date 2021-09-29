<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use timatanga\Headless\Browser;
use timatanga\Headless\WebDriver\DriverInstaller;

class InstallerTest extends TestCase
{

    public function test_driver_installer()
    {
        $installer = new DriverInstaller();

        $result = $installer->install();

        if ( PHP_OS_FAMILY == 'Windows' )
            $this->assertTrue( is_file(__DIR__.'../bin/chromedriver-winows') );

        if ( PHP_OS_FAMILY == 'Linux' )
            $this->assertTrue( is_file(__DIR__.'../bin/chromedriver-linux') );

        if ( PHP_OS == 'Darwin' && php_uname('m') == 'arm64' )
            $this->assertTrue( is_file(__DIR__.'../bin/chromedriver-darwin') );

        if ( PHP_OS == 'Darwin' && php_uname('m') == 'x86_64' )
            $this->assertTrue( is_file(__DIR__.'/../bin/chromedriver-darwin') );
    }


    public function test_installation_thru_browser()
    {
        $result = Browser::install();

        $this->assertTrue( !is_null($result) );
    }

}

