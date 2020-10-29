<?php

krnLoadLib('define');
krnLoadLib('settings');
krnLoadLib('modal');

class services extends krn_abstract{	

	public function __construct(){
		global $_LEVEL;
		parent::__construct();

		if ($this->serviceCode = $_LEVEL[1]) {
			$query = 'SELECT s.Id, s.Code, s.Title, s.Image, s.Button, '
					.'p.Id AS PageId, p.Content, p.Header, p.SeoTitle, p.SeoKeywords, p.SeoDescription, p.TemplateCode '
					.'FROM services s '
					.'LEFT JOIN static_pages p ON s.Code = p.Code '
					.'WHERE s.Code = ?s AND s.Lang = ?i AND p.Lang = ?i';
			$this->service = $this->db->getRow($query, $this->serviceCode, $this->lang->GetId(), $this->lang->GetId());
			if (!$this->service) {
				$this->notFound = true;
			}
			//ServiceProjectsRecsOnPage

			$this->pageTitle = $this->service['SeoTitle'] ?: $this->service['Title'];
			//$this->breadCrumbs = GetBreadCrumbs(array(
			//	$this->lang->GetValue('PAGE_HOME') => '/'),
			//	$this->pageTitle);
			$this->breadCrumbs = '';
		}

		if (!$this->service) {
			$this->notFound = true;
		}
	}	

	public function GetResult() {
		krnLoadLib('modal');
		global $Site;

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

		$modalCalculation = new Modal('calculation', [
			'Action' => '/ajax--act-Feedback/', 
			'Title' => $this->service['Button'],
			'Subject' => $this->service['Button'],
		]);
		$modalRequest = new Modal('request', ['Action' => '/ajax--act-Feedback/', 'Title' => 'Оставить заявку']);
		$Site->addModal($modalCalculation->GetModal());
		$Site->addModal($modalRequest->GetModal());

		$this->blocks = krnLoadModuleByName('blocks', $this->service['PageId']);
		$blocks = $this->blocks->GetPageBlocks($this->service);

		$header = $this->service['Header'] ?: $this->pageTitle;

		$result = krnLoadPageByTemplate('base_service');
		$result = strtr($result, array(
			'<%META_KEYWORDS%>'		=> $this->service['SeoKeywords'] ?: $Config['Site']['Keywords'],
			'<%META_DESCRIPTION%>'	=> $this->service['SeoDescription'] ?: $Config['Site']['Description'],
	    	'<%PAGE_TITLE%>'		=> $this->service['SeoTitle'] ?: $this->pageTitle,
	    	//'<%BREAD_CRUMBS%>'		=> $this->breadCrumbs,
	    	'<%BREAD_CRUMBS%>'		=> '',
	    	'<%CLASS%>'				=> mb_strlen($header) > 50 ? ' class="wide"' : '',
	    	'<%TITLE%>'				=> $header,
	    	'<%IMAGE%>'				=> $this->service['Image'],
	    	'<%TEXT%>'				=> $this->service['Content'],
	    	'<%BUTTON%>'			=> $this->service['Button'],
		));

		foreach ($blocks as $i => $block) {
			$result = str_replace('<%BLOCK' . ($i + 1) . '%>', $block, $result);
		}
				
		return $result;
	}

	public function GetMoreProjectsByPage() {
		$this->pageIndex = (int)$_POST['pageIndex'];

		$this->recsOnPage = $this->settings->GetSetting('ServiceProjectsRecsOnPage', 12);
		$query = 'SELECT COUNT(p.Id) '
				.'FROM cat_projects p '
				.'LEFT JOIN rel_projects_services p2s ON p2s.ProjectId = p.Id '
				.'WHERE p2s.ServiceId = ?i AND p.Lang = ?i';
		$this->totalCount = $this->db->getOne($query, $this->service['Id'], $this->lang->GetId());
		
		$element = LoadTemplate('bl_service_portfolio_el');
		$content = '';
		$query = 'SELECT p.Title, p.Image1204_766 AS Image, p.Code, '
				.'c.Code AS CategoryCode '
				.'FROM cat_projects p '
				.'LEFT JOIN rel_projects_services p2s ON p2s.ProjectId = p.Id '
				.'LEFT JOIN cat_categories c ON c.Id = p.CategoryId '
				.'WHERE p2s.ServiceId = ?i AND p.Lang = ?i '
				.'ORDER BY IF(p2s.`Order`, -100/p2s.`Order`, 0), IF(p.`Order`, -100/p.`Order`, 0) '
				.'LIMIT ?i, ?i';
		$items = $this->db->getAll($query, 
			$this->service['Id'], 
			$this->lang->GetId(),
			($this->pageIndex - 1) * $this->recsOnPage,
			$this->recsOnPage);
		$even = (($this->pageIndex - 1) * $this->recsOnPage) % 2 != 0;
		foreach ($items as $i => $item) {
			$link = '/projects/' . $item['CategoryCode'] . '/' . $item['Code'] . '/';
			$alt = htmlspecialchars($item['Title'], ENT_QUOTES);
			$image = '<a href="' . $link . '" class="projects-item-photo"><img src="' . $item['Image'] . '" alt="' . $alt . '"></a>';
			$content .= strtr($element, [
				'<%LINK%>'		=> $link,
				'<%TITLE%>'		=> $item['Title'],
				'<%ALT%>'		=> $alt,
				'<%LANG::DETAILS%>'	=> $this->lang->GetValue('DETAILS'),
				'<%BEFORE%>'	=> $even ? '' : $image,
				'<%AFTER%>'		=> !$even ? '' : $image,
			]);
			$even = !$even;
		}

		$more = $this->recsOnPage * $this->pageIndex < $this->totalCount;

		$json = array(
			'more' => $more,
			'html' => $content,
		);
		
		return json_encode($json);
	}
}

?>