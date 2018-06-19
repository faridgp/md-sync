<?php
header("Content-Type: text/html; charset=utf-8");
date_default_timezone_set("Europe/Berlin");

error_reporting(E_ALL ^ E_NOTICE);

require 'include.php';

if (empty($argv[1]) || !in_array($argv[1], ['shop', 'offer', 'post'])) {
	//die('No valid endpoint found. Please try with "shop" or "offer" or "post" as valid endpoint');
}

 if (!empty($_GET['func']) && $_GET['func'] === 'not_synced_shops') {
 	$model = Model::getInstance('live', Config::$connection_data_live);
	$stm = $model->execute("SELECT ch_f1, extern_id FROM gsp_shops WHERE ch_f1 != '' AND NOT ISNULL(ch_f1) AND ISNULL(cq_id) ORDER BY ch_f1");
	$data = $model->fetchAll($stm);
	echo '<table><tr><th>Name</th><th>Extern_id</th></tr>';
	foreach ($data as $s) {
		echo '<tr><td>' . $s['ch_f1'] . '</td><td>' . $s['extern_id'] . '</td></tr>';
	}
	echo '</table>';
	exit;
 }

  if (!empty($_GET['func']) && $_GET['func'] === 'synced_shops') {
 	$model = Model::getInstance('live', Config::$connection_data_live);
	$stm = $model->execute("SELECT ch_f1, extern_id, cq_id FROM gsp_shops WHERE ch_f1 != '' AND NOT ISNULL(ch_f1) AND NOT ISNULL(cq_id) ORDER BY ch_f1");
	$data = $model->fetchAll($stm);
	echo '<table><tr><th>Name</th><th>Extern_id</th><th>cq_id</th></tr>';
	foreach ($data as $s) {
		echo '<tr><td>' . $s['ch_f1'] . '</td><td>' . $s['extern_id'] . '</td><td>' . $s['cq_id'] . '</td></tr>';
	}
	echo '</table>';
	exit;
 }

$partner = '';
if (!empty($argv[3]) || !empty($_GET['partner'])) {
   	$partner = $_GET['partner'];
}

if ($argv[1] === 'cq_id' || (!empty($_GET['cq_id']) && $_GET['cq_id'] === 'del')) {
	$model = Model::getInstance('live', Config::$connection_data_live);
	$model->execute("UPDATE gsp_shops SET cq_id = null, onet_cq_id = null");
	$model->execute("UPDATE gsp_angebot SET cq_id = null, onet_cq_id = null");
	$model->execute("UPDATE magazyn_posts SET cq_id = null");
	$model->execute("UPDATE onet_blog_posts SET onet_cq_id = null");
}
if ($argv[1] === 'shop' || (!empty($_GET['object']) && $_GET['object'] === 'shop')) {
	$event = new ShopListener($partner);
} elseif ($argv[1] === 'offer' || (!empty($_GET['object']) && $_GET['object'] === 'offer')) {
	$event = new OfferListener($partner);
} elseif ($argv[1] === 'post' || (!empty($_GET['object']) && $_GET['object'] === 'post')) {
	$event = new PostListener($partner);
}


if (!empty($argv[2]) && $argv[2] !== 'all' && (int)$argv[2] > 0 || (!empty($_GET['sync']) && $_GET['sync'] != 'all' && (int)$_GET['sync'] > 0)) {
	$event->id = (!empty($argv[2]) && $argv[2] !== 'all' && (int)$argv[2] > 0 ? $argv[2] : $_GET['sync']);
	$event->synchronize();
} elseif (!empty($argv[2]) && $argv[2] === 'all'  || (!empty($_GET['sync']) && $_GET['sync'] === 'all')) {
	$event->synchronizeAll();
}
?>