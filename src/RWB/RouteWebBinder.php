<?php

namespace RWB;

use RWB\Router;
use RWB\Exceptions\RouteWebBinderException;

class RouteWebBinder
{
	/**
	 * @var array
	 */
	private $initInfo = [];

	/**
	 * @var string
	 */
	private $host;

	/**
	 * @var string
	 */
	private $sessionHelper = 'peer-to-peer';

	/**
	 * @var \RWB\Router
	 */
	private $router;

	/**
	 * @var string
	 */
	public $cacheDir;

	/**
	 * @var string
	 */
	public $cookiesDir;

	/**
	 * @var string
	 */
	public $userAgent = "Mozilla/5.0 (X11; Ubuntu; Linux i686; rv:56.0) Gecko/20100101 Firefox/56.0";

	/**
	 * @var bool
	 */
	public $renderAllHeaderResponse = true;

	/**
	 * Constructor.
	 *
	 * @param array $initInfo
	 */
	public function __construct($initInfo)
	{
		if (! isset($initInfo['host'])) {
			throw new RouteWebBinderException("Host is not provided", 1);
		}
		$this->host = $initInfo['host'];
		if (isset($initInfo['session'])) {
			if (in_array($initInfo['session'], ['peer-to-peer', 'store'])) {
				$this->sessionHelper = $initInfo['session'];
			} else {
				throw new RouteWebBinderException("Invalid session type", 1);
			}
		}
		$this->initDataDir();
		$this->router = new Router($this->host, new Session($this->sessionHelper), $this);
		if (isset($initInfo['user_agent'])) {
			$this->userAgent = $initInfo['user_agent'];
		}
		if (isset($initInfo['render_all_header_response'])) {
			$this->renderAllHeaderResponse = $initInfo['render_all_header_response'];
		}
	}

	/**
	 * Init data directory
	 */
	private function initDataDir()
	{
		if (defined("ROUTER_DATA_DIR")) {
			is_dir(ROUTER_DATA_DIR) or mkdir(ROUTER_DATA_DIR);
			is_dir($this->cacheDir = ROUTER_DATA_DIR."/cache") or mkdir(ROUTER_DATA_DIR."/cache");
			is_dir($this->cookiesDir = ROUTER_DATA_DIR."/cookies") or mkdir(ROUTER_DATA_DIR."/cookies");
			if (! is_dir(ROUTER_DATA_DIR)) {
				throw new RouteWebBinderException("Cannot create directory ".ROUTER_DATA_DIR, 1);
			}
			if (! is_writable(ROUTER_DATA_DIR)) {
				throw new RouteWebBinderException(ROUTER_DATA_DIR." is not writeable", 1);
			}
			if (! is_dir($this->cacheDir)) {
				throw new RouteWebBinderException("Cannot create directory ".$this->cacheDir, 1);		
			}
			if (! is_writable($this->cookiesDir)) {
				throw new RouteWebBinderException($this->cacheDir." is not writeable", 1);
			}
			if (! is_dir($this->cookiesDir)) {
				throw new RouteWebBinderException("Cannot create directory ".$this->cookiesDir, 1);		
			}
			if (! is_writable($this->cookiesDir)) {
				throw new RouteWebBinderException($this->cookiesDir." is not writeable", 1);
			}
		}
	}

	/**	 
	 * Run application.
	 */
	public function run()
	{
		$this->router->init();
		$this->router->run();
	}
}
