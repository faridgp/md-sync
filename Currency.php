<?php

class Currency {
	public $order = array();
	/**
	 * Currency::codes()
	 *
	 * @param mixed $value
	 * @return mixed
	 */
	public static function codes($value = null) {
		$options = array(
			static::CURRENCY_USD => 'USD',
			static::CURRENCY_EUR => 'EUR',
			static::CURRENCY_GBP => 'GBP',
			static::CURRENCY_CHF => 'CHF',
			static::CURRENCY_AUD => 'AUD',
			static::CURRENCY_CAD => 'CAD',
			static::CURRENCY_JPY => 'JPY'
		);
		return parent::enum($value, $options);
	}
	const CURRENCY_USD = 1;
	const CURRENCY_EUR = 2;
	const CURRENCY_GBP = 3;
	const CURRENCY_CHF = 4;
	const CURRENCY_AUD = 5;
	const CURRENCY_CAD = 6;
	const CURRENCY_JPY = 7;
}

?>