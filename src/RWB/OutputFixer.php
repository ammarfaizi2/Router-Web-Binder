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
		$out = explode("<form", $this->out);
		$fo = $out[0];
		unset($out[0]);
		foreach ($out as &$val) {
			if (strpos($val, "action=\"")) {
				$val = explode("action=\"", $val, 2);
				$val = explode("\"", $val[1], 2);
				$vvv = str_replace($this->host, $_SERVER['HTTP_HOST'], $val[0]);
				$val = "<form action=\"".$vvv."\"".$val[1];
			}
		}

		$this->out = $fo.implode($out);
	}

	private function fixHref()
	{
		$out = explode(" href=\"", $this->out);
		$fo = $out[0];
		unset($out[0]);
		foreach ($out as &$val) {
			$val = explode("\"", $val, 2);
			if (substr($val[0], 0, 8) === "https://") {
				$fpo = $val[1];
				$val = substr($val[0], 8);
				$val = explode("/", $val, 2);
				$val = "https://".str_replace($this->host, $_SERVER['HTTP_HOST'], $val[0])."/".$val[1]."\"".$fpo;
			} else {
				$val = implode("\"", $val);
			}
		}
		$this->out = $fo.implode(" href=\"", $out);
	}
}