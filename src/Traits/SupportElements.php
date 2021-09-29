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

use timatanga\Headless\ElementResolver;
use Facebook\WebDriver\Interactions\WebDriverActions;
use Facebook\WebDriver\Remote\LocalFileDetector;
use Facebook\WebDriver\Remote\RemoteWebDriver;

trait SupportElements
{

    /**
     * Check if element is visible 
     *
     * @param  string  $selector
     * @return $this
     */
    public function isVisible( string $selector )
    {
        $result = $this->browser->executeScript("return document.querySelector('{$selector}').offsetParent != null");

        $this->messages[] = $result;

        return $this;
    }
 
    /**
     * Click on link with the given selector
     * 
     * @param string  $selector
     * @return $this
     */
    public function click( string $selector ) 
    {
        $this->resolver->resolveLink($selector)->click();

        return $this;
    }


    /**
     * Press on button with the given selector
     * 
     * @param string  $selector
     * @return $this
     */
    public function press( string $selector ) 
    {
        $this->resolver->resolveButton($selector)->click();

        return $this;
    }


    /**
     * Get or set value of element with the given selector
     * 
     * @param string  $selector
     * @param string|null  $value
     * @return $this
     */
    public function value( string $selector, $value = null ) 
    {
        if ( is_null($value) )
            $this->messages[] = $this->resolver->find($selector)->getAttribute('value');

        if (! is_null($value) )
            $this->messages[] = $this->browser->executeScript(
                'document.querySelector('.json_encode($selector).').value = '.json_encode($value).';'
            );

        return $this;
    }


    /**
     * Get text of element with the given selector
     * 
     * @param string  $selector
     * @param string|null  $value
     * @return $this
     */
    public function text( string $selector ) 
    {
        $this->messages[] = $this->resolver->find($selector)->getText();

        return $this;
    }


    /**
     * Get or set attribute of element with the given selector
     * 
     * @param string  $selector
     * @param string  $attribute
     * @param string|null  $value
     * @return $this
     */
    public function attribute( string $selector, $attribute, $value = null ) 
    {
        if (! is_null($value) )
            $this->messages[] = $this->resolver->find($selector)->getAttribute($attribute);

        if ( is_null($value) )
            $this->messages[] = $this->browser->executeScript(
                'document.querySelector('.json_encode($selector).').setAttribute('.json_encode($attribute).','.json_encode($value).');'
            );

        return $this;
    }


    /**
     * Clear the given field
     *
     * @param  string  $selector
     * @return $this
     */
    public function clear( string $selector)
    {
        $this->resolver->resolveInput($selector)->clear();

        return $this;
    }


    /**
     * Clear selected element and type value
     *
     * @param  string  $field
     * @param  string  $value
     * @return $this
     */
    public function type( string $selector, $value )
    {
        $this->resolver->resolveInput($selector)->clear()->sendKeys($value);

        return $this;
    }


    /**
     * Append value to element with given selector
     *
     * @param  string  $field
     * @param  string  $value
     * @return $this
     */
    public function append( string $selector, $value )
    {
        $this->resolver->resolveInput($selector)->sendKeys($value);

        return $this;
    }


   /**
     * Select the given value of element with given selector
     *
     * @param  string  $selector
     * @param  string|array|null  $value
     * @return $this
     */
    public function select( $selector, $value = null )
    {
        // find select element
        $element = $this->resolver->resolveSelect($selector);

        // get select options
        $options = $element->findElements(WebDriverBy::cssSelector('option:not([disabled])'));

        // evalute multi select
        $isMultiple = false;
        if ($isMultiple = $select->isMultiple())
            $select->deselectAll();

        // cast value
        $value = is_bool($value) ? $value : ( $value ? 1 : 0 );

        // select one or multiple options
        foreach ($options as $option) {
            if ( in_array((string) $option->getAttribute('value'), $value) ) {
                $option->click();

                if (! $isMultiple ) break;
            }
        }

        return $this;
    }


    /**
     * Select radio option
     * 
     * @param  string  $selector
     * @param  string  $value
     * @return $this
     */
    public function radio( string $selector, $value = null ) 
    {
        $this->resolver->resolveRadioSelection($selector, $value)->click();

        return $this;
    }


    /**
     * Check the given checkbox.
     *
     * @param  string  $selector
     * @param  string  $value
     * @return $this
     */
    public function check( string $selector, $value = null)
    {
        $element = $this->resolver->resolveCheckbox($selector, $value);

        if (! $element->isSelected() )
            $element->click();

        return $this;
    }


    /**
     * Uncheck the given checkbox.
     *
     * @param  string  $selector
     * @param  string  $value
     * @return $this
     */
    public function uncheck( string $selector, $value = null)
    {
        $element = $this->resolver->resolveCheckbox($selector, $value);

        if ( $element->isSelected() )
            $element->click();

        return $this;
    }


    /**
     * Attach the given file to the field.
     *
     * @param  string  $selector
     * @param  string  $path
     * @return $this
     */
    public function attach( string $selector, string $path )
    {
        $element = $this->resolver->resolveFile($selector);

        $element->setFileDetector(new LocalFileDetector)->sendKeys($path);

        return $this;
    }


   /**
     * Accept a JavaScript dialog.
     *
     * @return $this
     */
    public function acceptDialog()
    {
        $this->browser->switchTo()->alert()->accept();

        return $this;
    }


    /**
     * Dismiss a JavaScript dialog.
     *
     * @return $this
     */
    public function dismissDialog()
    {
        $this->browser->switchTo()->alert()->dismiss();

        return $this;
    }


    /**
     * Type the given value in an open JavaScript prompt dialog.
     *
     * @param  string  $value
     * @return $this
     */
    public function typeInDialog($value)
    {
        $this->browser->switchTo()->alert()->sendKeys($value);

        return $this;
    }


   /**
     * Drag an element to another element using selectors.
     *
     * @param  string  $from
     * @param  string  $to
     * @return $this
     */
    public function drag( string $from, string $to)
    {
        (new WebDriverActions($this->driver))->dragAndDrop(
            $this->resolver->find($from), $this->resolver->find($to)
        )->perform();

        return $this;
    }


    /**
     * Drag an element up.
     *
     * @param  string  $selector
     * @param  int  $offset
     * @return $this
     */
    public function dragUp( string $selector, int $offset)
    {
        return $this->dragOffset($selector, 0, -$offset);
    }


    /**
     * Drag an element down.
     *
     * @param  string  $selector
     * @param  int  $offset
     * @return $this
     */
    public function dragDown( string $selector, int $offset)
    {
        return $this->dragOffset($selector, 0, $offset);
    }


    /**
     * Drag an element to the left.
     *
     * @param  string  $selector
     * @param  int  $offset
     * @return $this
     */
    public function dragLeft( string $selector, int $offset)
    {
        return $this->dragOffset($selector, -$offset, 0);
    }


    /**
     * Drag an element to the right.
     *
     * @param  string  $selector
     * @param  int  $offset
     * @return $this
     */
    public function dragRight( string $selector, int $offset)
    {
        return $this->dragOffset($selector, $offset, 0);
    }


    /**
     * Drag an element by the given offset.
     *
     * @param  string  $selector
     * @param  int  $x
     * @param  int  $y
     * @return $this
     */
    public function dragOffset( string $selector, $x = 0, $y = 0)
    {
        (new WebDriverActions($this->driver))->dragAndDropBy(
            $this->resolver->find($selector), $x, $y
        )->perform();

        return $this;
    }

}