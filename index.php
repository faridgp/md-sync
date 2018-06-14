<?php
header("Content-Type: text/html; charset=utf-8");
date_default_timezone_set("Europe/Berlin");

error_reporting(E_ALL ^ E_NOTICE);

require 'include.php';

if (empty($argv[1]) || !in_array($argv[1], ['shop', 'offer', 'post'])) {
	//die('No valid endpoint found. Please try with "shop" or "offer" or "post" as valid endpoint');
}

if ($argv[1] === 'cq_id' || (!empty($_GET['cq_id']) && $_GET['cq_id'] === 'del')) {
	$model = Model::getInstance('live', Config::$connection_data_live);	$model->execute("UPDATE gsp_shops SET cq_id = null, onet_cq_id = null");
	$model->execute("UPDATE gsp_angebot SET cq_id = null, onet_cq_id = null");
	$model->execute("UPDATE magazyn_posts SET cq_id = null");
	$model->execute("UPDATE onet_blog_posts SET onet_cq_id = null");
}
if ($argv[1] === 'shop' || (!empty($_GET['object']) && $_GET['object'] === 'shop')) {
   	$event = new ShopListener();
} elseif ($argv[1] === 'offer' || (!empty($_GET['object']) && $_GET['object'] === 'offer')) {
   	$event = new OfferListener();
} elseif ($argv[1] === 'post' || (!empty($_GET['object']) && $_GET['object'] === 'post')) {
   	$event = new PostListener();
}

if (!empty($argv[3]) || !empty($_GET['partner'])) {
   	$event->partner = $_GET['partner'];
}

if (!empty($argv[2]) && $argv[2] !== 'all' && (int)$argv[2] > 0 || (!empty($_GET['sync']) && $_GET['sync'] != 'all' && (int)$_GET['sync'] > 0)) {
   	$event->id = (!empty($argv[2]) && $argv[2] !== 'all' && (int)$argv[2] > 0 ? $argv[2] : $_GET['sync']);
	$event->synchronize();
} elseif (!empty($argv[2]) && $argv[2] === 'all'  || (!empty($_GET['sync']) && $_GET['sync'] === 'all')) {	$event->synchronizeAll();
}



?>