<?php

krnLoadLib('define');
krnLoadLib('settings');

class contact extends krn_abstract{	

	function __construct() {
		parent::__construct();

		$this->page = $this->db->getRow('SELECT Title, Header, Code, Content, SeoTitle, SeoKeywords, SeoDescription, TemplateCode FROM static_pages WHERE Code = ?s AND Lang = ?i', 'contact', $this->lang->GetId());

		$this->pageTitle = $this->page['Header'] ?: $this->page['Title'];
	}	

	function GetResult() {
		$this->blocks = krnLoadModuleByName('blocks');

		if ($this->notFound) {
			header('HTTP/1.1 404 Not Found');
			header('Status: 404 Not Found');
			$this->pageTitle = $this->lang->GetValue('ERROR404_HEADER');
			$result = krnLoadPageByTemplate('base_static');
			$result = strtr($result, array(
				'<%PAGE_TITLE%>'		=> $this->pageTitle,
				'<%BREAD_CRUMBS%>'		=> $this->breadCrumbs,
				'<%TITLE%>'				=> $this->pageTitle,
				'<%CONTENT%>'			=> LoadTemplate('404'),
			));
			return $result;
		}

		$contactsContent = '';
		$contact = $this->db->GetRow('SELECT * FROM contacts WHERE Lang = ?i', $this->lang->GetId());
		if ($contact['Address']) $contactsContent .= '<h4>Address</h4><p>' . $contact['Address'] . '</p>';
		if ($contact['Tel1'] || $contact['Tel2']) $contactsContent .= '<h4>Phone</h4><p id="tel">' . ($contact['Tel1'] ? '<a href="tel:'.preg_replace('/[^\d\+]/', '', $contact['Tel1']).'">'.$contact['Tel1'].'</a><br>' : '') . ($contact['Tel2'] ? '<a href="tel:'.preg_replace('/[^\d\+]/', '', $contact['Tel2']).'">'.$contact['Tel2'].'</a>' : '') . '</p>';
		if ($contact['Email1'] || $contact['Email2']) $contactsContent .= '<h4>E-mail</h4><p id="email">' . ($contact['Email1'] ? '<a href="mailto:'.$contact['Email1'].'">'.$contact['Email1'].'</a>' : '') . ($contact['Email2'] ? '<a href="mailto:'.$contact['Email2'].'">'.$contact['Email2'].'</a>' : '') . '</p>';

		$element = LoadTemplate('bl_social_el');
		$content = '';
		$items = $this->db->getAll('SELECT Title, Image, Link FROM social WHERE Image <> "" ORDER BY IF(`Order`, -100/`Order`, 0)');
		foreach ($items as $item) {
			$content .= strtr($element, [
				'<%ALT%>'		=> htmlspecialchars($item['Title'], ENT_QUOTES),
				'<%LINK%>'		=> $item['Link'],
				'<%IMAGE%>'		=> $item['Image'],
			]);
		}
		$social = SetContent(LoadTemplate('bl_social'), $content);
		
		$result = krnLoadPageByTemplate($this->page['TemplateCode'] ?: 'base_page');
		$result = strtr($result, array(
			'<%META_KEYWORDS%>'		=> $this->folder['SeoKeywords'] ?: $this->page['SeoKeywords'],
			'<%META_DESCRIPTION%>'	=> $this->folder['SeoDescription'] ?: $this->page['SeoDescription'],
	    	'<%PAGE_TITLE%>'		=> $this->folder['SeoTitle'] ?: ($this->page['SeoTitle'] ?: $this->pageTitle),
	    	'<%BREAD_CRUMBS%>'		=> '',
	    	'<%TITLE%>'				=> $this->pageTitle,
	    	'<%TEXT%>'				=> $this->page['Content'],
	    	'<%CONTENT%>'			=> $contactsContent,
	    	'<%SOCIAL%>'			=> $social,
	    	'<%MAPCODE%>'			=> $contact['MapCode'],
		));
				
		return $result;
	}
}

?>