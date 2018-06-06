<?php

class Offer {	const WORTH_ABSOLUTE = 0;
	const WORTH_RELATIVE = 1;
	protected $arts = [
		1 => 'Promocja',
		2 => 'kupony',
		3 => 'Gratis',
		4 => 'Kupon',
		5 => 'Rodzaj'

	];

	public function putCgId($externId, $cqId) {
		$model = Model::getInstance('live', Config::$connection_data_live);
		$model->execute('UPDATE gsp_angebot SET cq_id = ? WHERE extern_id = ?', [$cqId, $externId]);
	}
	public static function getFullWorth(array $offer) {
		if ($offer['worth'] <= 0 || empty($offer['currency_id'])) {
			return '';
		}
		$decimalPlaces = ceil($offer['worth']) > $offer['worth'] ? 2 : 0;
		$wholePosition = 'after';
		if ($offer['worth_unit'] == Offer::WORTH_RELATIVE) {
			//return CakeNumber::currency($offer['worth'], Currency::codes($offer['currency_id']), array('places' => $decimalPlaces, 'wholePosition' => $wholePosition, 'wholeSymbol' => '%'));
		}
		if ($offer['currency_id'] == Currency::CURRENCY_GBP) {
			$wholePosition = 'before';
		}
		//return CakeNumber::currency($offer['worth'], Currency::codes($offer['currency_id']), array('places' => $decimalPlaces, 'wholePosition' => $wholePosition));
	}

	public function findByShop($shopId) {		$model = Model::getInstance('live', Config::$connection_data_live);
		$query = 'SELECT *, extern_id id, FROM gsp_angebot WHERE ch_f5 = ? AND dat_f2 <= NOW() AND dat_f2 != "0000-00-00 00:00:00" AND (dat_f1 >= NOW() OR dat_f1 = "0000-00-00 00:00:00")';
		$stm = $model->execute($query, [$shopId]);
        if ($data = $model->fetchAll($stm)) {
        	return $data;
        }
        return [];
	}

	public function getMaxValueByShop($shopId) {		if (!($offers = $this->findByShop($shopId))) {			return null;
		}

		$maxValue = 0;
		$unit = '';
		foreach ($offers as $offer) {			if ((float)$offer['ch_f3'] > (float)$maxValue) {
               	$maxValue = $offer['ch_f3'];
               	$unit = ($offer['bool_f11'] == 1 ? 'z?' : ($offer['bool_f11'] == 2 ? '%' : ($offer['bool_f11'] == 3 ? '€' : ($offer['bool_f11'] == 4 ? '£' : ($offer['bool_f11'] == 5 ? '$' : '')))));
            }
		}
		return [
			'value' => str_replace('.00', '', $maxValue),
			'unit' => $unit,
			'lable' => $this->arts[$offer['ch_f10']]
		];
	}

	public function getPriority($shopId, $offerId) {		$model = Model::getInstance('live', Config::$connection_data_live);
		$query = 'SELECT * FROM gsp_shopOfferSortable WHERE f_shop = ?  AND offers LIKE ?';
		$stm = $model->execute($query, [$shopId, "%$offerId%"]);
        if (!($data = $model->fetch($stm))) {
        	return null;
        }
        $data = explode(',', $data['offers']);
        for($i = 0; $i < count($data); $i++) {
        	if ($data[$i] == $offerId) {				return $i+1;
        	}
        }
        return null;
	}
}

?>
