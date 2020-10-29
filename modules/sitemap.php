<?php

krnLoadLib('define');
krnLoadLib('settings');

class sitemap extends krn_abstract {

	protected $filelabel = 'sitemap';
	
	public function __construct() {
		parent::__construct();
	}
	
	public function GetResult() {
		$pages = $this->GetPages();
		echo $this->GetSitemap($pages);
		exit;
	}
	
	public function GetPages() {
		$time = time();
		
		//statics
		$items = $this->db->getAll('SELECT Code, LastModTime FROM static_pages WHERE Lang = ?i ORDER BY IF(`Order`,-1000/`Order`,0)', $this->lang->GetId());
		foreach ($items as $item){
			if ($item['Code'] != 'services' && $item['Code'] != 'news' && $item['Code'] != 'works') {
				if ($item['Code'] == 'main') {
					$pages[''] = $item['LastModTime'];

				} else {
					$pages[$item['Code']] = $item['LastModTime'];
				}
			}			
		}

		// services
		$items = $this->db->getAll('SELECT Title, Code FROM services WHERE Lang = ?i ORDER BY IF(`Order`,-1000/`Order`,0)', $this->lang->GetId());
		foreach ($items as $item) {
			$pages[$item['Code']] = $item['LastModTime'];		
		}

		// news
		$items = $this->db->getAll('SELECT Code, LastModTime FROM statues WHERE Lang = ?i ORDER BY Date DESC', $this->lang->GetId());
		foreach ($items as $item) {
			$pages['statues/' . $item['Code']] = $item['LastModTime'];
		}
		
		// projects
		$items = $this->db->getAll('SELECT Code, LastModTime FROM cat_categories WHERE Lang = ?i ORDER BY IF(`Order`,-100/`Order`,0)', $this->lang->GetId());
		foreach ($items as $item) {
			$pages['projects/' . $item['Code']] = $item['LastModTime'];
		}
		$items = $this->db->getAll('SELECT p.Code, p.LastModTime, c.Code AS CategoryCode FROM cat_projects p LEFT JOIN cat_categories c ON p.CategoryId = c.Id  WHERE p.Lang = ?i ORDER BY IF(p.`Order`,-100/p.`Order`,0)', $this->lang->GetId());
		foreach ($items as $item) {
			$pages['projects/' . $item['CategoryCode'] . '/' . $item['Code']] = $item['LastModTime'];
		}
		return $pages;
	}
	
	public function GetSitemap($pages) {
		global $Settings;
		$siteUrl = $Settings->GetSetting('SiteUrl',$Config['Site']['Url']);
		
		$xml = new DomDocument('1.0', 'utf8');
		
		$urlset = $xml->appendChild($xml->createElement('urlset'));
		$urlset->setAttribute('xmlns:xsi','http://www.w3.org/2001/XMLSchema-instance');
		$urlset->setAttribute('xsi:schemaLocation','http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd');
		$urlset->setAttribute('xmlns','http://www.sitemaps.org/schemas/sitemap/0.9');
		
		if (!count($pages)) return false;
		foreach ($pages as $page => $lastmodtime) {
			$url=$urlset->appendChild($xml->createElement('url'));
			$loc=$url->appendChild($xml->createElement('loc'));
			$loc->appendChild($xml->createTextNode($siteUrl . (substr($siteUrl,-1)!='/'?'/':'') . ($page ? $page . '/' : '')));
			$lastmod=$url->appendChild($xml->createElement('lastmod'));
			$lastmod->appendChild($xml->createTextNode(date('c',$lastmodtime?$lastmodtime:time())));
			$changefreq=$url->appendChild($xml->createElement('changefreq'));
			$changefreq->appendChild($xml->createTextNode('daily'));
			$priority=$url->appendChild($xml->createElement('priority'));
			$priority->appendChild($xml->createTextNode('0.5'));
		}
		
		$xml->formatOutput = true;
		$xml->save($this->filelabel . '.xml');
	}
	
	public function Generate($langId) {
		$this->lang->SetLangById($langId);
		$this->filelabel = 'sitemap_' . $this->lang->GetLang('Acronym');

		$pages = $this->GetPages();
		$this->GetSitemap($pages);
	}
	
}

?>