<?php

class Shop {	public function putCgId($externId, $cqId, $partner) {
		$model = Model::getInstance('live', Config::$connection_data_live);
		if ($partner === '17d2cb9b') {			$model->execute('UPDATE gsp_shops SET onet_cq_id = ? WHERE extern_id = ?', [$cqId, $externId]);
		} else {
			$model->execute('UPDATE gsp_shops SET cq_id = ? WHERE extern_id = ?', [$cqId, $externId]);
		}
	}

	public function findCqId($externId, $partner) {		$model = Model::getInstance('live', Config::$connection_data_live);
		$stm = $model->execute('SELECT cq_id, onet_cq_id FROM gsp_shops WHERE extern_id = ?', [$externId]);
		if ($shop = $model->fetch($stm)) {			if ($partner === '17d2cb9b') {				 return $shop['onet_cq_id'];
			}			return $shop['cq_id'];
		}
		return null;
	}

	public function findMinimumOrderValue($externId) {
		$model = Model::getInstance('live', Config::$connection_data_live);
		$stm = $model->execute('SELECT mbw_long FROM gsp_shops WHERE extern_id = ?', [$externId]);
		if ($shop = $model->fetch($stm)) {
			return (!empty($shop['mbw_long']) ? $shop['mbw_long'] : null);
		}
		return null;
	}
}

?>
