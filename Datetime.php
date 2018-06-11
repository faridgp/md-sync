<?php
class DatetimeProcessor
{
	/**
	 * @param string $datetime
	 * @param bool $nullable
	 *
	 * @return string|null
	 */
	public function process($datetime, $nullable)
	{
		$dst = date('I') === '0' ? 1 : 2;
		$result = (($nullable === true) ? '' : (date('Y-m-d H:i:s', mktime(date('H') - $dst, date('i'), date('s'), date('m'), date('d'), date('Y')))));
		if (!$datetime || $datetime === '0000-00-00 00:00:00' || $datetime === '0000-00-00') {
			return $result;
		}
		$datetime = strtotime($datetime);
		return date('Y-m-d H:i:s', mktime(date('H', $datetime) - $dst, date('i', $datetime), date('s', $datetime), date('m', $datetime), date('d', $datetime), date('Y', $datetime)));
	}
}
?>