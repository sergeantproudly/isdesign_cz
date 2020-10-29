<?php

	class Tabs {

		protected $params = [];

		protected $items;
		protected $itemActive;
		protected $classHolder;
		protected $template = 'tabs';
		protected $templateEl = 'tabs_el';
		protected $classActive = 'tab-act';

		private $db;
		private $settings;
		
		public function __construct($params = []) {
			global $Params;
			global $Settings;
			$this->db = $Params['Db']['Link'];
			$this->settings = $Settings;

			$this->init($params);
		}

		protected function init($params = []) {
			$this->params = $params;

			if (isset($params['items'])) $this->items = $this->params['items'];
			else $this->items = $this->params;
			if (isset($params['itemActive'])) $this->itemActive = $this->params['itemActive'];
			if (isset($params['template'])) $this->template = $this->params['template'];
			if (isset($params['templateEl'])) $this->templateEl = $this->params['templateEl'];
			if (isset($params['classActive'])) $this->classActive = $this->params['classActive'];
			if (isset($params['classHolder'])) $this->classHolder = $this->params['classHolder'];
		}

		public function getTabs() {
			$content = '';

			if (!$this->itemActive) $fst = true;
			foreach($this->items as $href => $title) {
				$content .= strtr(LoadTemplate($this->templateEl), [
					'<%CLASS%>'	=> ($fst || $this->itemActive == $href) ? ' class="' . $this->classActive . '"' : '',
					'<%HREF%>'	=> $href,
					'<%TITLE%>'	=> $title,
				]);
				$fst = false;
			}

			return strtr(LoadTemplate($this->template), [
				'<%CLASS%>'		=> $this->classHolder ? (' ' . $this->classHolder) : '',
				'<%CONTENT%>'	=> $content,
			]);
		}

	}

?>