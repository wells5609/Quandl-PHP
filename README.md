Quandl-PHP
==========

PHP library for the Quandl API. 

Very much an alpha version

###Basic Usage
```php
// Include the main Quandl class file
require '/path/to/Quandl.php';

// Register an autoloader for other library classes
Quandl::registerAutoloader();

// Set your API key (the one below is fake)
Quandl::setAuthToken('ABCDEFGH12345678');

// Get daily EOD quotes for Apple
$aapl = Quandl::request('WIKI/AAPL');

// Find the close on August 5, 2013
// use any PHP-recognized date format
if ($aug_5_13 = $aapl->getDataFrom('Aug 5, 2013')) {
  echo '<p>On August 5, 2013, the price of AAPL at close was ' . $aug_5_13['Close'] . '</p>';
}

// Get the Quandl.com page link
$link = $aapl->get('display_url');

echo '<a href="'.$link.'">View AAPL on Quandl.com</a>';
```
This should output something like:

On august 5, 2013, the price of AAPL at close was 469.45.
[View AAPL on Quandl.com](http://www.quandl.com/WIKI/AAPL)


Or, to get all the returned data:
```php
$data = $aapl->get('data');
```
