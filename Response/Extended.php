<?php

namespace Quandl\Response;

class Extended extends \Quandl\Response {
	
	/**
	 * Returns the first observation from the dataset.
	 * 
	 * Does NOT account for sort order - i.e. in ASC order,
	 * returns the oldest observation; in DESC order, returns the
	 * newest observation.
	 * 
	 * @return mixed
	 */
	public function getFirstObservation() {
		$data = $this->data;
		return array_pop($data);
	}
	
	/**
	 * Returns the last observation from the dataset.
	 * 
	 * Does NOT account for sort order - i.e. in ASC order,
	 * returns the newest observation; in DESC order, returns the
	 * oldest observation.
	 * 
	 * @return mixed
	 */
	public function getLastObservation() {
		$data = $this->data;
		return array_shift($data);
	}
	
	/**
	 * Returns the observation for a given date, if it exists.
	 * 
	 * @param string $date Date in any PHP-recognized format (uses strtotime()).
	 * @return array|null|boolean
	 */
	public function getDataFrom($date, &$index = null) {
		
		if (! $time = strtotime($date)) {
			return false;
		}
		if ($time < strtotime($this->from_date) || $time > strtotime($this->to_date)) {
			return false;
		}
		
		foreach($this->data as $i => $observation) {
			if ($time == strtotime($observation['Date'])) {
				$index = $i;
				return $observation;
			}
		}
		
		return null;
	}
	
	/**
	 * Returns the observations between two dates.
	 * 
	 * If no ending date is given, returns the observations
	 * up to and including the most recent.
	 * 
	 * @param string $start_date A PHP-recognized date.
	 * @param string $end_date [Optional]
	 * @return array
	 */
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
		
		foreach($this->data as $key => $observation) {
			
			$obs = strtotime($observation['Date']);
			
			if ($start <= $obs && $end >= $obs) {
				$data[$key] = $observation;
			}
		}
		
		return $data;
	}
	
}
