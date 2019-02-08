<?php

require("sites.php");

$input = $_GET['part'];

foreach ($sites as $site) {
	$query = $site->inputQuery($input);
	
	if (is_null($query) == false && strlen($query) > 0) {
		if (is_null($site->suggestions) == false) {
			siteSuggestion($site, $input, $query, $site->key);
		} else {
			siteSuggestion($defaultSite, $input, $query, $site->key);
		}
	}
}

if (is_null($defaultSite->suggestions) == false) {
	siteSuggestion($defaultSite, $input, $input, null);
}


function siteSuggestion($site, $input, $query, $key) {
	$patternsUrl = $site->suggestions->suggestionsUrl($query);
	
	$data = curlQuery($patternsUrl);
	
	$suggestions = $site->suggestions->parseSuggestionsResponse($data);
	
	if (is_null($key) == false) {
		$suggestions = array_map(function ($suggestion) use ($key) {
			return "$key $suggestion";
		}, $suggestions);
	}
	
	print(json_encode([$input, $suggestions]));
	exit;
}

function curlQuery($url) {
	$ch = curl_init();

	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_HTTPHEADER, [
		"User-agent" => "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_6) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/12.0 Safari/605.1.15"
	]);

	$data = curl_exec($ch);

	curl_close($ch);
	
	return $data;
}
