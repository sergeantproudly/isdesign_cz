<?php

krnLoadLib('mail');
krnLoadLib('settings');
krnLoadLib('define');
krnLoadLib('lang');

class ajax extends krn_abstract{

	public function __construct($params=array()) {
		parent::__construct();
	}
	
	public function GetResult() {
		if ($_POST['act'] && method_exists($this, $_POST['act'])) {
			echo $this->$_POST['act'];
		}
		exit;
	}	

	/** Модальное окно */
	public function GetModal() {
		krnLoadLib('modal');
		$modalCode = $_POST['code'];
		$modal = new Modal($modalCode, $_POST['data']);
		return $this->lang->ProcessTemplate($modal->GetModal());
	}
	
	/** Загрузчик файлов */
	public function GetUploader() {
		krnLoadLib('uploader');
		$uploaderCode = $_POST['code'];
		$func = 'Uploader';
		$r = explode('_',$uploaderCode);
		foreach ($r as $k) {
			$k{0} = strtoupper($k{0});
			$func .= $k;
		}
		if (function_exists($func)) return $func();
		return false;
	}

	/** Обратная связь */
	public function Feedback() {
		$name = trim($_POST['name']);
		$email = trim($_POST['email']);
		$tel = trim($_POST['tel']) ? '+' . trim($_POST['tel']) : '';
		$text = $_POST['text'];
		$code = $_POST['code'];
		$subject = $_POST['subject'];
		$referer = $_POST['referer'];

		$capcha = $_POST['capcha'];

		// проверка на спамбота
		// основывается на сверке user agent-ов
		if ($capcha == $_SERVER['HTTP_USER_AGENT']) {
			if ($tel) {				
				$form = $this->db->getRow('SELECT Title, SuccessHeader, Success FROM forms WHERE Code=?s AND Lang = ?i', $code, $this->lang->GetId());				
				$request = '';
				if ($name) $request .= "Имя: $name\r\n";
				if ($email) $request .= "E-mail: $email\r\n";
				if ($tel) $request .= "Телефон: $tel\r\n";
				if ($referer) $request .= 'Страница заявки: ' . $this->db->getOne('SELECT Title FROM static_pages WHERE Code= ?s AND Lang = ?i', $referer, $this->lang->GetId()) ."\r\n";
				if ($text) $request .= 'Текст:'."\r\n$text\r\n";
				$this->db->query('INSERT INTO requests SET DateTime=NOW(), Form=?s, Name=?s, Tel=?s, Text=?s, RefererPage=?s, IsSet=0, Lang = ?i',
					$subject ?: ($form ? $form['Title'] : ''),
				 	$name,
				 	$tel,
					str_replace('"', '\"', $request),
					$_SERVER['HTTP_REFERER'],
					$this->lang->GetId()
				);
					
				global $Config;
				$siteTitle = strtr(stGetSetting('SiteEmailTitle', $Config['Site']['Title']), array('«'=>'"','»'=>'"','—'=>'-'));
				$siteEmail = stGetSetting('SiteEmail', $Config['Site']['Email']);
				$adminTitle = 'Администратор';
				$adminEmail = stGetSetting('Email', $Config['Site']['Email']);
					
				$letter['subject'] = ($subject ?: ($form ? $form['Title'] : '')).' с сайта "'.$siteTitle.'"';
				$letter['html'] = '<b>'.($subject ?: ($form ? $form['Title'] : '')).'</b><br/><br/>';
				$letter['html'] .= str_replace("\r\n", '<br/>', $request);
				$mail = new Mail();
				$mail->SendMailFromSite($adminEmail, $letter['subject'], $letter['html']);
											
				$json = array(
					'status' => true,
					'header' => $form['SuccessHeader'],
					'message' => strip_tags($form['Success']),
				);

			} else {
				$json = array(
					'status' => false,
					'message' => $this->lang->GetValue('FORM_ERROR_SERVER')
				);
			}
		} else {
			$json = array(
				'status' => false,
				'message' => $this->lang->GetValue('FORM_ERROR_SPAM')
			);
		}

		return json_encode($json);
	}

}

?>