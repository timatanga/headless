<?php

namespace Tests;

use PHPUnit\Framework\Assert as PHPUnit;
use PHPUnit\Framework\TestCase;
use timatanga\Headless\Browser;

class ElementTest extends TestCase
{

    /**
     * Test browser navigate
     *
     * @return void
     */
    public function test_element_text()
    {
        $browser = new Browser;

        $result = $browser->visit('laracasts.com')
                            ->wait(2000)
                            ->text('[href="/login"')
                            ->getMessages();

        $this->assertTrue($result[0] == 'SIGN IN');

    }

}

