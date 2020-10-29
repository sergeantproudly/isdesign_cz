<?php

class _static extends krn_abstract {	

	function __construct() {
		parent::__construct();
		global $Params;
		$this->page = $this->db->getRow('SELECT Id, Code, Title, Header, Content, SeoTitle, SeoKeywords, SeoDescription, TemplateCode FROM static_pages WHERE Code= ?s AND Lang = ?i', $Params['Site']['Page']['Code'], $this->lang->GetId());
		if (!$this->page) {
			header('HTTP/1.1 404 Not Found');
			header('Status: 404 Not Found');
			$this->pageTitle = $this->lang->GetValue('ERROR404_HEADER');
			$this->page['Title'] = '';
			$this->page['Content'] = LoadTemplate('404');
			
		} else {
			$this->page['Title'] = stripslashes($this->page['Title']);
			$this->page['Content'] = stripslashes($this->page['Content']);
			$this->pageTitle = $this->page['SeoTitle'] ? $this->page['SeoTitle'] : $this->page['Title'];
			$this->breadCrumbs = GetBreadCrumbs(array($this->lang->GetValue('PAGE_HOME')=>'/'), $this->pageTitle);
		}
	}	

	function GetResult() {
		$this->content = $this->page['Content'];		

		$result = krnLoadPageByTemplate($this->page['TemplateCode'] ?: 'base_page');
		$result = strtr($result, array(
			'<%META_KEYWORDS%>'		=> $this->page['SeoKeywords'],
    		'<%META_DESCRIPTION%>'	=> $this->page['SeoDescription'],
    		'<%PAGE_TITLE%>'		=> $this->pageTitle,
    		//'<%BREAD_CRUMBS%>'		=> $this->breadCrumbs,
    		'<%BREAD_CRUMBS%>'		=> '',
    		'<%TITLE%>'				=> $this->page['Header'] ?: $this->page['Title'],
			'<%CONTENT%>'			=> $this->content,
		));

		$Blocks = krnLoadModuleByName('blocks', $this->page['Id']);
		$this->blocks = $Blocks->GetPageBlocks();
		foreach ($this->blocks as $i => $block) {
			$result = str_replace('<%BLOCK' . ($i + 1) . '%>', $block, $result);
		}
		return $result;
	}

}

?>