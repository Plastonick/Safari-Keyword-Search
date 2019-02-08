<?php

class SearchSite {
	public $key;
	private $pattern;
	private $default;
	
	public $suggestions;
	
	function __construct($json) {
		$this->key = $json->key;
		$this->pattern = $json->pattern;
		
		if (isset($json->default)) {
			$this->default = $json->default;
		} else {
			$this->default = $this->applyPattern("");
		}
		
		if (isset($json->suggest)) {
			$this->suggestions = new Suggestions($json->suggest);
		} else {
			$this->suggestions = null;
		}
	}
	
	private function applyPattern($query) {
		$url = str_replace([
			"@@@",
			"%%%"
		], [
			urlencode($query),
			rawurlencode($query)
		], $this->pattern);
		
		return $url;
	}
	
	public function inputQuery($input) {
		if ($input == $this->key) {
			return "";
		}
		
		$keyLength = strlen($this->key);
		if (substr($input, 0, $keyLength + 1) == "$this->key ") {
			return substr($input, $keyLength + 1);
		}
				
		return null;
	}
	
	public function inputUrl($input) {
		$query = $this->inputQuery($input);
		
		if (is_null($query)) {
			return null;
		}
		
		if ($query == "") {
			return $this->default;
		} else {
			return $this->applyPattern($query);
		}
	}
	
	public function queryUrl($query) {
		if (isset($query)) {
			return $this->applyPattern($query);
		} else {
			return $this->default;
		}
	}
}

class Suggestions {
	private $url;
	private $format;
	private $resultPath;
	
	function __construct($json) {
		$this->url = $json->url;
		$this->format = $json->format;
		$this->resultPath = $json->results;
	}
	
	public function suggestionsUrl($query) {
		$url = str_replace([
			"@@@",
			"%%%"
		], [
			urlencode($query),
			rawurlencode($query)
		], $this->url);
		
		return $url;
	}
	
	private function parseDataFormat($data) {
		switch ($this->format) {
			case "json":
				return json_decode($data);
			case "xml":
				return json_decode(json_encode(simplexml_load_string($data)));
		}
		
		return null;
	}
	
	public function parseSuggestionsResponse($data) {
		$results = [$this->parseDataFormat($data)];
		
		foreach ($this->resultPath as $path) {
			if (is_string($path)) {
				$results = array_map(function ($r) use ($path) { return $r->{$path}; }, $results);
			} else if (is_numeric($path)) {
				$results = array_map(function ($r) use ($path) { return $r[$path]; }, $results);
			} else if ($path === true) {
				$results = call_user_func_array("array_merge", $results);
			} else if (is_object($path)) {
				$filter = $path->filter;
				
				switch ($filter) {
					case "string": {
						$results = array_values(array_filter($results, function ($r) { return is_string($r); }));
					} break;
				}
			}
		}
		
		return $results;
	}
}