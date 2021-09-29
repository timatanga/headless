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

class ElementResolver
{

    /**
     * RemoteWebDriver
     *
     * @var \Facebook\WebDriver\Remote\RemoteWebDriver
     */
    public $driver;


    /**
     * Create new instance of element resolver.
     *
     * @param  \Facebook\WebDriver\Remote\RemoteWebDriver  $driver
     * @return void
     */
    public function __construct( $driver )
    {
        $this->driver = $driver;
    }


    /**
     * Find first input or textarea element matching the given selector
     * 
     * @param string  $selector
     * @return \Facebook\WebDriver\Remote\RemoteWebElement|null
     */
    public function resolveInput( string $selector = null  )
    {
    	// selector starting with # is supposed to be an element id
        if ( str_contains($selector, '#') )
            return $this->findById($selector);

    	// field name selector is supposed to be without certain chars
        if (! preg_match('/[\.=\[\]]/', $selector) ) {
            if (!is_null($element = $this->findByName("input[name='{$selector}']")) ||
                !is_null($element = $this->findByName("textarea[name='{$selector}']")) )
            return $element;
        }

    	return $this->find("input{$selector}, textarea{$selector}");
    }   


    /**
     * Find first file input element matching the given selector
     * 
     * @param string  $selector
     * @return \Facebook\WebDriver\Remote\RemoteWebElement|null
     */
    public function resolveFile( string $selector = null  )
    {
    	// selector starting with # is supposed to be an element id
    	if ( str_contains($selector, '#') )
    		return $this->findById($selector);

    	// field name selector is supposed to be without certain chars
        if (! preg_match('/[\.=\[\]]/', $selector) )
    		return $this->findByName("input[type='file'][name='{$selector}']");

    	return $this->find("input[type='file']{$selector}");
    }  


    /**
     * Find first button element matching the given selector
     * 
     * @param string  $selector
     * @return \Facebook\WebDriver\Remote\RemoteWebElement|null
     */
    public function resolveButton( string $selector )
    {
    	// resolve button by id
    	if ( str_contains($selector, '#') )
    		return $this->findById($selector);

        // resolve button by selector
        if (! empty($element = $this->find($selector)) )
            return $element;

    	// resolve button by name
        if (! is_null($element = $this->findByName("input[type=submit][name='{$selector}']")) )
            return $element; 

        if (! is_null($element = $this->findByName("input[type=button][name='{$selector}']")) )
            return $element; 

        if (! is_null($element = $this->findByName("button[name='{$selector}']")) )
            return $element; 

        // resolve button by value
        foreach ( $this->all('input[type=submit]') as $element ) {
            if ( $element->getAttribute('value') === $selector )
                return $element;
        }

        // resolve button by text
        foreach ( $this->all('button') as $element ) {
            if ( str_contains($element->getText(), $selector) )
                return $element;
        }
    } 


    /**
     * Find first select element matching the given selector
     * 
     * @param string  $selector
     * @return \Facebook\WebDriver\Remote\RemoteWebElement|null
     */
    public function resolveSelect( string $selector  )
    {
    	// selector starting with # is supposed to be an element id
    	if ( str_contains($selector, '#') )
    		return $this->findById("select{$selector}");

    	// field name selector is supposed to be without certain chars
        if (! preg_match('/[\.=\[\]]/', $selector) )
    		return $this->findByName("select[name='{$selector}']");

    	return $this->find("select{$selector}");

    } 


    /**
     * Resolve all the options with the given value on the select field.
     *
     * @param  string  $selector
     * @param  array  $value
     * @return \Facebook\WebDriver\Remote\RemoteWebElement[]
     *
     * @throws \Exception
     */
    public function resolveSelectOptions( string $selector, array $value )
    {
        $options = $this->resolveSelection($selector)
                		->findElements(WebDriverBy::cssSelector('option:not([disabled])'));

        if ( empty($options) )
            return [];

        return array_filter($options, function ($option) use ($value) {
            return in_array($option->getAttribute('value'), $value);
        });
    }


    /**
     * Find first radio element matching the given selector
     * 
     * @param string  $selector
     * @return \Facebook\WebDriver\Remote\RemoteWebElement|null
     */
    public function resolveRadio( string $selector  )
    {
    	// selector starting with # is supposed to be an element id
    	if ( str_contains($selector, '#') )
    		return $this->findById("input{$selector}[type='radio']");

    	// field name selector is supposed to be without certain chars
        if (! preg_match('/[\.=\[\]]/', $selector) )
    		return $this->findByName("input[type='radio'][name='{$selector}']");

    	return $this->find("input[type='radio']{$selector}");
    } 


    /**
     * Resolve the element for a given radio "field" / value.
     *
     * @param  string  $selector
     * @param  string  $value
     * @return \Facebook\WebDriver\Remote\RemoteWebElement
     *
     * @throws \Exception
     * @throws \InvalidArgumentException
     */
    public function resolveRadioSelection( string $selector, $value = null )
    {
    	// selector starting with # is supposed to be an element id
    	if ( str_contains($selector, '#') )
    		return $this->findById("input{$selector}[type='radio'][value='{$value}']");

    	// field name selector is supposed to be without certain chars
        if (! preg_match('/[\.=\[\]]/', $selector) )
    		return $this->findByName("input[type='radio'][name='{$selector}'][value='{$value}']");

    	return $this->find("input[type='radio']{$selector}[value='{$value}']");
    }


    /**
     * Find first checkbox element matching the given selector
     * 
     * @param string  $selector
     * @param  string  $value
     * @return \Facebook\WebDriver\Remote\RemoteWebElement|null
     */
    public function resolveCheckbox( string $selector, $value = null )
    {
    	// selector starting with # is supposed to be an element id
    	if ( str_contains($selector, '#') ) {
	    	$query = is_null($value) ? 
	    		"input{$selector}[type='checkbox']" : 
	    		"input{$selector}[type='checkbox'][value='{$value}']";

    		return $this->findById($query);
    	}

    	// field name selector is supposed to be without certain chars
        if (! preg_match('/[\.=\[\]]/', $selector)) {
	    	$query = is_null($value) ? 
	    		"input{$selector}[type='checkbox'][name='{$selector}']" : 
	    		"input{$selector}[type='checkbox'][name='{$selector}'][value='{$value}']";

    		return $this->findByName($query);
        }

    	$query = is_null($value) ? 
			"input[type='checkbox']{$selector}" :
			"input[type='checkbox']{$selector}[value='{$value}']";

    	return $this->find($query);
    } 


    /**
     * Find first link element matching the given selector
     * 
     * @param string  $selector
     * @return \Facebook\WebDriver\Remote\RemoteWebElement|null
     */
    public function resolveLink( string $selector  )
    {
    	// selector starting with # is supposed to be an element id
    	if ( str_contains($selector, '#') )
    		return $this->findById("a{$selector}");

    	// field name selector is supposed to be without certain chars
        if (! preg_match('/[\.=\[\]]/', $selector) )
    		return $this->findByName("a[name='{$selector}']");

    	return $this->find("a{$selector}");
    } 


    /**
     * Find first element matching the given selector
     * 
     * @param string  $selector
     * @return \Facebook\WebDriver\Remote\RemoteWebElement|null
     */
    public function all( string $selector )
    {
    	try {
	    	return $this->driver->findElements(WebDriverBy::cssSelector($selector));

        } catch (Exception $e) { }

        return [];
    } 


    /**
     * Find first element matching the given selector
     * 
     * @param string  $selector
     * @return \Facebook\WebDriver\Remote\RemoteWebElement|null
     */
    public function find( string $selector )
    {
    	// find by xpath
    	if ( str_contains($selector, '@') )
	    	return $this->driver->findElement(WebDriverBy::xpath($selector));

    	return $this->driver->findElement(WebDriverBy::cssSelector($selector));
    } 


    /**
     * Find element by its id as selector
     * 
     * @param string  $selector
     * @return \Facebook\WebDriver\Remote\RemoteWebElement|null
     */
    public function findById( string $selector  )
    {
        if ( preg_match('/^#[\w\-:]+$/', $selector) )
        	return $this->driver->findElement(WebDriverBy::id(substr($selector, 1)));

       	return null;
    } 


    /**
     * Find element by its name
     * 
     * @param string  $selector
     * @return \Facebook\WebDriver\Remote\RemoteWebElement|null
     */
    public function findByName( string $selector  )
    {
    	return $this->driver->findElement(WebDriverBy::name($selector));
    } 

}