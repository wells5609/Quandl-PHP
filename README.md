Quandl-PHP
==========

PHP library for the Quandl API. 

Very much in alpha

###Basic Usage
```php
// Include the main Quandl class file
require '/path/to/Quandl.php';

// Register an autoloader for other library classes
Quandl::registerAutoloader();

// Set your API key (the one below is fake)
Quandl::setAuthToken('ABCDEFGH12345678');

// Get EOD quotes for Apple
$aapl = Quandl::request('WIKI/AAPL');
// or new \Quandl\Request('WIKI/AAPL');

// Get one month of data
$aapl->startDate(date('Y-m-d', strtotime('4 weeks ago')));

$aapl->send();

// Find the close on August 5, 2013
// use any PHP-recognized date format
if ($aug_5 = $aapl->response->getDataFrom('Aug 5, 2013')) {
  echo 'On August 5, 2013, the price of AAPL at close was ' . $aug_5['Close'] . ' ';
}

// Get the Quandl.com page link
$link = $aapl->response->get('display_url');

echo '<a href="'.$link.'">View AAPL on Quandl.com</a>';
```
This should output something like:

On August 5, 2013, the price of AAPL at close was 469.45. [View AAPL on Quandl.com](http://www.quandl.com/WIKI/AAPL)


Get all the returned data:
```php
$data = $aapl->get('data');
```
