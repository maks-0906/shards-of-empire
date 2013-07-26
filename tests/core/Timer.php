<?php
/**
 * Description content file
 *
 * @author Greg
 * @package
 */

/**
 * Description class
 *
 * @author Greg
 * @version
 * @package
 */
class Timer extends Singleton {

	private $TIMES = array();

	/**
	 * Start time profiling.
	 *
	 * @param string $point
	 */
	public function startTime($point)
	{
		$dat = getrusage();

		$this->TIMES[$point]['start'] = microtime(TRUE);
		$this->TIMES[$point]['start_utime'] = $dat["ru_utime.tv_sec"] * 1e6 + $dat["ru_utime.tv_usec"];
		$this->TIMES[$point]['start_time'] = $dat["ru_stime.tv_sec"] * 1e6 + $dat["ru_stime.tv_usec"];
	}

	/**
	 * Stop profiling.
	 *
	 * @param string $point
	 * @param string $comment
	 */
	public function stopTime($point, $comment = '')
	{
		$dat = getrusage();

		$this->TIMES[$point]['end'] =  microtime(TRUE);
		$this->TIMES[$point]['end_utime'] = $dat["ru_utime.tv_sec"] * 1e6 + $dat["ru_utime.tv_usec"];
		$this->TIMES[$point]['end_stime'] = $dat["ru_stime.tv_sec"] * 1e6 + $dat["ru_stime.tv_usec"];

		$this->TIMES[$point]['comment'] .= $comment;

		$this->TIMES[$point]['sum'] += $this->TIMES[$point]['end'] - $this->TIMES[$point]['start'];
		$this->TIMES[$point]['sum_utime'] +=
			($this->TIMES[$point]['end_utime'] - $this->TIMES[$point]['start_utime']) / 1e6;
		$this->TIMES[$point]['sum_stime'] +=
			($this->TIMES[$point]['end_stime'] - $this->TIMES[$point]['start_stime']) / 1e6;
	}

	public function logData()
	{
		//$query_logger = DBQueryLog::
	}
}
