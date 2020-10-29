<?php

class map extends krn_abstract{	

	function __construct() {
		parent::__construct();
		$this->page = $this->db->getRow('SELECT Title, Header, Code, Content, SeoTitle, SeoKeywords, SeoDescription, TemplateCode FROM static_pages WHERE Code = ?s AND Lang = ?i', 'map', $this->lang->GetId());
		
		$this->pageTitle = $this->page['Title'];
		$this->breadCrumbs = GetBreadCrumbs(array(
			$this->lang->GetValue('PAGE_HOME') => ''),
			$this->pageTitle);
	}	

	function GetResult(){
		$Blocks = krnLoadModuleByName('blocks');

		$this->content = $this->GetMap();

		$result = krnLoadPageByTemplate($this->page['TemplateCode'] ?: 'base_page');
		$result = strtr($result, array(
			'<%META_KEYWORDS%>'		=> $this->page['SeoKeywords'],
    		'<%META_DESCRIPTION%>'	=> $this->page['SeoDescription'],
    		'<%PAGE_TITLE%>'		=> $this->pageTitle,
    		'<%BREAD_CRUMBS%>'		=> $this->breadCrumbs,
    		'<%TITLE%>'				=> $this->page['Header'] ?: $this->page['Title'],
			'<%CONTENT%>'			=> $this->content,
		));
		return $result;
	}
	
	function GetMap() {		
		$arr = $this->db->getAll('SELECT Title, Code FROM static_pages WHERE Lang = ?i ORDER BY IF(`Order`,-1000/`Order`,0)', $this->lang->GetId());
		foreach ($arr as $static_page){
			$static_pages[$static_page['Code']] = $static_page;
		}

		$tree = array();

		// statics
		$items =$this->db->getAll('SELECT Title, Code FROM static_pages WHERE Lang = ?i ORDER BY IF(`Order`,-1000/`Order`,0)', $this->lang->GetId());
		foreach ($items as $item) {
			if ($item['Code'] != 'services' && $item['Code'] != 'news' && $item['Code'] != 'works') {
				if ($item['Code'] == 'main') $item['Code'] = '/';
				$tree[$item['Code']] = $item;
			}
		}
		
		// services
		$items = $this->db->getAll('SELECT Title, Code FROM services WHERE Lang = ?i ORDER BY IF(`Order`,-1000/`Order`,0)', $this->lang->GetId());
		foreach ($items as $item) {
			$tree[$item['Code']] = $item;			
		}

		// statues
		/*
		$tree['statues'] = $static_pages['statues'];
		$items = $this->db->getAll('SELECT Title, Code FROM statues WHERE Lang = ?i ORDER BY Date DESC', $this->lang->GetId());
		foreach ($items as $item) {
			$tree['statues']['pages'][$item['Code']] = $item;
		}
		*/

		// projects
		$tree['projects'] = $static_pages['projects'];
		$items = $this->db->getAll('SELECT p.Title, p.Code, c.Code AS Category FROM cat_projects p LEFT JOIN cat_categories c ON p.CategoryId = c.Id WHERE p.Lang = ?i ORDER BY IF(p.`Order`,-100/p.`Order`,0)', $this->lang->GetId());
		foreach ($items as $item) {
			$tree['projects']['pages'][$item['Code']] = $item;
		}
		
		$content = '';
		foreach ($tree as $code => $item) {
			$sub = '';
			if ($item['pages']) {
				foreach ($item['pages'] as $sub_code => $sub_item) {
					if ($code != 'projects') {
						$sub .= '<li><a href="/'.$code.'/'.$sub_code.'/">' . $sub_item['Title'] . '</a></li>';
					} else {
						$sub .= '<li><a href="/'.$code.'/'.$sub_item['Category'].'/'.$sub_code.'/">' . $sub_item['Title'] . '</a></li>';
					}					
				}
				$sub = '<ul>' . $sub . '</ul>';
			}

			if ($code != '/') {
				$content .= '<li><a href="/'.$item['Code'].'/">'.$item['Title'].'</a>'.$sub.'</li>';
			} else {
				$content .= '<li><a href="'.$item['Code'].'">'.$item['Title'].'</a>'.$sub.'</li>';
			}
		}

		$result = SetContent(LoadTemplate('map'), $content);
		return $result;
	}

}

?>