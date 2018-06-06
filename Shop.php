<?php

class Shop {
	public function putCgId($externId, $cqId) {
		$model = Model::getInstance('live', Config::$connection_data_live);
		$model->execute('UPDATE gsp_shops SET cq_id = ? WHERE extern_id = ?', [$cqId, $externId]);
	}

	public function findCqId($externId) {
		$model = Model::getInstance('live', Config::$connection_data_live);
		$stm = $model->execute('SELECT cq_id FROM gsp_shops WHERE extern_id = ?', [$externId]);
		if ($shop = $model->fetch($stm)) {
			return $shop['cq_id'];
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