<?php
class OfferListener extends AbstractSyncListener {
	protected $_table = 'gsp_angebot';
	protected $_endpoint = 'offers';

	protected $_typesMap = [
		1 => 2, // CONQUEROR:TYPE_SALE
		2 => 4, // CONQUEROR:TYPE_PRINTABLE
		3 => 5, // CONQUEROR:TYPE_FREEBIE
		4 => 1, // CONQUEROR:TYPE_COUPON
		5 => 3, // CONQUEROR:TYPE_GIFT_CARD
	];
	/**
	 * @var array
	 */
	protected $_currenciesMap = [
		0 => 1, // ??? => EUR
		5 => 2, // USD
		3 => 1, // EUR
		4 => 3, // GBP
		1 => 5, // Zl
	];
	/**
	 * @var array
	 */
	protected $_customerTypesMap = [
		0 => 1, // CONQUEROR:CUSTOMER_TYPE_NEW
		1 => 2, // CONQUEROR:CUSTOMER_TYPE_OLD
		2 => 3, // CONQUEROR:CUSTOMER_TYPE_BOTH
	];
	const CUSTOMER_NEW = 0;
	const CUSTOMER_OLD = 1;
	const CUSTOMER_ALL = 2;


   	public function __construct(){
        parent::__construct();
    }

    protected function _findRecord()
	{
		$query = 'SELECT *, extern_id id, IF(dat_f2 <= NOW() AND dat_f2!="0000-00-00 00:00:00" AND (dat_f1 >= NOW() OR dat_f1="0000-00-00 00:00:00"), 1, 0) status FROM ' . $this->_table . ' WHERE extern_id = ? AND ch_f1 != ""';
        $stm = $this->model->execute($query, [$this->id]);
        if ($data = $this->model->fetch($stm)) {
        	return $data;
        }
        return null;
	}

   	protected function _findRecords()
	{
		$query = 'SELECT extern_id id FROM ' . $this->_table . ' WHERE ch_f1 != "" ORDER BY cq_id ASC';
        $stm = $this->model->execute($query);
        if ($data = $this->model->fetchAll($stm)) {
        	return $data;
        }
        return null;
	}

	protected function _formatData(array $record)
	{
		$textProcessor = new TextProcessor();
		$placeholderProcessor = new PlaceholderProcessor();
		$urlBuilder = new UrlBuilder();
		$datetimeProcessor = new DatetimeProcessor();
		$shop = new Shop();
		$offer = new Offer();
        if (!($shop_cq_id = $shop->findCqId($record['ch_f5']))) {        	return null;
        }
		$redeemNotes = $placeholderProcessor->process($record['txt_f2'], $record);
		$redeemNotes = $textProcessor->process($redeemNotes);
		$redeemExceptions = $placeholderProcessor->process($record['ausnahmen'], $record);
		$redeemExceptions = $textProcessor->process($redeemExceptions);
		$description = sprintf('<h2>%s</h2>', $record['coupon_small_text_header']) . $record['coupon_small_text'];
		$description = $placeholderProcessor->process($description, $record);
		$description = $textProcessor->process($description);
        $minimum_order_value = $shop->findMinimumOrderValue($record['ch_f5']);
		$data = [
			'name' => $textProcessor->decode(sprintf('%s %s', $record['headline'], $record['headlineSmall'])),
			'description' => strip_tags($description),
			'shop_id' => $shop_cq_id,
			'review_date' => $datetimeProcessor->process((!empty($record['dat_f6']) && $record['dat_f6'] != '00.00.0000' ? $record['dat_f6'] : ''), true),
			'is_active' => (bool)$record['status'],
			'start_date' => $datetimeProcessor->process((!empty($record['dat_f2']) && $record['dat_f2'] != '00.00.0000' ? $record['dat_f2'] : '') , true),
			'expires' => $datetimeProcessor->process((!empty($record['dat_f1']) && $record['dat_f1'] != '00.00.0000' ? $record['dat_f1'] : ''), true),
			'customer_type' => $this->_resolveCustomerType($record),
			'availability' => $this->_resolveAvailability($record),
			'redeem_notes' => $redeemNotes,
			'redeem_exceptions' => $redeemExceptions,
			'type' => $this->_resolveType($record),
			'coupon_code' => (string)$record['ch_f14'],
			'has_unique_codes' => (bool)$record['bool_f14'],
			'is_percentage' => ($record['bool_f11'] == 2 ? true : false),
			'worth' => $this->_resolveNumber($record['ch_f3']),
			'currency_id' => ($record['bool_f11'] == 2 ? null : $this->_currenciesMap[$record['bool_f11']]),
			'minimum_order_value' => ((bool)$record['noMbw'] ? null : $minimum_order_value),
			'is_exclusive' => (bool)$record['bool_f13'],
			'is_free_shipping' => (bool)$record['freeshipping'],
			'clickout_url' => $urlBuilder->getValidUrl(trim($record['ch_f2'])),
			'priority' => $offer->getPriority($record['ch_f5'], $record['id']),
			'inheritance' => [
				'legacy_tanio_id' => $record['id'],
			],
		];
		return $data;
	}

	/**
	 * @param int|float $number
	 *
	 * @return string
	 */
	protected function _resolveNumber($number)
	{
		$number = str_replace('.00', '', $number);
		if ($number <= 0 || $number === '0.00') {
			return '';
		}
		return (string)$number;
	}
	/**
	 * @param array $record
	 *
	 * @return int
	 */
	protected function _resolveCustomerType(array $record)
	{
		if ((!(bool)$record['bool_f9'] && !(bool)$record['bool_f10']) || ((bool)$record['bool_f9'] && (bool)$record['bool_f10'])) {
			return self::CUSTOMER_ALL; // CONQUEROR:CUSTOMER_TYPE_BOTH
		}
		if ((bool)$record['bool_f9']) {
			return self::CUSTOMER_OLD; // CONQUEROR:CUSTOMER_TYPE_BOTH
		}
		if ((bool)$record['bool_f10']) {
			return self::CUSTOMER_NEW; // CONQUEROR:CUSTOMER_TYPE_BOTH
		}
	}
	/**
	 * @param array $record
	 *
	 * @return int
	 */
	protected function _resolveAvailability(array $record)
	{
		//if ($record['Offer']['is_local']) {
		//	return 2; // CONQUEROR:AVAILABILITY_OFFLINE
		//}
		return 1; // CONQUEROR:AVAILABILITY_ONLINE
	}
	/**
	 * @param array $record
	 *
	 * @return int
	 */
	protected function _resolveType(array $record)
	{
		$type = $this->_typesMap[$record['f_angebotart']];
		if (!(bool)$record['freeshipping']) {
			return $type;
		}
		$worth = $this->_resolveNumber($record['ch_f3']);
		if (empty($worth)) {
			return 6; // CONQUEROR:TYPE_FREE_SHIPPING
		}
		return $type;
	}

	public function putCgId($cqId) {
		$offer = new Offer();
		$offer->putCgId($this->id, $cqId);
	}
}
?>