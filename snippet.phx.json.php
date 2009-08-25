<?php

// Initialize things
$debug = '';

if (preg_match('%^\{%i', $output)) {
	$json = json_decode($output, true);
	$debug .= 'is json';
} else {
	$json = base64_decode($output);
	$debug .= 'raw json: ' . $json;
	$json = json_decode($json, true);
	$debug .= 'is base64';
}

$json = $json[key($json)];
$output = '';
$debug .= "\nJSON: " . print_r($json, true);
// return $debug;

/*
	=====================================================
	OPTIONS:
	length	returns the length of the JSON array
	field	returns the value of the first "field" node in the JSON array
*/


/*
	=====================================================
	HELPER FUNCTIONS
*/
if (!function_exists(quote_fix)) {
	function quote_fix(&$item, $key) {
		$item = stripslashes($item);
	}
}


/*
	=====================================================
	MEAT & POTATOES
*/

// =====================================================
// Return false if no options set
if (!$options) {return false;}

// =====================================================
// Return the array length if "length" set
if ($options == 'length') {
	// $output .= print_r($json, true); // for debugging
	return count($json);
}


// =====================================================
// Do some magic if options are set

// Read the PHx options into an array
$options = str_replace('::', '=', $options);
$options = str_replace('||', '&', $options);
$options = str_replace('++', '~~', $options); // Plusses get stripped so convert them to something else
$options_phx = array();
parse_str($options, $options_phx);
$debug .= "\n options_phx: " . print_r($options_phx, true);


// FIELD?
// Return the first field in the array
if (isset($options_phx['field'])) {
	// $debug .= 'json field: ' . $options_phx['field'] . print_r($options_phx);
	// $debug .= "\nJSON options: " . print_r()
	// return $debug;
	return $json[0][$options_phx['field']];
}



// Set some values
$display = (isset($options_phx['display'])) ? $options_phx['display'] : null;
if (isset($options_phx['start']) && is_numeric($options_phx['start'])) {
	$start = $options_phx['start'];
	// $debug .= 'Start is set';
} else {
	$start = null;
	// $debug .= 'Start is null';
}

if ($start) {
	$json = array_slice($json, $start - 1, $display, true);
	// $debug .= 'Start is true: ' . print_r($json, true);
} elseif ($display) {
	$json = array_slice($json, 0, $display, true);
	// $debug .= 'Display is true: ' . print_r($json, true);
}

// Fix the magic quotes, if it's an issue
if (get_magic_quotes_gpc()) {
	array_walk($options_phx, 'quote_fix');
}

// Replace the placeholders with the values from the JSON
preg_match_all('%~~([a-z0-9-_]+)~~%i', $options_phx['@CODE'], $replacements);
foreach($json as $arr) {
	if (is_array($arr)) {
		$output_temp = $options_phx['@CODE'];
		foreach($arr as $key => $value) {
			// $debug .= "Foreach, internal: " . print_r($arr, true);
			$output_temp = str_replace('~~' . $key . '~~', $value, $output_temp);
		}
		$output .= $output_temp;
	}
}
// =====================================================




/*
	OUTPUT
*/
// return $debug;
return $output;


?>