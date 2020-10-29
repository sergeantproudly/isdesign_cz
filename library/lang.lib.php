<?php

krnLoadLib('settings');
krnLoadLib('define');

class Lang {
	const ERROR404 = 'ERROR404';
	const ERROR404_HEADER = 'ERROR404_HEADER';
	const PRIVACY_POLICY = 'PRIVACY_POLICY';
	const MAP = 'MAP';
	const FEEDBACK = 'FEEDBACK';
	const CALCULATE = 'CALCULATE';
	const VIEW = 'VIEW';
	const VIEW_INSTAGRAM = 'VIEW_INSTAGRAM';
	const DETAILS = 'DETAILS';
	const SOCIAL_SERVICES = 'SOCIAL_SERVICES';
	const FIELD_NAME = 'NAME';
	const FIELD_PHONE = 'PHONE';
	const FIELD_EMAIL = 'EMAIL';
	const FIELD_TEXT = 'TEXT';
	const FIELD_TEXT2 = 'TEXT2';
	const CLOSE = 'CLOSE';
	const AGREEMENT = 'AGREEMENT';
	const SEND = 'SEND';
	const SHARE = 'SHARE';
	const SHOW_MORE = 'SHOW_MORE';
	const ADMINISTRATOR_NAME = 'ADMINISTRATOR_NAME';
	const FORM_ERROR_SERVER = 'FORM_ERROR_SERVER';
	const FORM_ERROR_SPAM = 'FORM_ERROR_SPAM';
	const PAGE_HOME = 'PAGE_HOME';
	const CATEGORY_NEW = 'CATEGORY_NEW';
	const COOKIES_TEXT = 'COOKIES_TEXT';
	const COOKIES_BUTTON = 'COOKIES_BUTTON';
	const MONTHS_JAN = 'MONTHS_JAN';
	const MONTHS_FEB = 'MONTHS_FEB';
	const MONTHS_MAR = 'MONTHS_MAR';
	const MONTHS_APR = 'MONTHS_APR';
	const MONTHS_MAY = 'MONTHS_MAY';
	const MONTHS_JUN = 'MONTHS_JUN';
	const MONTHS_JUL = 'MONTHS_JUL';
	const MONTHS_AUG = 'MONTHS_AUG';
	const MONTHS_SEP = 'MONTHS_SEP';
	const MONTHS_OKT = 'MONTHS_OKT';
	const MONTHS_NOV = 'MONTHS_NOV';
	const MONTHS_DEC = 'MONTHS_DEC';


	protected $db;
	protected $settings;

	protected $langs = [];
	protected $lang;
	protected $data = [
		'ru'	=> [
			self::ERROR404 => 'Видимо, что-то пошло не так.<br/>Попробуйте перейти на <a href="/">главную</a> или <a href="tel:<%TELLINK%>" class="tel">позвоните по номеру <%TEL%></a>',
			self::ERROR404_HEADER => 'Увы, пусто',
			self::PRIVACY_POLICY => 'Обработка персональных данных',
			self::MAP => 'Карта сайта',
			self::FEEDBACK => 'Обратная связь',
			self::CALCULATE => 'Получить расчет стоимости',
			self::VIEW => 'Смотреть',
			self::VIEW_INSTAGRAM => 'Смотреть наш Instagram',
			self::DETAILS => 'Подробнее',
			self::SOCIAL_SERVICES => 'Социальные сети',
			self::FIELD_NAME => 'Имя',
			self::FIELD_PHONE => 'Телефон',
			self::FIELD_EMAIL => 'E-mail',
			self::FIELD_TEXT => 'Вопрос коротко',
			self::FIELD_TEXT2 => 'Вопрос',
			self::CLOSE => 'Закрыть',
			self::AGREEMENT => 'Я даю согласие на обработку своих<br/><a href="/privacy-policy/">персональных данных</a>',
			self::SEND => 'Отправить',
			self::SHARE => 'Поделиться',
			self::SHOW_MORE => 'Показать еще',
			self::ADMINISTRATOR_NAME => 'Администратор',
			self::FORM_ERROR_SERVER => 'Серверная ошибка. При повторном возникновении, пожалуйста, обратитесь к администратору.',
			self::FORM_ERROR_SPAM => 'Сработал антиспам. При повторном возникновении, пожалуйста, обратитесь к администратору.',
			self::PAGE_HOME => 'Главная',
			self::CATEGORY_NEW => 'Новые проекты',
			self::COOKIES_TEXT => 'Используя сайт ISDesign group, вы соглашаетесь с использованием файлов cookie и сервисов сбора технических данных для улучшения качества обслуживания. <a href="/privacy-policy/">Подробнее</a>',
			self::COOKIES_BUTTON => 'Хорошо',
			self::MONTHS_JAN => 'января',
			self::MONTHS_FEB => 'февраля',
			self::MONTHS_MAR => 'марта',
			self::MONTHS_APR => 'апреля',
			self::MONTHS_MAY => 'мая',
			self::MONTHS_JUN => 'июня',
			self::MONTHS_JUL => 'июля',
			self::MONTHS_AUG => 'августа',
			self::MONTHS_SEP => 'сентября',
			self::MONTHS_OKT => 'октября',
			self::MONTHS_NOV => 'ноября',
			self::MONTHS_DEC => 'декабря',
		],
		'en'	=> [
			self::ERROR404 => 'Something seems to be wrong.<br/>Try going to the <a href="/">Home</a> page or <a href="tel:<%TELLINK%>" class="tel">call <%TEL%></a>',
			self::ERROR404_HEADER => 'Sorry, nothing here',
			self::PRIVACY_POLICY => 'Personal Data Processing',
			self::MAP => 'Site Map',
			self::FEEDBACK => 'Feedback',
			self::CALCULATE => 'Calculate the cost',
			self::VIEW => 'View',
			self::VIEW_INSTAGRAM => 'See our Instagram',
			self::DETAILS => 'Details',
			self::SOCIAL_SERVICES => 'Social networks',
			self::FIELD_NAME => 'Name',
			self::FIELD_PHONE => 'Phone',
			self::FIELD_EMAIL => 'E-mail',
			self::FIELD_TEXT => 'Question in brief',
			self::FIELD_TEXT2 => 'Question',
			self::CLOSE => 'Close',
			self::AGREEMENT => 'I agree to my <br/><a href="/privacy-policy/">personal data</a> being processed',
			self::SEND => 'Submit',
			self::SHARE => 'Share',
			self::SHOW_MORE => 'Show more',
			self::ADMINISTRATOR_NAME => 'Administrator',
			self::FORM_ERROR_SERVER => 'Server error. Should it occur again, please, refer to the administrator.',
			self::FORM_ERROR_SPAM => 'Anti-spam filter in action. Should it occur again, please, refer to the administrator.',
			self::PAGE_HOME => 'Home',
			self::CATEGORY_NEW => 'New projects',
			self::COOKIES_TEXT => ' Using the ISDesign Group site, you agree to use the cookie files and technical data collection services to improve our service quality. <a href="/privacy-policy/">Details</a>',
			self::COOKIES_BUTTON => 'Ok',
			self::MONTHS_JAN => 'January',
			self::MONTHS_FEB => 'February',
			self::MONTHS_MAR => 'March',
			self::MONTHS_APR => 'April',
			self::MONTHS_MAY => 'May',
			self::MONTHS_JUN => 'June',
			self::MONTHS_JUL => 'July',
			self::MONTHS_AUG => 'August',
			self::MONTHS_SEP => 'September',
			self::MONTHS_OKT => 'October',
			self::MONTHS_NOV => 'November',
			self::MONTHS_DEC => 'December',
		],
		'cz'	=> [
			self::ERROR404 => 'Zdá se, že se něco pokazilo. <br/>Zkuste přejít na <a href="/">Home</a> tuto stránku nebo <a href="tel:<%TELLINK%>" class="tel">volejte <%TEL%></a>',
			self::ERROR404_HEADER => 'Omlouváme se, zde nic není',
			self::PRIVACY_POLICY => 'Zpracování osobních údajů',
			self::MAP => 'Mapa stránek',
			self::FEEDBACK => 'Zpětná vazba',
			self::CALCULATE => 'Spočítat náklady',
			self::VIEW => 'Prohlédnout si',
			self::VIEW_INSTAGRAM => 'Podívejte se na náš Instagram',
			self::DETAILS => 'Podrobnosti',
			self::SOCIAL_SERVICES => 'Sociální sítě',
			self::FIELD_NAME => 'Jméno',
			self::FIELD_PHONE => 'Telefon',
			self::FIELD_EMAIL => 'E-mail',
			self::FIELD_TEXT => 'Stručný dotaz',
			self::FIELD_TEXT2 => 'Dotaz',
			self::CLOSE => 'Zavřít',
			self::AGREEMENT => 'Souhlasím se <br/><a href="/privacy-policy/">zpracováním</a> osobních údajů',
			self::SEND => 'Odeslat',
			self::SHARE => 'Sdílet',
			self::SHOW_MORE => 'Zobrazit více',
			self::ADMINISTRATOR_NAME => 'Správce',
			self::FORM_ERROR_SERVER => 'Došlo k chybě serveru. Pokud se to bude opakovat, obraťte se prosím na správce.',
			self::FORM_ERROR_SPAM => 'Došlo ke spuštění antispamového filtru. Pokud se to bude opakovat, obraťte se prosím na správce.',
			self::PAGE_HOME => 'Domů',
			self::CATEGORY_NEW => 'Nové projekty',
			self::COOKIES_TEXT => 'Používáním stránek ISDesign Group souhlasíte s využitím souborů cookies a služby sběru technických dat za účelem vylepšení kvality našich služeb. <a href="/privacy-policy/">Podrobnosti</a>',
			self::COOKIES_BUTTON => 'Souhlasím',
			self::MONTHS_JAN => 'Leden',
			self::MONTHS_FEB => 'Únor',
			self::MONTHS_MAR => 'Březen',
			self::MONTHS_APR => 'Duben',
			self::MONTHS_MAY => 'Květen',
			self::MONTHS_JUN => 'Červen',
			self::MONTHS_JUL => 'Červenec',
			self::MONTHS_AUG => 'Srpen',
			self::MONTHS_SEP => 'Září',
			self::MONTHS_OKT => 'Říjen',
			self::MONTHS_NOV => 'Listopad',
			self::MONTHS_DEC => 'Prosinec',
		],
	];

	public function __construct($domain = false) {
		global $Params;
		global $Settings;
		$this->db = $Params['Db']['Link'];
		$this->settings = $Settings;

		$this->langs = $this->db->getInd('Domain', 'SELECT * FROM languages ORDER BY IF(`Order`, -100/`Order`, 0)');
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
	
	public function GetValue($code) {
		return $this->data[$this->lang['Acronym']][$code];
	}

	public function SetLangById($langId) {
		foreach ($this->langs as $lang) {
			if ($lang['Id'] == $langId) {
				$this->lang = $lang;
				return true;
			}
		}
		return false;
	}

	public function ProcessTemplate($template) {
		$subst = [];
		foreach ($this->data[$this->lang['Acronym']] as $key => $value) {
			$subst['<%LANG::' . $key . '%>'] = $value;
		}
		return strtr($template, $subst);
	}
}

?>