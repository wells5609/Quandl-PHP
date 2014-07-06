<?php

namespace Quandl;

/**
 * Represents a single Quandl API request.
 */
class Request {
	
	/**
	 * @var Quandl\Url
	 */
	protected $url;
	
	/**
	 * @var Quandl\Response
	 */
	protected $response;
	
	/**
	 * Creates a URL for the given Quandl code.
	 * 
	 * @param string $quandl_code
	 */
	public function __construct($quandl_code) {
		$this->url = new Url($quandl_code);
	}
	
	/**
	 * Magic getter
	 * @param $var Property name
	 * @return mixed
	 */
	public function __get($var) {
		return isset($this->$var) ? $this->$var : null;
	}
	
	/**
	 * Returns the Quandl code.
	 * 
	 * @return string
	 */
	public function getQuandlCode() {
		return $this->url->getQuandlCode();
	}
	
	/**
	 * Returns the request's Quandl\Response.
	 * 
	 * @return Quandl_Response
	 */
	public function getResponse() {
		return isset($this->response) ? $this->response : null;
	}
	
	/**
	 * Sends the request (if no cached response exists).
	 * 
	 * @param int|null $cache_ttl Time in seconds to cache the response.
	 * @return $this
	 */
	public function send($cache_ttl = null) {
		
		$qcode = $this->url->getQuandlCode();
		$manips = $this->url->getManipulations();
		
		if (! $response = Quandl::getCachedResponse($qcode, $manips, $cache_ttl)) {
			
			$response = file_get_contents($this->url);
			
			if (empty($response)) {
				return null;
			}
			
			switch($this->url->getFormat()) {
				
				case '.json':
					$response = Response::createFromJson($response);
					break;
				
				case '.csv':
					$response = Response::createFromCsv($response);
					break;
				
				case '.xml':
					$response = Response::createFromXml($response);
					break;
				
				default:
					break;
			}
			
			// @TODO dirty hack
			if ('WIKI' === $this->url->getSourceCode() && $response instanceof Response) {
				$response = $response->upgradeObject('Quandl\\Response\\Stock');
			}
			
			Quandl::cacheResponse($qcode, $response, $manips, $cache_ttl);
		}
		
		$this->response = $response;
		
		return $this;
	}
	
	/**
	 * Set the start date for the returned time series.
	 * 
	 * @param string $Ymd Date in format "2001-12-31"
	 * @return $this
	 */
	public function startDate($Ymd) {
		$this->url->manipulate('trim_start', $Ymd);
		return $this;
	}
	
	/**
	 * Set the ending date for the returned time series.
	 * 
	 * @param string $Ymd Date in format "2011-12-31"
	 * @return $this
	 */
	public function endDate($Ymd) {
		$this->url->manipulate('trim_end', $Ymd);
		return $this;
	}
	
	/**
	 * Set the sort order of the results.
	 * 
	 * @param string $order One of "asc" or "desc", or their corresponding PHP constants.
	 */
	public function sortOrder($order) {
		if (SORT_ASC === $order) {
			$order = 'asc';
		} else if (SORT_DESC === $order) {
			$order = 'desc';
		}
		$this->url->manipulate('sort_order', $order);
		return $this;
	}
	
	/**
	 * Set the number of rows to return.
	 * 
	 * @param int $num
	 * @return $this
	 */
	public function rows($num) {
		$this->url->manipulate('rows', $num);
		return $this;
	}
	
	/**
	 * Set the column index to return.
	 * 
	 * @param int $index
	 * @return $this
	 */
	public function column($index) {
		$this->url->manipulate('column', $index);
		return $this;
	}
	
	/**
	 * Set the observation frequency.
	 * 
	 * One of: 'none', 'daily', 'weekly', 'monthly', 'quarterly', or 'annual'.
	 * Default is 'daily' (I believe).
	 * 
	 * @param string $freq
	 * @return $this
	 */
	public function frequency($freq) {
		$this->url->manipulate('collapse', $freq);
		return $this;
	}
	
	/**
	 * Set a data transformation.
	 * 
	 * One of: 'diff', 'rdiff', 'cumul', or 'normalize'.
	 * 
	 * @param string $arg
	 * @return $this
	 */
	public function transform($arg) {
		$this->url->manipulate('transformation', $arg);
		return $this;
	}
	
	/**
	 * Exclude data headers (column names) from the response.
	 * 
	 * Only for CSV calls.
	 * 
	 * @return $this
	 */
	public function excludeHeaders() {
		$this->url->manipulate('exclude_headers', 'true');
		return $this;
	}
	
	/**
	 * Try to forward unknown methods to Quandl\Url.
	 * 
	 * @return mixed
	 * 
	 * @throws BadMethodCallException
	 */
	public function __call($func, $args) {
			
		if (is_callable(array($this->url, $func))) {
				
			$results = call_user_func_array(array($this->url, $func), $args);
			
			return (null === $results) ? $this : $results;
		}
		
		throw new \BadMethodCallException("Unknown method '$func'");
	}
	
}
