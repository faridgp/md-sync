<?php
class UrlBuilder {
	protected $hosts = [
		'17d2cb9b' => 'https://kodyrabatowe.onet.pl/',
		'0123456789' => 'https://www.tanio.co/',
		'd9eaee41' => 'http://kupony.mydeal.pl/',
		'mk7543f6' => 'https://kupony.dlastudenta.pl/'
	];

	public function getHost($partnerId = '') {
		if (!empty($this->hosts[$partnerId])) {
			return $this->hosts[$partnerId];
		}
		return '';
	}

	public function getShopDetailsUrl($shopSlug, $partnerId = '') {
		$shopSlug = strtolower(trim($shopSlug));
		if (!preg_match('/sklep\//', $shopSlug) && in_array($partnerId, [Config::PARTNER_MYDEAL, Config::PARTNER_WP_PL])) {
			$shopSlug = sprintf('sklep/%s', $shopSlug);
		} elseif (!preg_match('/kodyrabatowe\//', $shopSlug) && in_array($partnerId, [Config::PARTNER_ONET])) {
			$shopSlug = sprintf('kodyrabatowe/%s', $shopSlug);
		}
		return $this->getHost($partnerId) . $shopSlug;

	}

	public function getCategoryUrl($categorySlug, $subCategorySlug = '', $partnerId = '') {
		$categorySlug = strtolower(trim($categorySlug));
		$subCategorySlug = strtolower(trim($subCategorySlug));
		if ($subCategorySlug) {
			$categorySlug = sprintf('%s/%s', $categorySlug, $subCategorySlug);
		}
		if (!$partnerId || in_array($partnerId, [Config::PARTNER_TANIO])) {
			$categorySlug = sprintf('kategorie/%s', $categorySlug);
		}
		return $this->getHost($partnerId) . $categorySlug;
	}

	/**
	 * @param string $url
	 *
	 * @return string|null
	 */
	public function getValidUrl($url, $addWww = false) {
		$url = trim($url);
		if (empty($url)) {
			return null;
		}
		if (strpos($url, 'http') !== 0) {
			if ($addWww && strpos($url, 'www') !== 0) {
				$url = sprintf('www.%s', $url);
			}
			return sprintf('http://%s', $url);
		}
		return $url;
	}

	/**
	 * @param array $image
	 *
	 * @return string|null
	 */
	public function getImageUrl($image) {
		if (!$image) {
			return null;
		}
		if (strpos($image, 'i.tanio')) {
			return $image;
		}
		if (strpos($image, 'static/images')) {
			return str_replace("../static/images/", Config::$staticUrlImages, $image);
		}
		return sprintf('%s%s', Config::$staticUrlImages, $image);
	}
}

?>