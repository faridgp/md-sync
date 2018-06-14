<?php
class ShopListener extends AbstractSyncListener {
	protected $_table = 'gsp_shops';
	protected $_endpoint = 'shops';
	protected $_categoriesMap = [
		10083 => 37,
		10059 => 57,
		10072 => 42,
		10001 => 64,
		10027 => 64,
		10029 => 64,
		10028 => 64,
		10026 => 64,
		10065 => 40,
		10012 => 54,
		10030 => 54,
		10075 => 53,
		10007 => 52,
		10037 => 52,
		10036 => 52,
		10038 => 52,
		10085 => 52,
		10091 => 52,
		10040 => 38,
		10041 => 38,
		10096 => 38,
		10071 => 38,
		10076 => 38,
		10042 => 38,
		10044 => 46,
		10098 => 46,
		10097 => 46,
		10013 => 41,
		10079 => 49,
		10087 => 50,
		10004 => 58,
		10032 => 54,
		10033 => 54,
		10061 => 43,
		10002 => 51,
		10045 => 51,
		10046 => 51,
		10060 => 51,
		10100 => 51,
		10099 => 51,
		10003 => 56,
		10050 => 56,
		10051 => 56,
		10077 => 56,
		10089 => 56,
		10103 => 56,
		10112 => 56,
		10082 => 56,
		10039 => 45,
		10094 => 45,
		10109 => 45,
		10049 => 60,
		10009 => 63,
		10008 => 44,
		10005 => 59,
		10078 => 59,
		10104 => 59,
		10066 => 59,
		10056 => 59,
		10057 => 59,
		10088 => 59,
		10073 => 62,
		10062 => 61,
		10107 => 61,
		10106 => 61,
		10105 => 61,
		10011 => 39,
		10055 => 65,
		10074 => 47,
		10084 => 48,
		10086 => 55,
		// UNSORTED
		10014 => 36,
		10063 => 36,
		10067 => 36,
		10068 => 36,
		10070 => 36,
		10080 => 36,
		10092 => 36,
		10093 => 36,
		10101 => 36,
		10090 => 36,
		10081 => 36,
		0 => 36,
	];
	protected $_domainsMap = [];
	protected $_excludeIds = [];

	/**
	 * @var array
	 */
	protected $_paymentMethodsMap = [
		1 => 1, // Paypal
		2 => 2, // Rechnung
		3 => 3, // Bankeinzug
		4 => 4, // Vorauskasse
		5 => 5, // Nachnahme
		6 => 6, // Ratenzahlung
		7 => 7, // Sofort�berweisung
		8 => 8, // paysafecard
		9 => 9, // moneybookers
		10 => 10, // clickandbuy
		11 => 11, // giropay
		12 => 12, // visa
		13 => 13, // Eurocard / Mastercard
		14 => 14, // Amex
		15 => 15, // Barzahlung
		16 => 16, // ogone
		17 => 17, // Debitkarte
		19 => 18, // Wirecard
		20 => 19, // Diners Club Card
		21 => 20, // Billsafe
		22 => 21, // Trust Pay
		23 => 22, // Click2Pay
		24 => 23, // Qiwi
		25 => 24, // Amazon Payments
		37 => 37, // przelew24
		38 => 38, // przelewy
		39 => 39, // wbk
		40 => 40, // polcard
		41 => 41, // transferuj
		42 => 42, // SMS
	];

	protected $_deliveryMethodsMap = [
		1 => 1, // DHL
		2 => 2, // UPS
		3 => 3, // Hermes
		4 => 4, // DPD
		5 => 5, // Fed Ex
		6 => 6, // GLS
		7 => 7, // Gogreen (Deutsche Post)
		8 => 8, // Spedition
		9 => 9, // Inpost
		10 => 10, // Paczkomat
		11 => 11, // Odbi?r osobisty
		12 => 12, // Si?demka
		13 => 13, // Kurier
		14 => 14, // OPEK
		15 => 15, // K-EX
	];

	public function __construct() {
		parent::__construct();
	}

	protected function _findRecords() {		$query = "SELECT extern_id id FROM " . $this->_table . " WHERE ch_f1 != '' AND (lockedPartners NOT LIKE '%" . $this->partner . "%' OR ISNULL(lockedPartners) OR lockedPartners = '') ORDER BY " . ($this->partner === '17d2cb9b' ? 'onet_cq_id' : 'cq_id') . " ASC";		$stm = $this->model->execute($query);
		if ($data = $this->model->fetchAll($stm)) {
			return $data;
		}
		return null;
	}

	protected function _findRecord() {
		if (in_array($this->id, $this->_excludeIds)) {
			return null;
		}

		$query = "SELECT *, extern_id id FROM " . $this->_table . " WHERE extern_id = ? AND ch_f1 != ''";
		$stm = $this->model->execute($query, [$this->id]);
		$data2 = [];
		if ($this->partner === '17d2cb9b') {
			  $query2 = "SELECT txt_f2, txt_f3, vorspannText, txt_f19, txt_f21  FROM gsp_shops_seo_partners WHERE extern_id = ? AND partner = ?";
			  $stm2 = $this->model->execute($query2, [$this->id, $this->partner]);
			  $data2 = $this->model->fetch($stm2);
		}
		if ($data = $this->model->fetch($stm)) {
			if (!empty($data2)) {
				$data['txt_f2'] = $data2['txt_f2'];
				$data['txt_f3'] = $data2['txt_f3'];
				$data['vorspannText'] = $data2['vorspannText'];
				$data['txt_f19'] = $data2['txt_f19'];
				$data['txt_f21'] = $data2['txt_f21'];
			}
			return $data;
		}
		return null;
	}

	protected function _formatData(array $record) {
		$textProcessor = new TextProcessor();
		$placeholderProcessor = new PlaceholderProcessor();
		$urlBuilder = new UrlBuilder();
		$description = $placeholderProcessor->process($record['txt_f3'], $record);
		$description = $textProcessor->process($description);

		$excerpt = sprintf('<h2>%s</h2>', $record['txt_f2']) . $record['vorspannText'];
		$excerpt = $placeholderProcessor->process($excerpt, $record);
		$excerpt = $textProcessor->process($excerpt);

		$couponNotes = $placeholderProcessor->process($record['txt_f19'], $record);
		$couponNotes = $textProcessor->process($couponNotes);

		$saleNotes = $placeholderProcessor->process($record['txt_f21'], $record);
		$saleNotes = $textProcessor->process($saleNotes);

		$minimumOrderLabel = $this->_formatMinimumOrderLabel($record['mbw_short']);

		$domain = $this->_formatDomain($record['ch_f5']);

		$data = [
			'shop_category_id' => $this->_categoriesMap[$record['breadcrumb']],
			'domain' => $domain,
			'show_expired_offers' => (bool)$record['show_expired_offers'],
			'clickout_url' => $urlBuilder->getValidUrl($record['ch_f2']),
			'logo_url' => $urlBuilder->getImageUrl((empty($record['ch_f7']) ? $record['ch_f9'] : $record['ch_f7'])),
			'minimum_order_label' => $minimumOrderLabel,
			'url' => $urlBuilder->getValidUrl($record['screenshotUrl']),
			'is_monetizable' => (bool)$record['is_monetizable'],
			'is_active' => true,

			'inheritance' => [
				'legacy_tanio_id' => $record['id'],
				'slug' => trim(strtolower($record['ch_f4'])),
				'name' => $textProcessor->decode($record['ch_f1']),
				'description' => $description,
				'excerpt' => $excerpt,
				'clickout_url' => $urlBuilder->getValidUrl($record['ch_f2']),
				'show_expired_offers' => (bool)$record['show_expired_offers'],
				'meta_title' => '',
				'meta_description' => '',
				'is_active' => (bool)$record['bool_f1'],
				'shop_redeem_notes' => [
					[
						'note' => $couponNotes,
						'type' => 1,
					],
					[
						'note' => $saleNotes,
						'type' => 2,
					],
				],
				'address' => $textProcessor->decode($this->_formatAddress($record)),
				'contact' => $textProcessor->decode($this->_formatContact($record)),
			],

			'payment_methods' => $this->_formatPaymentMethods($record),
			'delivery_methods' => $this->_formatDeliveryMethods($record),
		];
		if (($this->partner === '0123456789' && empty($record['cq_id'])) || ($this->partner === '17d2cb9b' && empty($record['onet_cq_id']))) {
			$data += [
				'name' => $textProcessor->decode($record['ch_f1']) . ' PL',
				'slug' => trim(strtolower($record['ch_f4'])) . '-pl',
			];
		}
		$metaTitle = $placeholderProcessor->process($record['meta_title'], $record);
		$metaTitle = $textProcessor->decode($metaTitle);
		$metaDescription = $placeholderProcessor->process($record['meta_description'], $record);
		$metaDescription = $textProcessor->decode($metaDescription);
		$data['inheritance']['meta_title'] = $metaTitle;
		$data['inheritance']['meta_description'] = $metaDescription;
		return $data;
	}

	/**
	 * @param string $domain
	 *
	 * @return string
	 */
	protected function _formatDomain($domain) {
		if (array_key_exists($domain, $this->_domainsMap)) {
			return $this->_domainsMap[$domain];
		}
		if (!preg_match('#http#', $domain)) {
			$domain = 'http://' . $domain;
		}
		$url = parse_url($domain);
		return $url['host'];
	}

	/**
	 * @param array $shop
	 *
	 * @return null|string
	 */
	protected function _formatAddress(array $shop) {
		$city = isset($shop['hiLocation']) ? $shop['hiLocation'] : '';
		$country = isset($shop['hiCountry']) ? $shop['hiCountry'] : '';
		if ($country) {
			$stm = $this->model->execute('SELECT * FROM gsp_shopinfoCountry WHERE id = ?', [$country]);
			$row = $this->model->fetch($stm);
			$country = $row['val'];
		}
		$address = [
			$shop['hiTitle'],
			$shop['hiStreet'],
			$shop['hiZipcode'] . ' ' . $city,
			$country,
		];
		$address = array_filter($address);
		return rtrim(implode(PHP_EOL, $address));
	}

	/**
	 * @param array $shop
	 *
	 * @return null|string
	 */
	protected function _formatContact(array $shop) {
		$contact = [
			$shop['hiHotline'],
			$shop['hiHotlineE']
		];
		$contact = array_filter($contact);
		return rtrim(implode(PHP_EOL, $contact));
	}

	/**
	 * @param array $shop
	 *
	 * @return array|null
	 */
	protected function _formatPaymentMethods($shop) {
		if (empty($shop['hiPayment'])) {
			return null;
		}
		$return['_ids'] = [];
		$paymentMethods = explode(',', $shop['hiPayment']);
		foreach ($paymentMethods as $paymentMethodId) {
			if (empty($this->_paymentMethodsMap[$paymentMethodId])) {
				continue;
			}
			$return['_ids'][] = $this->_paymentMethodsMap[$paymentMethodId];
		}
		return $return;
	}

	/**
	 * @param array $shop
	 *
	 * @return array|null
	 */
	protected function _formatDeliveryMethods($shop) {
		if (empty($shop['hiShippingFrom'])) {
			return null;
		}
		$return['_ids'] = [];
		$deliveryMethods = explode(',', $shop['hiShippingFrom']);
		foreach ($deliveryMethods as $deliveryMethodId) {
			if (empty($this->_deliveryMethodsMap[$deliveryMethodId])) {
				continue;
			}
			$return['_ids'][] = $this->_deliveryMethodsMap[$deliveryMethodId];
		}
		return $return;
	}

	public function putCqId($cqId) {
		$shop = new Shop();
		$shop->putCqId($this->id, $cqId, $this->partner);
	}
}

?>