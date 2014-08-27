<?php

namespace Quandl;

/**
 * Represents a response from the Quandl API.
 */
class Response implements \Serializable, \Countable, \IteratorAggregate {
	
	protected $id;
	protected $errors;
	protected $source_name;
	protected $source_code;
	protected $code;
	protected $name;
	protected $private;
	protected $type;
	protected $urlize_name;
	protected $display_url;
	protected $description;
	protected $updated_at;
	protected $frequency;
	protected $from_date;
	protected $to_date;
	protected $column_names;
	protected $data;
	
	/**
	 * Creates a new Response from a JSON response string.
	 * 
	 * @param string $json JSON string returned from Quandl API.
	 * @return \Quandl\Response
	 */
	public static function createFromJson($json) {
		return new static(json_decode($json));
	}
	
	/**
	 * Creates a new Response from an XML response string.
	 * 
	 * @param string $xml XML string returned from Quandl API.
	 * @return \Quandl\Response
	 */
	public static function createFromXml($xml) {
		
		// xml to array via json see-saw
		$parsed = json_decode(json_encode(simplexml_load_string($xml)), true);
		$data = array();
		
		foreach($parsed as $key => $value) {
			// make keys match class properties
			$key = str_replace('-', '_', $key);
			
			// normalize various mis-matchings
			switch($key) {
					
				case 'column_names':
					$value = $value['column-name'];
					break;
				
				case 'data':
					foreach($value['datum'] as &$obs) {
						$obs = $obs['datum'];
					}
					$value = $value['datum'];
					break;
				
				case 'type':
					unset($value['@attributes']);
					$value = empty($value) ? null : $value;
					break;
				
				default:
					break;
			}
			
			$data[$key] = $value;
		}
		
		return new static($data);
	}
	
	/**
	 * Creates a new Response from a CSV response string.
	 * 
	 * Note CSV responses only provide the data and column names (if not excluded).
	 * 
	 * @param string $csv CSV string returned from Quandl API.
	 * @return \Quandl\Response
	 */
	public static function createFromCsv($csv, $exclude_headers = false) {
		
		// Parse rows in array
		$rows = str_getcsv($csv, "\n");
		
		$data = array('data' => array());
		
		if (! $exclude_headers) {
			// Parse 1st row as headers
			$data['column_names'] = str_getcsv(array_shift($rows), ',');
		}

		foreach($rows as $row) {
			$data['data'][] = str_getcsv($row, ',');
		}
		
		return new static($data);
	}
	
	/**
	 * Constructs the object using response data.
	 * 
	 * @param mixed $response
	 */
	public function __construct($response) {
		$this->import($response);
	}
	
	/**
	 * Returns an object property, if set.
	 * 
	 * @param string $var
	 * @return mixed
	 */
	public function get($var) {
		return isset($this->$var) ? $this->$var : null;
	}
	
	/**
	 * Returns the entire dataset.
	 * 
	 * @return array
	 */
	public function getData() {
		return $this->data;
	}
	
	/**
	 * Returns the number of observations in the response data.
	 * 
	 * @return int.
	 */
	public function count() {
		return count($this->data);
	}
	
	/**
	 * Returns an \ArrayIterator for the response data.
	 * 
	 * @return \ArrayIterator
	 */
	public function getIterator() {
		return new \ArrayIterator($this->data);
	}
	
	/**
	 * Merges an array of data into existing data recursively.
	 * 
	 * @param array $newdata
	 * @return $this
	 */
	public function mergeData(array $data) {
		
		$this->data = array_merge_recursive($this->data, $data);
		
		return $this;
	}
	
	/**
	 * Checks whether any errors were returned.
	 * 
	 * @return boolean
	 */
	public function isError() {
		
		if (empty($this->errors)) {
			return false;
		}
		
		$vars = get_object_vars($this->errors);
		
		return ! empty($vars);	
	}
	
	/**
	 * Returns the index for a given column name.
	 * 
	 * The 'Date' column is always 0. Data columns begin at 1.
	 * 
	 * @param string $name
	 * @return int
	 */
	public function getColumnIndex($name) {
		
		if (! isset($this->column_names)) {
			return null;
		}
		
		return array_search($name, $this->column_names, true);
	}
	
	/**
	 * Returns the date on which the dataset was last updated.
	 * 
	 * Will not work for CSV requests.
	 * 
	 * @return string
	 */
	public function getLastUpdate() {
		return isset($this->updated_at) ? $this->updated_at : null;
	}
	
	/**
	 * Imports data into the object as properties.
	 * 
	 * Additionally attempts to add column names to each 
	 * observation as item keys.
	 * 
	 * @param mixed $data
	 * @return $this
	 */
	public function import($data) {
		
		if (! is_array($data)) {
			$data = method_exists($data, 'toArray') ? $data->toArray() : (array)$data;
		}
		
		foreach($data as $key => $value) {
			if (property_exists($this, $key)) {
				$this->$key = $value;
			}
		}
		
		if (! empty($this->column_names) && ! empty($this->data)) {
			// Combine column names and data
			foreach($this->data as &$array) {
				$array = array_combine($this->column_names, $array);
			}
		}
		
		return $this;
	}
	
	public function toArray() {
		return get_object_vars($this);
	}
	
	public function serialize() {
		return serialize($this->toArray());
	}
	
	public function unserialize($serial) {
		$this->import(unserialize($serial));
	}
	
}
