<?php
abstract class AbstractSyncListener {

	protected $_token = 'sCJOuLzksiHieEQVktovNH0xyJSipfFY';
	protected $_baseUrl = 'http://md-conqueror-01.menschdanke.io/api/v1';
	public $id = 0;
	public $partner = '0123456789';
	protected $_cq_id = 0;
	protected $request;
	protected $model;
	protected $_shopExcludeIds = [];
	protected $_offerExcludeIds = [];

	public function __construct() {
		$this->model = Model::getInstance('live', Config::$connection_data_live);
	}

	public function synchronizeAll() {
		$records = $this->_findRecords();
		foreach ($records as $record) {
			$this->id = $record['id'];
			$this->synchronize();
		}
	}

	public function synchronize() {
		$record = $this->_findRecord();
		if ($record === null) {
			return [
				'success' => false,
				'message' => 'Record could not be found or has invalid data.',
			];
		}

		$request = $this->_buildRequest($record);
		if (empty($request['body'])) {
			print_r([
				'success' => false,
				'message' => 'No data found in body: possibly because the shop [' . $record['ch_f5'] . '] has no cq_id',
				'id' => $this->id
			]);
			return;
		}

		$response = $this->_sendRequest($request);
		if (isset($response['success'])) {
			if ($response['success'] == 1) {
				if (!empty($response['data']['id'])) {
					$this->putCqId($response['data']['id']);
				}
				/*
				print_r([
					'success' => true,
					'result' => (empty($response['data']['id']) ? 'Updated' : 'Posted'),
					'id' => $this->id,
					'cq_id' => (empty($response['data']['id']) ? $record['cq_id'] : $response['data']['id'])
				]);
				 *
				 */
			} else {
				print_r([
					'success' => false,
					'message' => $response['data']['message'],
					'id' => $this->id
				]);
				echo '<br>';
				echo '<b>Errors:</b>';
				print_r($response['data']['errors']);
			}
		}
	}

	public function _sendRequest($request) {
		$rest = curl_init();
		curl_setopt($rest, CURLOPT_URL, $request['uri']);
		curl_setopt($rest, CURLOPT_HTTPHEADER,  $request['header']);
		curl_setopt($rest, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($rest, CURLOPT_CUSTOMREQUEST, $request['method']);
		curl_setopt($rest, CURLOPT_POSTFIELDS,http_build_query($request['body']));
		$response = curl_exec($rest);
		$http_code = curl_getinfo($rest, CURLINFO_HTTP_CODE);
		$error = curl_errno($rest);
		curl_close($rest);
		if($error){
			return null;
		}
		$response = json_decode($response, true);
		return $response;
	}

	/**
	 * @param array $record
	 *
	 * @return array
	 */
	protected function _buildRequest(array $record)
	{
		$request = [
			'header' => [
				'Time-Zone: Europe/Berlin',
				'X-Api-Token: ' . $this->_token,
				'Accept: application/json',
				'Content-Type: application/x-www-form-urlencoded'
			],
			'method' => $record['cq_id'] ? 'PUT' : 'POST',
			'body' => $this->_formatData($record),
		];
		$uri = sprintf('%s/%s', $this->_baseUrl, $this->_endpoint);
		if ($request['method'] === 'PUT') {
			$uri .= sprintf('/%s', $record['cq_id']);
		}
		$request['uri'] = $uri;
		return $request;
	}


	/**
	 * @param string $minimumOrderLabel
	 *
	 * @return integer
	 */
	protected function _formatMinimumOrderLabel($minimumOrderLabel)
	{
		if (empty($minimumOrderLabel)) {
			return 1;
		}
		return 2;
	}
}
?>