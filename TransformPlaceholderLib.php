<?php

class TransformPlaceholderLib {
	public $transformableStrings = array(
		'shopLink' => '{SHOP-LINK}',
		'offerWorth' => '{VALUE}',
		'startDate' => '{START}',
		'endDate' => '{END}',
		'shopName' => '{SHOP}',
		'year' => '{YEAR}',
		'month' => '{MONTH}',
		'fullDate' => '{DATE}',
		'shopOfferWorth' => '{AMOUNTTOPOFFER}'
	);
	/**
	 * TransformPlaceholderLib::transformSingle()
	 *
	 * @param string $content
	 * @param array $data
	 * @param bool $plain
	 * @return string
	 */
	public function transformSingle($content, array $data = array(), $plain = false) {
		if ($this->_checkTransform($content)) {
			$content = $this->_transform($content, $data, $plain);
		}
		return $content;
	}
	/**
	 * TransformPlaceholderLib::transformMulti()
	 *
	 * @param array $content
	 * @param array $data
	 * @param bool $plain
	 * @return array
	 */
	public function transformMulti($content, array $data = array(), $plain = false) {
		foreach ($content as $key => $item) {
			$content[$key] = $this->transformSingle($item, $data, $plain);
		}
		return $content;
	}
	/**
	 * Transforms Placeholders to actual data
	 *
	 * @param string $string
	 * @param array $data
	 * @param bool $plain
	 * @return string
	 */
	protected function _transform($string, array $data = array(), $plain = false) {
		$now = date('Y-m-d H:i:s');
		$shopName = !empty($data['Shop']['name']) ? $data['Shop']['name'] : '';
		$startDate = !empty($data['Offer']['valid_from']) ? $data['Offer']['valid_from'] : '';
		$endDate = !empty($data['Offer']['valid_until']) ? $data['Offer']['valid_until'] : __('Valid without enddate');
		$worth = !empty($data['Offer']['worth']) ? Offer::getFullWorth($data['Offer']) : '';
		$shopLink = '';
		$shopTopOffer = '';
		if (!empty($data['Shop']['slug']) && strpos($string, $this->transformableStrings['shopLink'])) {
			$shopLink = $data['Shop']['slug'];
			$shopLink = '<a href="' . $shopLink . '" title="' . $shopName . '">' . $shopName . '</a>';
		}
		if (empty($data['IgnoreTopOffer']) && !empty($data['Shop']['id']) && !empty($data['Offer']['id']) && strpos($string, $this->transformableStrings['shopOfferWorth'])) {
			$shopTopOffer = $this->_getBestOfferByShop($data);
		}
		$replace = array($shopLink, $worth, $startDate, $endDate, $shopName, $now->format(FORMAT_NICE_Y), __($now->format('F')), $now->format(FORMAT_NICE_YMD), $shopTopOffer);
		$return = str_replace($this->transformableStrings, $replace, $string);
		if ($plain) {
			$return = html_entity_decode(strip_tags($return));
		}
		return $return;
	}
	/**
	 * Checks if a given string has any Placeholders to transform.
	 *
	 * @param string $string
	 * @return boolean
	 */
	protected function _checkTransform($string) {
		foreach ($this->transformableStrings as $value) {
			if (strpos($string, $value) !== false) {
				return true;
			}
		}
		return false;
	}
	/**
	 * TransformPlaceholderLib::_getBestOfferByShop()
	 *
	 * @param array $data
	 * @return string
	 */
	protected function _getBestOfferByShop($data) {
		$this->Offer = ClassRegistry::init('Offer');
		$options = array(
			'conditions' => array(
				'Offer.shop_id' => $data['Shop']['id'],
				'Offer.worth_unit' => Offer::WORTH_ABSOLUTE,
				'Shop.status' => true
			) + $this->Offer->makeValidConditions($data['Offer']),
			'contain' => array('Shop.id'),
			'order' => array('Offer.worth' => 'DESC'),
		);
		if ($offer = $this->Offer->find('first', $options)) {
			return Offer::getFullWorth($offer['Offer']);
		}
		return '';
	}
}
?>