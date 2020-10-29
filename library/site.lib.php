<?php
    
    class Site {

    	protected $db;
		protected $settings;

		protected $modals;

		public function __construct() {
			global $Params;
			global $Settings;
			global $Lang;
			$this->db = $Params['Db']['Link'];
			$this->settings = $Settings;
			$this->lang = $Lang;
		}

    	public function GetCurrentPage() {
			$page = false;
			if (preg_match('/\/([a-zA-Z0-9_\-\-]+)\/?$/', $_SERVER['REQUEST_URI'], $match)) {
				$page = $match[1];
			} elseif (preg_match('/\/$/', $_SERVER['REQUEST_URI'])) {
				$page = '/';
			}
			return $page;
		}

		public function GetPageFromLink($link) {
			$page = false;
			if (preg_match('/\/([a-zA-Z0-9_\-]+)\/?$/', $link, $match)) {
				$page = $match[1];
			} elseif (preg_match('/\/$/', $link)) {
				$page = '/';
			}
			return $page;
		}

		public function SetLinks($html) {
			$result = preg_replace('~<a +href="(?!http[s]?://)([^\>]+)~i', '<a href="/$1', $html);
			return strtr($result, array(
				'<a href="//'		=> '<a href="/',
				'<a href="/#'		=> '<a href="#',
				'<a href="/tel:'	=> '<a href="tel:',
				'<a href="/mailto'	=> '<a href="mailto' 
			));
		}

		public function AddModal($html) {
			$this->modals .= $html;
		}

		public function GetModals() {
			return $this->modals;
		}

		public function GetPage() {
			krnLoadLib('define');
			krnLoadLib('menu');
			krnLoadLib('lang');
			krnLoadLib('modal');
			global $krnModule;

			// menus
			$menuMain = new Menu([
				'menuDb'			=> 'menu_items',
				'subMenuDb'			=> 'menu_sub_items',
				'template'			=> 'mn_main',
				'templateEl'		=> 'mn_main_el',
				'templateElAct'		=> 'mn_main_el_act',
				'templateSub'		=> 'mn_sub',
				'templateSubEl'		=> 'mn_sub_el',
				'templateSubElAct'	=> 'mn_sub_el_act',
			]);

			$menuBottom = new Menu([
				'menuDb'			=> 'menu_bottom_items',
				'template'			=> 'mn_bottom',
				'templateEl'		=> 'mn_bottom_el',
				'templateElAct'		=> 'mn_bottom_el_act',
			]);

			// contacts
			$contact = $this->db->GetRow('SELECT * FROM contacts WHERE Lang = ?i', $this->lang->GetId());

			// tel
			$tel = $contact['Tel1'] ?: $contact['Tel2'];
			$tellink = preg_replace('/[^\d\+]/', '', $tel);

			// settings
			$siteTitle = $this->settings->GetSetting('SiteTitle', $Config['Site']['Title']);

			// user agreement and policy
			$files = $this->db->getAll('SELECT Title, Code, File FROM files');
			foreach ($files as $file) {
				if ($file['Code'] == 'agreement' || $file['Code'] == 'policy') $law[$file['Code']] = '<a href="' . $file['File'] . '">' . $file['Title'] . '</a>';
			}

			// copyright
			$copyright = strtr($this->settings->GetSetting('Copyright'), array(
				'<%YEAR%>'					=> date('Y'),
		    	'&lt;%YEAR%&gt;'			=> date('Y'),
			));

			// base modals
			$modalFeedback = new Modal('feedback', ['Action' => '/ajax--act-Feedback/']);
			$modalConsultation = new Modal('consultation', ['Action' => '/ajax--act-Feedback/']);
			$modalDone = new Modal('done');
			$this->addModal($modalFeedback->GetModal());
			$this->addModal($modalConsultation->GetModal());
			$this->addModal($modalDone->GetModal());

			// languages menu
			$content = '';
			$element = LoadTemplate('mn_lang_el');
			foreach ($this->lang->GetLangs() as $lang) {
				$lang['Class'] = $this->lang->GetId() == $lang['Id'] ? ' active' : '';
				$lang['Link'] = $lang['Protocol'] . $lang['Domain'] . $_SERVER['REQUEST_URI'];
				$content .= SetAtribs($element, $lang);
			}
			$langMenu = SetContent(LoadTemplate('mn_lang'), $content);

			// result
			$result = $this->lang->ProcessTemplate($krnModule->GetResult());

			// cookies
			$cookies = $_COOKIE['cookie_notice'] != 1 ? $this->lang->ProcessTemplate(LoadTemplate('cookies_notice')) : '';

			$result = strtr($result, array(
				'<%LANG%>'					=> $this->lang->GetLang('Acronym'),
		    	'<%META_KEYWORDS%>'			=> $Config['Site']['Keywords'],
		    	'<%META_DESCRIPTION%>'		=> $Config['Site']['Description'],
		    	'<%META_IMAGE%>'			=> '',
		    	'<%PAGE_TITLE%>'			=> $siteTitle,
		    	'<%SITE_TITLE%>'			=> $siteTitle,
		    	'<%SITE_TITLE_ALT%>'		=> htmlspecialchars($siteTitle, ENT_QUOTES),
		    	'<%SITE_URL%>'				=> $this->settings->GetSetting('SiteUrl', $Config['Site']['Url']),
		    	'<%EMAIL%>'					=> $this->settings->GetSetting('Email', $Config['Site']['Email']),
		    	'<%TEL%>'					=> $tel,
		    	'<%TELLINK%>'				=> $tellink,
		    	'&lt;%TEL%&gt;'				=> $tel,
		    	'&lt;%TELLINK%&gt;'			=> $tellink,
		    	'<%META_VERIFICATION%>'		=> $this->settings->GetSetting('MetaVerification'),
		    	'<%YANDEX_METRIKA%>'		=> $this->settings->GetSetting('YandexMetrika'),
		    	'<%RATING_COUNTER%>'		=> $this->settings->GetSetting('RatingCounter'),
		    	'<%MN_MAIN%>'				=> $menuMain->GetMenu(),
		    	'<%MN_LANG%>'				=> $langMenu,
		    	'<%BREAD_CRUMBS%>'			=> '',
		    	'<%FEEDBACKTITLE%>'			=> $this->settings->GetSetting('FeedbackTitle'),
		    	'<%COPYRIGHT%>'				=> $copyright,
		    	'<%DIRECTION%>'				=> $this->settings->GetSetting('Direction'),
		    	'<%MN_BOTTOM%>'				=> $menuBottom->GetMenu(),
		    	'<%MODALS%>'				=> $this->GetModals(),
		    	'<%CONSULTANT%>'			=> $this->settings->GetSetting('ConsultantCode'),
		    	'<%ANALYTICS%>'				=> $this->settings->GetSetting('AnalyticsCode'),
		    	'<%BLOCK1%>'				=> '',
		    	'<%BLOCK2%>'				=> '',
		    	'<%BLOCK3%>'				=> '',
		    	'<%BLOCK4%>'				=> '',
		    	'<%BLOCK5%>'				=> '',
		    	'<%BLOCK6%>'				=> '',
		    	'<%BLOCK7%>'				=> '',
		    	'<%BLOCK8%>'				=> '',
		    	'<%BLOCK9%>'				=> '',
		    	'<%BLOCK10%>'				=> '',
		    	'<%BLOCK11%>'				=> '',
		    	'<%BLOCK12%>'				=> '',
		    	'<%BLOCK13%>'				=> '',
		    	'<%BLOCK14%>'				=> '',
		    	'<%BLOCK15%>'				=> '',
		    	'<%YEAR%>'					=> date('Y'),
		    	'&lt;%YEAR%&gt;'			=> date('Y'),
		    	'<%COOKIES%>'				=> $cookies,
			));

			return $this->SetLinks($result);
		}	

	}
	
?>