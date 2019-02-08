<?php

// $_GET['part'] = $_GET['text']; require("suggest.php"); exit;

require("sites.php");

if (isset($_GET['text']) == false) {
	header("Location: " . $defaultSite->queryUrl());
	exit;
}

$input = trim($_GET['text']);

foreach ($sites as $site) {
	$location = $site->inputUrl($input);
	
	if (is_null($location) == false) {
		header("Location: " . $location);
		exit;
	}
}

$location = $defaultSite->queryUrl($input);
header("Location: " . $location);
