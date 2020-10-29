<?php

krnLoadLib('settings');
krnLoadLib('define');

class Modal {

	protected $db;
	protected $settings;
	protected $lang;
	
	protected $code;
	protected $params = [];

	protected $template = '';
	protected $result = false;
	
	public function __construct($code, $params = []) {
		global $Params;
		global $Settings;
		global $Lang;
		$this->db = $Params['Db']['Link'];
		$this->settings = $Settings;
		$this->lang = $Lang;

		$this->code = $code;
		if (!empty($params)) $this->params = $params;
		else $this->params = $_POST;
		
		$this->template = SetAtribs(LoadTemplate('modal_base'), [
			'Id'		=> $this->params['Id'] ? $this->params['Id'] : $this->code,
			'Code'		=> $this->code,
			'Content' 	=> LoadTemplate('modal_'.$this->code)
		]);
		$func = 'Modal';
		$r = explode('_', $this->code);
		foreach ($r as $k) {
			$k{0} = strtoupper($k{0});
			$func .= $k;
		}
		if (method_exists($this, $func)) $this->result = $this->$func();		
	}
	
	public function GetModal() {
		return $this->lang->ProcessTemplate($this->result);
	}

	public function ModalDone() {
		return $this->template;
	}

	public function ModalFeedback() {
		global $Params;
		$form = $this->db->getRow('SELECT * FROM `forms` WHERE Code = ?s AND Lang = ?i', 'modal-feedback', $this->lang->GetId());
		
		$template = strtr($this->template, array(
			'<%TITLE%>'			=> $this->params['Title'] ?: $form['Title'],
			'<%TEXT%>'			=> $form['Text'],
			'<%ACTION%>'		=> $this->params['Action'],
			'<%ID%>'			=> $this->params['Id'] ? $this->params['Id'] : $this->code,
			'<%CODE%>'			=> $this->code,
			'<%REFERER%>'		=> $Params['Site']['Page']['Code'],
		));
		return $template;
	}

	public function ModalConsultation() {
		global $Params;
		$form = $this->db->getRow('SELECT * FROM `forms` WHERE Code = ?s AND Lang = ?i', 'modal-consultation', $this->lang->GetId());
		
		$template = strtr($this->template, array(
			'<%TITLE%>'			=> $this->params['Title'] ?: $form['Title'],
			'<%TEXT%>'			=> $form['Text'],
			'<%ACTION%>'		=> $this->params['Action'],
			'<%ID%>'			=> $this->params['Id'] ? $this->params['Id'] : $this->code,
			'<%CODE%>'			=> $this->code,
			'<%REFERER%>'		=> $Params['Site']['Page']['Code'],
		));
		return $template;
	}

	public function ModalCalculation() {
		global $Params;
		$form = $this->db->getRow('SELECT * FROM `forms` WHERE Code = ?s AND Lang = ?i', 'modal-calculation', $this->lang->GetId());
		
		$template = strtr($this->template, array(
			'<%TITLE%>'			=> $this->params['Title'] ?: $form['Title'],
			'<%TEXT%>'			=> $form['Text'],
			'<%ACTION%>'		=> $this->params['Action'],
			'<%ID%>'			=> $this->params['Id'] ? $this->params['Id'] : $this->code,
			'<%CODE%>'			=> $this->code,
			'<%SUBJECT%>'		=> $this->params['Subject'] ?: '',
			'<%REFERER%>'		=> $Params['Site']['Page']['Code'],
		));
		return $template;
	}

	public function ModalRequest() {
		global $Params;
		$form = $this->db->getRow('SELECT * FROM `forms` WHERE Code = ?s AND Lang = ?i', 'modal-request', $this->lang->GetId());
		
		$template = strtr($this->template, array(
			'<%TITLE%>'			=> $this->params['Title'] ?: $form['Title'],
			'<%TEXT%>'			=> $form['Text'],
			'<%ACTION%>'		=> $this->params['Action'],
			'<%ID%>'			=> $this->params['Id'] ? $this->params['Id'] : $this->code,
			'<%CODE%>'			=> $this->code,
			'<%REFERER%>'		=> $Params['Site']['Page']['Code'],
		));
		return $template;
	}
	
}

?>