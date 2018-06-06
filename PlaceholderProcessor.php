<?php

class PlaceholderProcessor {
	protected $plMonths = [
		'Styczeń',
		'Luty',
		'Marzec',
		'Kwiecień',
		'Maj',
		'Czerwiec',
		'Lipiec',
		'Sierpień',
		'Wrzesień',
		'Październik',
		'Listopad',
		'Grudzień'
	];

	protected $model = null;
	protected $functions = null;
	protected $urlBuilder = null;

	/**
	 * @param string $text
	 * @param array $record
	 *
	 * @return string
	 */
	public function process($text, array $record = []) {
		$this->model = Model::getInstance('live', Config::$connection_data_live);
		$this->urlBuilder = new UrlBuilder();
		$text = $this->processPlaceholders($text, $record);
		$text = $this->processAddImages($text, $record);
		$text = $this->processDates($text);
		$text = $this->processVariables($text, $record);
		$text = $this->processLists($text);
		return $text;
	}

	/**
	 * @param string $text
	 *
	 * @return string
	 */
	public function processDates($text) {
		return str_replace([
			'##DATUM##',
			'##MIESIAC##',
			'##MIESIĄC##',
			'##ROK##'
		], [
			$this->plMonths[((int)@date('m')-1)] . ' ' . @date('Y'),
			$this->plMonths[((int)@date('m')-1)],
			$this->plMonths[((int)@date('m')-1)],
			@date('Y')
		], $text);
	}

	public function processPlaceholders($text, array $record = []) {
		if (!$record) {
			return $text;
		}
		$url = $this->urlBuilder->getValidUrl($record['ch_f5'], true);
		$text = str_replace(array('##NAME##', '##SKLEP##'), [$record['ch_f1'], $record['ch_f1']], $text);

		$videoStart = strpos($text, '##VIDEO##[');
		if($videoStart !== false){
			$videoEnd = strpos($text, ']##VIDEO##');
			$code = substr($text, $videoStart + 10, $videoEnd - $videoStart - 10 );
			$text = str_replace('##VIDEO##['.$code.']##VIDEO##', '<iframe width="538" height="303" src="http://www.youtube.com/embed/'.$code.'?rel=0&wmode=transparent" frameborder="0" allowfullscreen></iframe>', $text);
		}
		$value = strpos($text, '##WARTOŚĆ##');

		if(preg_match('/\#\#WARTOŚĆ\#\#/', $text)){
			$offer = new Offer();
			$maxValue = $offer->getMaxValueByShop($record['extern_id']);
			$text = str_replace('##WARTOŚĆ##', sprintf('%s%s %s', $maxValue['value'], $maxValue['unit'], $maxValue['lable']), $text);
		}
		return $text;
	}

	public function processAddImages($text, array $record = []) {
		if (!$record) {
			return $text;
		}
		$graphics = json_decode($record['graphics']);

		$images = [];
		$search = [];
		for($i = 1; $i <= 27; $i++){
			if($graphics->$i){
				$images[] = "<img src='" . $this->urlBuilder->getImageUrl($graphics->$i) . "'>";
				$search[] = "##BILD$i##";
			}
		}
		$text = str_replace($search, $images, $text);
		return $text;
	}

	/**
	 * @param string $text
	 * @param array $record
	 *
	 * @return string
	 */
	public function processVariables($text, array $record = []) {
		if (!$record) {
			return $text;
		}
		$preg_match_all = preg_match_all('|<a.*\s+href=[\"\']([^\'\"]*)[\'\"].*>.*</a>|Ui', $text, $linktext, PREG_PATTERN_ORDER);

		foreach($linktext[0] as $k => $v){
			$replace = '';
			if(preg_match('/s/', $linktext[1][$k])){
				$query = 'SELECT lower(ch_f4) slug FROM gsp_shops WHERE extern_id=?';
				$stm = $this->model->execute($query, array(str_replace('s', '', $linktext[1][$k])));
				$row = $this->model->fetch($stm);
				if(!empty($row['slug'])){
					$replace = $this->urlBuilder->getShopDetailsUrl($row['slug'], Config::PARTNER_TANIO);
				}
		} else if(preg_match('/c/', $linktext[1][$k])){
			$query = 'SELECT lower(ch_f2) slug, ch_f5 parent_category_id FROM gsp_categories WHERE extern_id=?';
			$stm = $this->model->execute($query, array(str_replace('c', '', $linktext[1][$k])));
			$row = $this->model->fetch($stm);
			if(!empty($row['parent_category_id'])){
				$query = 'SELECT lower(ch_f2) slug FROM gsp_categories WHERE extern_id=?';
				$stm2 = $this->model->execute($query, array($row['parent_category_id']));
				$row2 = $this->model->fetch($stm2);
				if(!empty($row2['slug'])){
					$replace = $this->urlBuilder->getCategoryUrl($row2['slug'], $row['slug'], Config::PARTNER_TANIO);
				}
				} else {
					$replace = $this->urlBuilder->getCategoryUrl($row['slug'], '', Config::PARTNER_TANIO);
				}
			}

			if($replace){
				$text = str_replace($linktext[1][$k], $replace, $text);
			}
		}
		return $text;
	}

	public function processLists($text) {
		if (!preg_match('/\#\#/', $text)) {
			return $text;
		}
		return '<ul><li style="display:none">' . str_replace('##', '</li><li>', strip_tags($text)) . '</li></ul>';
	}
}

?>