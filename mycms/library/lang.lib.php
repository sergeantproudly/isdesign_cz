<?php

krnLoadLib('settings');
krnLoadLib('define');

class Lang {
	protected $langs = [];
	protected $lang;

	public function __construct($domain = false) {
		$res = dbDoQuery('SELECT * FROM languages ORDER BY IF(`Order`, -100/`Order`, 0)');
		foreach ($res as $rec) {
			$this->langs[$rec['Domain']] = $rec;
		}
		if ($domain && in_array($domain, array_keys($this->langs))) {
			$this->lang = $this->langs[$domain];
		} else {
			$this->lang = current($this->langs);
		}
	}

	public function GetLangs() {
		return $this->langs;
	}

	public function GetLang($field = false) {
		return $field ? $this->lang[$field] : $this->lang;
	}

	public function GetId() {
		return $this->lang['Id'];
	}
}

?>