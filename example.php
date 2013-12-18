<?php

//
// There are many different ways to make HTTP requests with PHP. The appropriate solution will depend on your PHP version.
// in this example, we will use CURL by default.
// - have a look at lib/api.php for examples of how to make requests in other routes (e.g. if CURL is not available on your system)
//
// Please note that these examples have error reporting for most cases where an API call could possibly fail. This may be overkill for
// a practical application, but outlines what steps can be done to validate requests.
//
// Successful requests will return header code 200 OK, where as various errors will return various other codes. Errors will still return
// data containing information about the error, but you may encounter a boolean false result from the call instead of error data. In this
// case, e.g. with stream_context_create(), you will need to specify 'ignore_errors' => true.
//

require_once('lib/api.php');

// pass your API token into the constructor of iRESAPI class
$api = new iRESAPI('yourapitoken');

// For testing purposes: throw exception when header response code is not 200? 
// If this is false, output will be shown at the bottom of the page.
# $api->die_on_error = true;

// optional: define filtering options (refer to the docs)
$api->limit = 5;
# $api->order = 'Contact DESC';

// Define return format: default JSON
# $api->format = 'xml';

try {

	// --- CURL
	# $operators = $api->get_operators_curl();
	// --- PHP Streams
	$operators = $api->get_operators_stream('GET');
	
	// Note: - the PECL_HTTP extension must be loaded on your server for these methods to work
	// 	     - header response checks have not been added for these options, so you will need to add your own.
	// --- PECL_HTTP (procedural)
	# $operators = $api->get_operators_pecl();
	// --- PECL_HTTP (OO)
	# $operators = $api->get_operators_pecl_oo();

} catch (Exception $e) { ?>
	<pre><span style="color: red">API Error:</span>
<?=$e?>
<span style="color: red">Attempting header trace:</span>
<?php 
	print_r($api->response_headers);
	exit;
}

// Process the formatted results into PHP array:
switch(strtolower($api->format)) {
	case 'xml':
		$results = simplexml_load_string($operators); // convert XML results into an object
		break;
	case 'json':
	default:
		$results = json_decode($operators); // convert JSON results into an object
		break;
}
	
?>
<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>iRES API v1 - Example</title>
	<meta name="description" content="iRES API v1 - Example">
	<meta name="author" content="iRES">
</head>

<body>
	
	<h1>iRES API v1 - Example</h1>
	<p>This page shows you examples of how to connect to iRES API and retrieve information. <a href="https://github.com/robbieaverill/iresapi" target="_blank">For documentation, please click here.</a></p>
	
	<h2>Operator results:</h2>
	<ul class="results"><?php
		if($api->response_code != 200)
			echo 'API request failed... Skipping results';
		else {
			foreach($results as $operator) {
				echo "\n\t\t"; // spacing for source code
				echo '<li><span style="font-weight: bold">' . $operator->name . '</span> - Contact: ' . $operator->contact . ' on ' . $operator->phone . '</li>';
			}
			echo "\n";
		}
		?>
	</ul>
	
	<h3>Data returned from API</h3>
	<div style="height: 300px; background-color: #eee; border: 1px solid #ccc; overflow: scroll">
		<pre><?php echo htmlentities($operators); ?></pre>
	</div>
	
</body>
</html>