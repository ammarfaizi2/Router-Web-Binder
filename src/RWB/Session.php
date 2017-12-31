<?php

namespace RWB;

use RWB\RouteWebBinder;

class Session
{
	/**
	 * @var string
	 */
	private $type;

	/**
	 * @var \RWB\RouteWebBinder
	 */
	private $routeWebBinder;

	/**
	 * @var string
	 */
	private $cookieFile;

	/**
	 * Constructor.
	 *
	 * @param string $type
	 */
	public function __construct($type)
	{
		$this->type = $type;
	}

	public function initSession()
	{
		if ($this->type === 'peer-to-peer') {
			$this->initPeerToPeerSession();
		} elseif ($this->type === 'store') {
			$this->initStoreSession();
		}
	}

	public function giveMeRouteWebBinderInstance(RouteWebBinder $routeWebBinderInstance)
	{
		$this->routeWebBinder = $routeWebBinderInstance;
	}

	private function initPeerToPeerSession()
	{
		if (isset($_COOKIE['peer_to_peer_session'])) {
			$this->cookieFile = $_COOKIE['peer_to_peer_session'];
		} else {
			$key = sha1(time());
			setcookie("peer_to_peer_session", $key, time()+(3600*24*14));
			$this->cookieFile = $this->routeWebBinder->cookiesDir."/".$key;
		}
	}

	private function initStoreSession()
	{
		$this->cookieFile = $this->routeWebBinder->cookiesDir."/stored_cookies";
	}


	public function getCookieFile()
	{
		return $this->cookieFile;
	}
}
