<?php

namespace RWB;

class OutputFixer
{
	private $out;

	private $host;

	private $optional;

	public function __construct($out, $host, $optional = null)
	{
		$this->out = $out;
		$this->host = $host;
		$this->optional = $optional;
	}

	public function __toString()
	{
		if (! isset($_SERVER['REQUEST_SCHEME'])) {
			$_SERVER['REQUEST_SCHEME'] = "http";
		}
		$this->fixHref();
		$this->fixForm();
		if ($this->optional !== null) {
			$q = $this->optional;
			return $q($this->out);
		}
		return $this->out;
	}

	private function fixForm()
	{
		$this->out = explode("<form", $this->out);
		foreach ($this->out as &$val) {
			$tmp = $val;
			$tmp = explode(">", $tmp, 2);
			if (strpos($tmp[0], "action=\"") !== false) {				
				$val = explode("action=\"", $val, 2);
				$url = explode("\"", $val[1], 2);
				$val[1] = $url[1];
				$url = str_replace($this->host, $_SERVER['HTTP_HOST'], $url[0]);
				$val = "action=\"".$url."\"".$val[1];
			}
		}
		unset($val);
		$this->out = implode("<form ", $this->out);
	}

	private function fixHref()
	{
		$this->out = explode(" href=", $this->out);
		foreach ($this->out as &$val) {
			$tmp = $val;
			if (substr($tmp, 0, 7) === "http://") {
				$val = explode("http://", $val, 2);
				$val = explode("/", $val[1], 2);
				$val = str_ireplace($this->host, $_SERVER['HTTP_HOST'], $val[0], $n).(isset($val[1]) ? "/".$val[1] : "");
				$val = $n > 0 ? $_SERVER['REQUEST_SCHEME']."://".$val : $val;
			} elseif (substr($tmp, 0, 8) === "https://") {
				$val = explode("https://", $val, 2);
				$val = explode("/", $val[1], 2);
				$val = str_ireplace($this->host, $_SERVER['HTTP_HOST'], $val[0], $n).(isset($val[1]) ? "/".$val[1] : "");
				$val = $n > 0 ? $_SERVER['REQUEST_SCHEME']."://".$val : $val;
			}
		}
		$this->out = implode(" href=", $this->out);
		$this->out = explode(" href=\"", $this->out);
		foreach ($this->out as &$val) {
			$tmp = $val;
			if (substr($tmp, 0, 7) === "http://") {
				$val = explode("http://", $val, 2);
				$val = explode("/", $val[1], 2);
				$val = str_ireplace($this->host, $_SERVER['HTTP_HOST'], $val[0], $n).(isset($val[1]) ? "/".$val[1] : "");
				$val = $n > 0 ? $_SERVER['REQUEST_SCHEME']."://".$val : $val;
			} elseif (substr($tmp, 0, 8) === "https://") {
				$val = explode("https://", $val, 2);
				$val = explode("/", $val[1], 2);
				$val = str_ireplace($this->host, $_SERVER['HTTP_HOST'], $val[0], $n).(isset($val[1]) ? "/".$val[1] : "");
				$val = $n > 0 ? $_SERVER['REQUEST_SCHEME']."://".$val : $val;
			}
		}
		unset($val, $tmp);
		$this->out = implode(" href=\"", $this->out);
	}
}