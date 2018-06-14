<?php

class Post {
	public function putCqId($postId, $cqId, $partner) {
		$model = Model::getInstance('live', Config::$connection_data_live);
		if ($this->partner === '17d2cb9b') {
        	$model->execute('UPDATE onet_blog_posts SET onet_cq_id = ? WHERE ID = ?', [$cqId, $postId]);
        } else {
			$model->execute('UPDATE magazyn_posts SET cq_id = ? WHERE ID = ?', [$cqId, $postId]);
		}
	}
}

?>
