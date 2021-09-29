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

trait SupportScripts
{

    /**
     * Execute JavaScript within the browser.
     * 
     * @param string|array  $scripts
     * @return $this
     */
    public function script( $scripts ) 
    {
        // cast scripts into array
        $scripts = is_array($scripts) ? $scripts : [$scripts];

        foreach ($scripts as $script) {
            $this->messages[] = $this->browser->executeScript($script);
        }

        return $this;
    }

}