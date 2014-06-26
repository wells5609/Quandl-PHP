<?php

class Quandl_Response {
	
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
	
	public function __construct($response) {
		
		foreach((array)$response as $key => $value) {
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
	
	public function get($var) {
		return isset($this->$var) ? $this->$var : null;
	}
	
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
	
}
