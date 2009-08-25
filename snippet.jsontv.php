<?php

/*
	PARAMETERS =====================================================
	json	JSON. Should be base64 encoded to work around the line break issues with TV params
	start	Position in the JSON array that the output should start at
	display	Number of nodes in the JSON array to output
	tpl		Chunk name for the output
*/


// Initialize the JSON as an array
// Detect if it's base64 encoded or not
if (!isset($json)) {
	return false;
} elseif (preg_match('%^\{%i', $json)) {
	$json = json_decode($output, true);
} else {
	$json = base64_decode($json);
	$json = json_decode($json, true);
}


// Set the rest
$display = (isset($display)) ? $display : null;

if (!isset($start) && !is_numeric($start)) {
	$start = null;
}



// More initialization
$json = $json[key($json)];
$debug = '';
$output = "\n";


// If no template is set, just return the array, raw
if (!$tpl) {
	return print_r($json, true);
}




/*
	=====================================================
	MEAT & POTATOES
*/

// =====================================================
// Do some magic if options are set

if ($start) {
	$json = array_slice($json, $start - 1, $display, true);
	$debug .= 'Start is true: ' . print_r($json, true);
} elseif ($display) {
	$json = array_slice($json, 0, $display, true);
	$debug .= 'Display is true: ' . print_r($json, true);
}

// Replace the placeholders with the values from the JSON
foreach($json as $arr) {
	if (is_array($arr)) {
		$output_temp = $modx->getChunk($tpl);
		// Replace regular placeholders
		// $output_temp = $modx->parseChunk($tpl, $arr, '[+', '+]');
		// Manual placeholder replacements, to allow for PHx modifiers on placeholders
		foreach($arr as $key => $value) {
			$debug .= "Foreach, internal: " . print_r($arr, true);
			$output_temp = str_replace('[+' . $key . ':', '[+phx:input=`' . $value . '`:', $output_temp);
		}
		foreach($arr as $key => $value) {
			$debug .= "Foreach, internal: " . print_r($arr, true);
			$output_temp = str_replace('[+' . $key . '+]', $value, $output_temp);
		}
		// Append the new chunk to the $output
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