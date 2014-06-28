Quandl-PHP
==========

PHP library for the Quandl API. Very much in alpha

###Requirements
 * PHP 5.3+
 * `allow_url_fopen` ini directive set to true


###Basic Usage
```php
// Include the main Quandl class file
require '/path/to/Quandl.php';

// Register an autoloader for other library classes
Quandl::registerAutoloader();

// Set your API key (optional but recommended)
Quandl::setAuthToken('ABCDEFGH12345678');

// Getting EOD quotes for Apple
$aapl = Quandl::request('WIKI/AAPL');
// or new \Quandl\Request('WIKI/AAPL');

// Get all observations from 2011
$aapl->startDate('2011-01-01');
$aapl->endDate('2011-12-31');

// Send the request
$aapl->send();

if ($aapl->isError()) {
  echo "Something went wrong.";
  exit(-1);
}

// Find the closing price on August 5, 2011
if ($aug5 = $aapl->response->getDataFrom('2011-08-05')) {
  echo "On August 5th 2011, the closing price of AAPL was ".$aug5['Close'].". ";
}

// Show the Quandl.com page link
echo '<a href="'.$aapl->response->get('display_url').'">View AAPL on Quandl.com</a>';
```
This should output:

On August 5, 2011, the closing price of AAPL was 373.62. [View AAPL on Quandl.com](http://www.quandl.com/WIKI/AAPL)

Get all the returned data:
```php
$data = $aapl->get('data');
```
