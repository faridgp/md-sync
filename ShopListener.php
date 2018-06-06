<?php
class ShopListener extends AbstractSyncListener {
	protected $_table = 'gsp_shops';
	protected $_endpoint = 'shops';
	protected $_categoriesMap = [
		10083 => 28,
		10059 => 2,
		10072 => 3,
		10092 => 3,
		10093 => 3,
		10001 => 4,
		10027 => 4,
		10029 => 4,
		10028 => 4,
		10026 => 4,
		10065 => 29,
		10012 => 30,
		10030 => 30,
		10075 => 5,
		10007 => 8,
		10037 => 8,
		10091 => 8,
		10038 => 8,
		10040 => 12,
		10041 => 12,
		10096 => 12,
		10071 => 12,
		10042 => 12,
		10044 => 31,
		10098 => 31,
		10097 => 31,
		10013 => 19,
		10079 => 10,
		10087 => 10,
		10089 => 10,
		10004 => 10,
		10032 => 11,
		10033 => 11,
		10080 => 11,
		10061 => 15,
		10101 => 15,
		10090 => 15,
		10002 => 24,
		10100 => 24,
		10046 => 24,
		10099 => 24,
		10088 => 27,
		10003 => 1,
		10050 => 1,
		10051 => 1,
		10077 => 1,
		10103 => 1,
		10112 => 1,
		10039 => 33,
		10094 => 33,
		10109 => 33,
		10049 => 35,
		10009 => 7,
		10008 => 7,
		10005 => 27,
		10078 => 27,
		10104 => 27,
		10066 => 27,
		10056 => 27,
		10073 => 14,
		10062 => 25,
		10107 => 25,
		10106 => 25,
		10105 => 25,
		10011 => 26,
		10055 => 20,
		10074 => 34,
		// UNSORTED
		10014 => 36,
		10063 => 36,
		10067 => 36,
		10068 => 36,
		10070 => 36,
		10084 => 36,
		10085 => 36,
		10086 => 36,
		// MISSING RELATION
		10045 => 36,
		10060 => 36,
		10076 => 36,
		10036 => 36,
		10057 => 36,
		10082 => 36,
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

	protected function _findRecords() {
		$query = "SELECT extern_id id FROM " . $this->_table . " WHERE ch_f1 != '' ORDER BY cq_id ASC";
		$stm = $this->model->execute($query);
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
		if ($data = $this->model->fetch($stm)) {
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
		if (empty($record['cq_id'])) {
			$data += [
				'name' => $textProcessor->decode($record['ch_f1']),
				'slug' => trim(strtolower($record['ch_f4'])),
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
		return preg_replace('#\/$#', '', $domain);
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

	public function putCgId($cqId) {
		$shop = new Shop();
		$shop->putCgId($this->id, $cqId);
	}
}

?>