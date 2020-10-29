<?php

krnLoadLib('settings');

class main extends krn_abstract{	

	public function __construct(){
		parent::__construct();
		$this->page = $this->db->getRow('SELECT Id, Title, Header, Content, SeoTitle, SeoKeywords, SeoDescription FROM static_pages WHERE Code="main" AND Lang = ?i', $this->lang->GetId());
		
		global $Config;
		$this->pageTitle = $this->page['Title'] ?: $this->settings->GetSetting('SiteTitle', $Config['Site']['Title'] ?: 'Главная');
	}	

	public function GetResult(){
		krnLoadLib('modal');
		global $Site;

		global $Config;
		$Blocks = krnLoadModuleByName('blocks', $this->page['Id']);
		$blocks = $Blocks->GetPageBlocks();
		
		$result = krnLoadPageByTemplate('base_main');
		$result = strtr($result, array(
			'<%META_KEYWORDS%>'		=> $this->page['Keywords'] ?: $Config['Site']['Keywords'],
			'<%META_DESCRIPTION%>'	=> $this->page['Description'] ?: $Config['Site']['Description'],
			'<%PAGE_TITLE%>'		=> $this->pageTitle,
			'<%SLIDER%>'			=> $this->GetSlider(),
		));

		foreach ($blocks as $i => $block) {
			$result = str_replace('<%BLOCK' . ($i + 1) . '%>', $block, $result);
		}

		return $result;
	}

	/** Слайдер на главной */
	public function GetSlider() {
		$element = LoadTemplate('slider_el');
		$content = '';

		$slider = $this->db->getAll('SELECT Image, ImageMob, Link, Text FROM slider WHERE Image <> "" AND Lang = ?i ORDER BY IF(`Order`, -1000/`Order`, 0)', $this->lang->GetId());
		foreach ($slider as $counter => $slide) {
			$slide['Class'] = $counter == 0 ? ' active' : '';
			$slide['Background'] = 'data-background-image="' . $slide['Image'] . '" data-background-mob-image="' . $slide['ImageMob'] . '"';
			$content .= SetAtribs($element, $slide);
		}

		$result = strtr(LoadTemplate('slider'), array(
			'<%CONTENT%>'		=> $content,
			'<%AUTOSECONDS%>'	=> $this->settings->GetSetting('SliderAutotime')
		));
		return $result;
	}

}
?>