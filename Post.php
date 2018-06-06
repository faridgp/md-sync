<?php

class Post {
	public function putCgId($postId, $cqId) {
		$model = Model::getInstance('live', Config::$connection_data_live);
		$model->execute('UPDATE magazyn_posts SET cq_id = ? WHERE ID = ?', [$cqId, $postId]);
	}
}

?>
