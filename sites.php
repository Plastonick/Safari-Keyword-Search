<?php

require("SearchSite.php");

$sites = [];

foreach (scandir("sites") as $jsonFile) {
	if (substr($jsonFile, 0, 1) == ".") continue;
	
	$info = pathinfo("sites/$jsonFile");
	if ($info['extension'] != "json") continue;
	
	$json = json_decode(file_get_contents("sites/$jsonFile"));
	$sites[$info['filename']] = new SearchSite($json);
}

$settings = json_decode(file_get_contents("settings.json"));

$defaultSite = $sites[$settings->default];