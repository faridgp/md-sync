<?php
class PostListener extends AbstractSyncListener {
	protected $_prefix = 'magazyn';
	protected $_endpoint = 'posts';
	protected $_categoriesMap = [
		1 => 43,
		18 => 44,
		25 => 45,
		29 => 46,
		40 => 47,
		51 => 48,
		56 => 49,
		61 => 50,
		84 => 51,
		674 => 52,
		698 => 53,
		803 => 54
	];

	/**
	 * @var array
	 */
	protected $_usersMap = [
		 12 => 1, //Andrzej Szwato?ski
		 18 => 1, // Barbara Ni?y?ska
		 23 => 1, // Ewa Gizewska
		 28 => 1, // Krzysztof Nalepa
		 26 => 1, // ?ucja Stachura
		 27 => 1, // Ma?gorzata Mrozek
		 //=> 23, // Julia Müller
		 //=> 24, // Melanie Werner
		 //=> 25, // Julian Daquila
		 //=> 26, // Anja Fujawa
		24 => 3, // Altan Sarisin
		 //=> 27, // Maria Wolff
		 //=> 28, // Julius Hilker
		 //=> 29, // Claudia Obritzhauser
		 //=> 30, // Margarete Schücking
		 //=> 31, // Aileen Rost
		 //=> 1, // Laura ??? => Admin
		 //=> 1, // Wenzel ??? => Admin
		 //=> 1, // Henry ??? => Admin
		 //=> 1, // Sarah ??? => Admin
		 //=> 1, // Arlidna ??? => Admin
		 //=> 6, // Tina Rodriguez
		 //=> 32, // Jennifer Dühnfort
		 //=> 33, // Sibylle Ploch
		 //=> 34, // Michaela Niemeyer
		 //=> 1, // Mario ??? => Admin
		 //=> 1, // Annika ??? => Admin
		 //=> 1, // Birgit ??? => Admin
		 //=> 38, // Isabel Herwig
		 //=> 39, // Daniel Becker
		 //=> 40, // Fabian Kuhn
	];

    public function __construct(){
        parent::__construct();

    }

    protected function _findRecord()
	{
		if ($this->partner === '17d2cb9b') {
        	$this->_prefix = 'onet_blog';
        }
		$query = 'select
					ID id,
					post_title,
					post_excerpt,
					post_content,
					post_name,
					post_author,
					post_modified,
					post_date,
					post_status,
					cq_id,
					(
						SELECT
							' . $this->_prefix . '_terms.term_id
					    FROM
					    	' . $this->_prefix . '_terms
					    INNER JOIN
					    	' . $this->_prefix . '_term_taxonomy
					   	ON ' . $this->_prefix . '_terms.term_id = ' . $this->_prefix . '_term_taxonomy.term_id
					    INNER JOIN
					    	' . $this->_prefix . '_term_relationships
					    ON ' . $this->_prefix . '_term_relationships.term_taxonomy_id = ' . $this->_prefix . '_term_taxonomy.term_taxonomy_id
					    WHERE
					    	taxonomy= "category" AND
					    	' . $this->_prefix . '_term_relationships.object_id = ' . $this->id . '
						LIMIT 1
					) AS "post_category_id",
					(
						SELECT
							GROUP_CONCAT(' . $this->_prefix . '_terms.name SEPARATOR ", ")
					    FROM
					    	' . $this->_prefix . '_terms
					    INNER JOIN
					    	' . $this->_prefix . '_term_taxonomy
					    ON ' . $this->_prefix . '_terms.term_id = ' . $this->_prefix . '_term_taxonomy.term_id
					    INNER JOIN
					    	' . $this->_prefix . '_term_relationships
					    ON ' . $this->_prefix . '_term_relationships.term_taxonomy_id = ' . $this->_prefix . '_term_taxonomy.term_taxonomy_id
					    WHERE
					    	taxonomy= "post_tag" AND
					    	' . $this->_prefix . '_posts.ID = ' . $this->_prefix . '_term_relationships.object_id
					) AS "Tags",
					(
						SELECT
							meta_value
						FROM
							' . $this->_prefix . '_postmeta
						WHERE
							' . $this->_prefix . '_postmeta.meta_key = "_aioseop_title" AND
							' . $this->_prefix . '_postmeta.post_id = ' . $this->_prefix . '_posts.ID
					) AS aiosp_title,
					(
						SELECT
							meta_value
						FROM
							' . $this->_prefix . '_postmeta
						WHERE
							' . $this->_prefix . '_postmeta.meta_key = "_yoast_wpseo_metadesc" AND
							' . $this->_prefix . '_postmeta.post_id = ' . $this->_prefix . '_posts.ID
					) AS yoast_wpseo_metadesc,
        			(
						SELECT
							meta_value
						FROM
							' . $this->_prefix . '_postmeta
						WHERE
							' . $this->_prefix . '_postmeta.meta_key = "_aioseop_description" AND
							' . $this->_prefix . '_postmeta.post_id = ' . $this->_prefix . '_posts.ID
					) AS aioseop_description,
					(
						' .
						($this->_prefix === 'magazyn' ?
						'SELECT
							p.guid
						FROM
							magazyn_posts p
						WHERE
							p.post_type = "attachment" AND
							p.post_mime_type like "image%" AND
							p.post_parent = magazyn_posts.ID
						ORDER BY
							p.ID DESC
						LIMIT 1'
						:

						'SELECT
							p.guid
						FROM
							' . $this->_prefix . '_posts p
						LEFT JOIN
							' . $this->_prefix . '_postmeta pm
							ON pm.meta_value = p.ID
						WHERE
							pm.post_id = ' . $this->_prefix . '_posts.ID AND
							pm.meta_key = "_thumbnail_id"
						') . '
					) AS image_url
				from
					' . $this->_prefix . '_posts
				JOIN
			    	' . $this->_prefix . '_term_relationships
			     ON ' . $this->_prefix . '_posts.ID = ' . $this->_prefix . '_term_relationships.object_id
			     JOIN
			     	' . $this->_prefix . '_term_taxonomy
			     ON ' . $this->_prefix . '_term_relationships.term_taxonomy_id = ' . $this->_prefix . '_term_taxonomy.term_taxonomy_id
			     JOIN
			     	' . $this->_prefix . '_terms
			     ON ' . $this->_prefix . '_term_taxonomy.term_id = ' . $this->_prefix . '_terms.term_id
				where
					post_type = "post" AND
					' . $this->_prefix . '_term_taxonomy.taxonomy= "category" AND
					(ID="' . $this->id . '")';

        $stm = $this->model->execute($query, [$this->id]);
        if ($data = $this->model->fetch($stm)) {
        	return $data;
        }
        return null;
	}

   	protected function _findRecords()
	{
        if ($this->partner === '17d2cb9b') {
        	$this->_prefix = 'onet_blog';
        }
        $query = 'select
        			ID id
				from
					' . $this->_prefix . '_posts
				where
					post_type = "post"';
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
		$excerpt = strip_tags($textProcessor->process($record['post_excerpt']));
		if (empty($this->_categoriesMap[$record['post_category_id']])) {			return null;
		}

		$data = [
			'post_category_id' => $this->_categoriesMap[$record['post_category_id']],
			'user_id' => $this->_usersMap[$record['post_author']],
			'title' => $textProcessor->decode($record['post_title']),
			'subtitle' => $textProcessor->decode($record['post_title']),
			'excerpt' => $excerpt?: '__MISSING_EXCERPT__',
			'content' => $textProcessor->process($record['post_content']),
			'slug' => $record['post_name'],
			'published' => $datetimeProcessor->process($record['post_date'], false),
			'image_url' => $urlBuilder->getImageUrl($record['image_url']),
			'meta_title' => $textProcessor->decode($record['aiosp_title'] ?: $record['post_title']),
			'meta_description' => $textProcessor->decode(($record['aioseop_description'] ?: $record['yoast_wpseo_metadesc'])),
			'is_active' => ($record['post_status'] === 'publish' ? true : false),
			'created' => $datetimeProcessor->process($record['post_date'], false),
			'inheritance' => [
				'legacy_tanio_id' => $record['id'],
			],
		];
		return $data;
	}

	public function putCqId($cqId) {
		$post = new Post();
		$post->putCqId($this->id, $cqId, $this->partner);
	}
}


?>