<?php

namespace Quandl\Response;

class Stock extends \Quandl\Response {
	
	protected $return_all_data = false;
	
	public function returnAllData($val = null) {
		if (isset($val)) {
			$this->return_all_data = (bool)$val;
		}
		return $this->return_all_data;
	}
	
	public function simpleMovingAverage($periods = 15, $start_date = null, $end_date = null) {
		
		if (! isset($start_date)) {
			$firstObs = $this->getFirstObservation();
			$start_date = $firstObs['Date'];
		}
		
		$data = $this->getDataBetween($start_date, $end_date);
		
		if (empty($data)) {
			return null;
		}
		
		$sma = array();
		$key = 'SMA_'.$periods;
			
		foreach($data as $i => &$item) {
			
			$vals = array_slice($data, $i, $periods, true);
			
			if (count($vals) < $periods) {
				// not enough information
				$item[$key] = 'NEI';
				continue;
			}
		
			$period_sum = array_sum(array_column($vals, 'Adj. Close'));
		
			$item[$key] = round($period_sum/$periods, 4);
		
			$smas[] = array(
				'Date' => $item['Date'],
				'Close' => $item['Close'],
				'Adj. Close' => $item['Adj. Close'],
				'SMA-'.$periods => $item[$key],
			);
		}
		
		return $this->returnAllData() ? $data : $smas;
	}
	
}
