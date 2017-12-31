<?php

namespace RWB;

use RWB\Exceptions\UrlGenerationException;

class UrlGenerator
{
	public static function hostWithClientUri($host)
	{
		if (substr($host, 0, 7) !== "http://" && substr($host, 0, 8) !== "https://") {
			$host = "http://{$host}";
		}
		if (! filter_var($host, FILTER_VALIDATE_URL)) {
			throw new UrlGenerationException("Invalid host", 1);
		}
		return rtrim($host, "/").$_SERVER['REQUEST_URI'];
	}

	public static function generateHeaderLocationUrl($host, $context)
	{
		return str_replace(
				str_replace(
					["http://", "https://"], ["", ""], $host
				),
				$_SERVER['HTTP_HOST'],
				$context
			);
	}

	public static function pureHostWithoutProtocol($host)
	{
		return preg_replace("@[^\w\d\.\-]@", "", str_replace(["https://", "http://"], "", $host));
	}
}
