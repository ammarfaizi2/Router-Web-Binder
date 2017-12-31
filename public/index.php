<?php

require __DIR__ . "/../vendor/autoload.php";

define("ROUTER_DATA_DIR", __DIR__."/../data");

$app = new \RWB\RouteWebBinder(
	[
		"host" 		=> "https://m.facebook.com",
		"session"	=> "store",
		"user_agent"=> "Mozilla/5.0 (X11; Ubuntu; Linux i686; rv:56.0) Gecko/20100101 Firefox/56.0",
		"render_all_header_response" => false
	]
);
$app->run();
