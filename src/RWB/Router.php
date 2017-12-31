<?php

namespace RWB;

use RWB\Session;
use RWB\OutputFixer;
use RWB\UrlGenerator;
use RWB\RouteWebBinder;

class Router
{
	private $ch;

	private $out;

	private $info;

	private $host;

	private $session;

	private $routeWebBinder;

	private $postContextStream;

	public function __construct($host, Session $sessionInstance, RouteWebBinder $routeWebBinderInstance)
	{
		$this->session = $sessionInstance;
		$this->routeWebBinder = $routeWebBinderInstance;
		$this->ch = curl_init(
			$this->host = UrlGenerator::hostWithClientUri($host)
		);
		$this->session->giveMeRouteWebBinderInstance($this->routeWebBinder);
	}

	public function init()
	{
		$this->initCookieSession();
		$this->generateRequestContext();
		curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, false);
		curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($this->ch, CURLOPT_CONNECTTIMEOUT, 30);
		curl_setopt($this->ch, CURLOPT_TIMEOUT, 30);
		curl_setopt($this->ch, CURLOPT_HEADER, true);
	}

	public function run()
	{
		$this->out = curl_exec($this->ch);
		if ($errno = curl_errno($this->ch)) {
			$this->out = "Error ({$errno}): ".curl_error($this->ch);
		}
		$this->info = curl_getinfo($this->ch);
		$this->renderHeader();
		print new OutputFixer($this->out, UrlGenerator::pureHostWithoutProtocol($this->host));
	}

	private function renderHeader()
	{
		if (isset($this->info['header_size'])) {
			$headerResponse = explode("\n", substr($this->out, 0, $this->info['header_size']));
			if ($this->routeWebBinder->renderAllHeaderResponse) {
				array_walk($headerResponse, function (&$header) {
					header(
						trim($header)
					);
				});
			} else {
				if (isset($this->info['http_code'])) {
					http_response_code($this->info['http_code']);
				}
				$headerContext = [];
				array_walk($headerResponse, function ($header) use (&$headerContext) {
					$header = explode(":", $header);
					$headerContext[strtolower(trim($header[0]))] = isset($header[1]) ? trim($header[1]) : "";
				});
				if (isset($headerContext['location'])) {
					header(
						"location: ".UrlGenerator::generateHeaderLocationUrl(
							$this->host, $headerContext['location']
						)
					);
				}
			}
			$this->out = substr($this->out, $this->info['header_size']);
		}
	}

	private function generateRequestContext()
	{
		curl_setopt($this->ch, CURLOPT_USERAGENT, $this->routeWebBinder->userAgent);
		$this->postContextStream = file_get_contents("php://input");
		if ($_SERVER['REQUEST_METHOD'] === "POST") {
			curl_setopt($this->ch, CURLOPT_POST, true);
			curl_setopt($this->ch, CURLOPT_POSTFIELDS, $this->postContextStream);
		} else {
			curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, $_SERVER['REQUEST_METHOD']);
			if ($this->postContextStream !== "") {
				curl_setopt($this->ch, CURLOPT_POSTFIELDS, $this->postContextStream);
			}
		}
	}

	private function initCookieSession()
	{
		$cookieFile = $this->session->getCookieFile();
		curl_setopt($this->ch, CURLOPT_COOKIEJAR, $cookieFile);
		curl_setopt($this->ch, CURLOPT_COOKIEFILE, $cookieFile);
	}
}
