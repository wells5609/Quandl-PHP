<?php

namespace Quandl;

use InvalidArgumentException;

class Response implements \Serializable {
	
	protected $errors;
	protected $id;
	protected $source_name;
	protected $source_code;
	protected $code;
	protected $name;
	protected $urlize_name;
	protected $description;
	protected $updated_at;
	protected $frequency;
	protected $from_date;
	protected $to_date;
	protected $column_names;
	protected $private;
	protected $type;
	protected $display_url;
	protected $data;
	
	public static function createFromJson($json) {
		return new static(json_decode($json));
	}
	
	public static function createFromCsv($csv) {
		
		$rows = str_getcsv($csv, "\n");
		$headers = str_getcsv(array_shift($rows), ',');
		
		$data = array(
			'column_names' => $headers,
			'data' => array()
		);
		
		foreach($rows as $row) {
			$data['data'][] = str_getcsv($row, ',');
		}
		
		return new static($data);
	}
	
	public static function createFromXml($xml) {
		
		$parsed = json_decode(json_encode(simplexml_load_string($xml)), true);;
		$data = array();
		
		foreach($parsed as $key => $value) {
			
			$key = str_replace('-', '_', $key);
			
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
	
	public function get($var) {
		return isset($this->$var) ? $this->$var : null;
	}
	
	public function getColumnIndex($name) {
		return array_search($name, $this->column_names, true);
	}
	
	public function getLastUpdate() {
		return $this->updated_at;
	}
	
	public function getObservationCount() {
		return count($this->data);
	}
	
	public function getFirstObservation() {
		$data = $this->data;
		return array_pop($data);
	}
	
	public function getLastObservation() {
		$data = $this->data;
		return array_shift($data);
	}
	
	public function getData() {
		return $this->data;
	}
	
	public function isError() {
		if (! empty($this->errors) && $vars = get_object_vars($this->errors)) {
			return ! empty($vars);	
		}
		return false;
	}
	
	/**
	 * Returns the observation for a given date, if it exists.
	 * 
	 * @param string $date Date in any PHP-recognized format (uses strtotime()).
	 * @return array|null|boolean
	 */
	public function getDataFrom($date) {
		
		if (! $time = strtotime($date)) {
			return false;
		}
		if ($time < strtotime($this->from_date)) {
			return false;
		}
		if ($time > strtotime($this->to_date)) {
			return false;
		}
		
		foreach($this->data as $observation) {
			if ($time == strtotime($observation['Date'])) {
				return $observation;
			}
		}
		
		return null;
	}
	
	public function getDataBetween($start_date, $end_date = null) {
		
		if (! $start = strtotime($start_date)) {
			return false;
		}
		
		if (! isset($end_date)) {
			$end = time();	
		} else if (! $end = strtotime($end_date)) {
			return false;
		}
		
		$data = array();
		
		foreach($this->data as $observation) {
			
			$obs = strtotime($observation['Date']);
			
			if ($start <= $obs && $end >= $obs) {
				$data[] = $observation;
			}
		}
		
		return $data;
	}
	
	public function __construct($response) {
		$this->import($response);
	}
	
	public function import($data) {
		
		foreach((array)$data as $key => $value) {
			if (property_exists($this, $key)) {
				$this->$key = $value;
			}
		}
		
		if (! empty($this->column_names) && ! empty($this->data)) {
			foreach($this->data as &$array) {
				$array = array_combine($this->column_names, $array);
			}
		}
	}
	
	public function upgradeObject($class) {
		
		if (! class_exists($class, true)) {
			throw new InvalidArgumentException("Unknown class '$class'.");
		}
		
		return new $class(get_object_vars($this));
	}
	
	public function serialize() {
		return serialize(get_object_vars($this));
	}
	
	public function unserialize($serial) {
		$this->import(unserialize($serial));
	}
	
}
