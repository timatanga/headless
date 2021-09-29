<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use timatanga\Headless\Browser;

class BrowserTest extends TestCase
{

    /**
     * Test browser navigate
     *
     * @return void
     */
    public function test_browser_navigate()
    {
        $browser = new Browser();

        $result = $browser->visit('laracasts.com')
                          ->getMessages();

        $this->assertTrue(empty($result));
    }

}

