<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use timatanga\Headless\Browser;

class FileTest extends TestCase
{
    /**
     * Test get page source
     *
     * @return void
     */
    public function test_get_source_text()
    {
        $browser = new Browser;

        $result = $browser->visit('laracasts.com')
                            ->wait(2000)
                            ->getPage();

        $this->assertTrue(str_contains($result, 'Laracasts'));
    }


    /**
     * Test save page source
     *
     * @return void
     */
    public function test_save_source_text()
    {
        $browser = new Browser(__DIR__.'/temp');

        $result = $browser->visit('laracasts.com')
                            ->wait(2000)
                            ->savePage('phpunit');

        $this->assertTrue(file_exists($result));

        unlink($result);
    }


    /**
     * Test get console
     *
     * @return void
     */
    public function test_get_console()
    {
        $browser = new Browser(__DIR__.'/temp');

        // only warn, error logs are fetched
        $browser->visit('laracasts.com')
                ->wait(1000)
                ->script('return console.warn("phpunit test")')
                ->consoleLog('phpunit_console');

        $this->assertTrue(file_exists(__DIR__.'/temp/' . 'phpunit_console.txt'));

        $content = file_get_contents(__DIR__.'/temp/' . 'phpunit_console.txt');

        $this->assertTrue(str_contains($content, 'phpunit test'));

        unlink($result);

    }
}

