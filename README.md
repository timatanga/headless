# Headless
This package provides a fluent interface for browser automation. It is heavily influenced by Laravel Dusk so all credits go to Taylor Otwell and his amazing team for there incredible work and inspiration!
Instead of installing JDK or Selenium, this package requires and uses a standalone ChromeDriver.


## Installation
To get started, you should install Google Chrome on your system. Google Chrome will be managed in headless mode via this package through the ChromeDriver.

Please make use of composer to install this package

	composer require dbisapps/headless


Instead of relying on Laravel's artisan command line interface to install the ChromeDriver this package supports a static method to get the driver installed within the package directory:

	use dbizapps\Headless\Browser;

	$result = Browser::install();

The required ChromeDriver version is detected based on the major version of the installed Chrome/Chromium browser on your machine.


## Browser Navigation
The Headless packages provides an easy to use, fluent interface to concat browser operations. 
Navigation operations all return the browser instance itself.

A good starting point is to visit a page by:

	use dbizapps\Headless\Browser;

    $browser = new Browser;

    $browser->visit('laravel.com');


Other available navigation operations are:

	// browse to the blank page.
    $browser->blank();

    // refresh the page
    $browser->refresh();

    // navigate back
    $browser->back();

    // navigate back
    $browser->forward();


## Browser Sizing & Moving
Even in headless mode with non-visible operations, it is sometimes useful for further operations to adjust the browser size

    // maximize browser window
    $browser->maximize();

    // resize browser window
    $browser->resize($width, $height);

	// make the browser window as large as the content.
    $browser->fitContent();

    // Move the browser window.
    $browser->move($x, $y);



## Page
Just visiting a page isn't that useful. A useful browser automation needs to interact with page elements.

Interacting with page form elements is enabled after visiting a page

    // Click on link with the given selector, e.g. #submit
    $browser->click($selector);

    // Press on button with the given selector
    $browser->press($selector);

    // Get (just passing the selector) or set value (passing selector and value) of element with the given selector. 
    $browser->value($selector, $value);

    // Get text of element with the given selector
    $browser->text($selector);

	// Clear selected element and type value
    $browser->type($selector, $value);

	// Append value to element with given selector
    $browser->append($selector, $value);

    // Clear the given field
    $browser->clear($selector);

	// Select the given value of element with given selector
    $browser->select($selector, $value);

	// Select radio option
    $browser->radio($selector, $value);

	// Check the given checkbox.
    $browser->check($selector, $value);

	// Uncheck the given checkbox.
    $browser->uncheck($selector, $value);


Dialog interactions are supported as well

	// Accept a JavaScript dialog.
    $browser->acceptDialog();

	// DismissDialog a JavaScript dialog.
    $browser->dismissDialog();

	// Type the given value in an open JavaScript prompt dialog.
    $browser->typeInDialog($value);


## Execute Scripts
If you need to extend existing scripts with custom javascripts just use:

	// Execute JavaScript within the browser.
    $browser->script($script);


## Assertions
For automated browser interactions you may want to assert that a specific content is on the page.  

	// Assert that the page title matches the given text.
    $browser->assertTitle($title);

	// Assert that the page title contains the given text.
    $browser->assertTitleContains($text);

	// Assert that the given text is present on the page.
    $browser->assertSee($text);

	// Assert that the given text is not present on the page.
    $browser->assertDontSee($text);

	// Assert that the given text is present within the selector.
    $browser->assertSeeIn($selector, $value);

	// Assert that the given text is not present within the selector.
    $browser->assertDontSeeIn($selector, $value);

	// Assert that the given link is present on the page.
    $browser->assertSeeLink($link);

	// Assert that the given link is not present on the page.
    $browser->assertDontSeeLink($link);

	// Assert that the given input field does not have the given value.
    $browser->assertInputValue($field, $value);

	// Assert that the given input field does not have the given value.
    $browser->assertInputValueIsNot($field, $value);

	// Assert that the given checkbox is checked.
    $browser->assertChecked($field, $value);

	// Assert that the given checkbox is not checked.
    $browser->assertNotChecked($field, $value);

	// Assert that the given radio field is selected.
    $browser->assertRadioSelected($field, $value);

	// Assert that the given radio field is not selected.
    $browser->assertRadioNotSelected($field, $value);

	// Assert that the given dropdown has the given value selected.
    $browser->assertSelected($field, $value);    

	// Assert that the given dropdown does not have the given value selected.
    $browser->assertNotSelected($field, $value);   

    // Assert that the given array of values are available to be selected.
    $browser->assertSelectHasOptions($field, $values); 

    // Assert that the given array of values are not available to be selected.
    $browser->assertSelectMissingOptions($field, $values); 

... and many more. Please refer to the SupportAssertions Trait for details.



## Get Messages, Content or Outputs
Headless browser operations require feedback to be useful at any kind.
From computed messages, console logs to page source or page caputures are numerous browser operations available:

	// Preserve computed or returned messages
	$arr = $browser->getMessages()

	// set html source into browser
	$page = $browser->setHtml($html)  

	// returns html page dump 
	$page = $browser->getPage()

	// dump html page to disk
	$path = $browser->savePage($filename)

	// takes a screen capture, stores as image
	$path = $browser->screenshot($filename)

	// takes a screen (visible screen) capture, stores as image
	$path = $browser->screenshot($filename)

	// takes a page (viewport) capture, stores as image
	$path = $browser->pageshot($filename)

	// dumps the console log to disk
	$path = $browser->consoleLog($filename)



