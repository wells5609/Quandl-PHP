<?php

namespace Quandl;

/**
 * Represents a single Quandl API request.
 * 
 * Encapsulates operations on \Quandl\Url and produces a \Quandl\Response.
 */
class Request {
	
	/**
	 * @var \Quandl\Url
	 */
	protected $url;
	
	/**
	 * @var \Quandl\Response
	 */
	protected $response;
	
	/**
	 * Creates a URL for the given Quandl code.
	 * 
	 * @param string $quandlCode Full Quandl code string.
	 */
	public function __construct($quandlCode) {
		$this->url = new Url($quandlCode);
	}
	
	/**
	 * Checks whether a cached response is available.
	 * 
	 * @return boolean
	 */
	public function isCached() {
		
		if (! Quandl::hasCache()) {
			return false;
		}
		
		$cache = Quandl::getCache();
		
		return (bool) $cache->get($this->url->getQuandlCode(), $this->url->getManipulations());
	}
	
	/**
	 * Returns the cached response, if it exists.
	 * 
	 * @return \Quandl\Response
	 */
	public function getCached() {
		
		if (! Quandl::hasCache()) {
			return null;
		}
		
		$cache = Quandl::getCache();
		
		return $cache->get($this->url->getQuandlCode(), $this->url->getManipulations());
	}
	
	/**
	 * Sends the request.
	 * 
	 * @return \Quandl\Request
	 */
	public function send(&$success = null) {
		
		if (! Quandl::hasAdapter()) {
			$success = false;
			throw new \RuntimeException("Cannot send request: no request adapter set.");
		}
		
		$response = Quandl::getAdapter()->request($this->url);
		
		if (empty($response)) {
			$success = false;
		
		} else {	
		
			switch($this->url->getFormat()) {
				
				case '.json':
					$response = Response::createFromJson($response);
					break;
				
				case '.xml':
					$response = Response::createFromXml($response);
					break;
				
				case '.csv':
					$excl_headers = (bool) $this->url->getManipulation('exclude_headers');
					$response = Response::createFromCsv($response, $excl_headers);
					break;
			}
			
			$this->response = $response;
			$success = true;
		}
		
		return $this;
	}
	
	/**
	 * Returns the request's Quandl\Response.
	 * 
	 * @return \Quandl\Response
	 */
	public function getResponse() {
		return isset($this->response) ? $this->response : null;
	}
	
	/** ===========================================================
	 * 		Manipulation Methods
	 * ==========================================================*/
	
	/**
	 * Set the sort order of the results.
	 * 
	 * @param string $order One of "asc" or "desc", or their corresponding PHP constants.
	 * @return \Quandl\Request
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
	 * Set the start date for the returned time series.
	 * 
	 * @param string $Ymd Date in format "2001-12-31"
	 * @return \Quandl\Request
	 */
	public function startDate($Ymd) {
		
		$this->url->manipulate('trim_start', $Ymd);
		
		return $this;
	}
	
	/**
	 * Set the ending date for the returned time series.
	 * 
	 * @param string $Ymd Date in format "2011-12-31"
	 * @return \Quandl\Request
	 */
	public function endDate($Ymd) {
		
		$this->url->manipulate('trim_end', $Ymd);
		
		return $this;
	}
	
	/**
	 * Set the number of rows to return.
	 * 
	 * @param int $num
	 * @return \Quandl\Request
	 */
	public function rows($num) {
		
		$this->url->manipulate('rows', $num);
		
		return $this;
	}
	
	/**
	 * Set the column index to return.
	 * 
	 * @param int $index
	 * @return \Quandl\Request
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
	 * @return \Quandl\Request
	 */
	public function frequency($freq) {
		
		$this->url->manipulate('collapse', $freq);
		
		return $this;
	}
	
	/**
	 * Set a data transformation.
	 * 
	 * @param string $arg One of: 'diff', 'rdiff', 'cumul', or 'normalize'.
	 * @return \Quandl\Request
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
	 * @return \Quandl\Request
	 */
	public function excludeHeaders() {
		
		$this->url->manipulate('exclude_headers', 'true');
		
		return $this;
	}
	
	/**
	 * Try to forward unknown methods to \Quandl\Url.
	 * 
	 * @return mixed
	 * 
	 * @throws \BadMethodCallException
	 */
	public function __call($func, array $args) {
			
		if (is_callable(array($this->url, $func))) {
			return call_user_func_array(array($this->url, $func), $args);
		}
		
		throw new \BadMethodCallException("Unknown method '$func'");
	}
	
}
