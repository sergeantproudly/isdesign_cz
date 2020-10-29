<?php

krnLoadLib('define');
krnLoadLib('settings');
krnLoadLib('tabs');

class projects extends krn_abstract{	

	public function __construct(){
		global $_LEVEL;
		parent::__construct();

		$this->page = $this->db->getRow('SELECT Id, Code, Title, Header FROM static_pages WHERE Code = ?s AND Lang = ?i', 'projects', $this->lang->GetId());

		if (preg_match('/^[\d]+$/', $_LEVEL[4], $m) || preg_match('/^[\d]+$/', $_LEVEL[3], $m)) $this->pageIndex = $m[0];

		if ($_LEVEL[3] && !preg_match('/^[\d]+$/', $_LEVEL[3])) {
			$this->projectCode = $_LEVEL[3];
			$query = 'SELECT p.Id, p.Title, p.Text, p.Header, p.SeoTitle, p.SeoKeywords, p.SeoDescription, '
					.'c.Title AS CategoryTitle, c.Code AS CategoryCode '
					.'FROM cat_projects p '
					.'LEFT JOIN cat_categories c ON p.CategoryId = c.Id '
					.'WHERE p.Code = ?s AND p.Lang = ?i';
			$this->project = $this->db->getRow($query, $this->projectCode, $this->lang->GetId());
			if (!$this->project) {
				$this->notFound = true;
			}

			$this->recsOnPage = $this->settings->GetSetting('PhotosRecsOnPage', 12);
			$this->totalCount = $this->GetPhotosCount();

			$this->pageTitle = $this->project['SeoTitle'] ?: $this->project['Title'];
			$this->breadCrumbs = GetBreadCrumbs(array(
				$this->lang->GetValue('PAGE_HOME') => '/',
				$this->page['Title'] => '/' . $this->page['Code'] . '/',
				$this->project['CategoryTitle'] => '/' . $this->page['Code'] . '/' . $this->project['CategoryCode'] . '/#projects-' . $this->project['CategoryCode']),
				$this->pageTitle);

		} elseif (!preg_match('/^[\d]+$/', $_LEVEL[2])) {
			$this->categoryCode = $_LEVEL[2];
			if (!$this->categoryCode) $this->categoryCode = $this->db->getOne('SELECT Code FROM cat_categories WHERE Lang = ?i ORDER BY IF(`Order`, -100/`Order`, 0), Title LIMIT 0, 1', $this->lang->GetId());

			$query = 'SELECT c.Id, c.Title, c.Code, c.Header, c.SeoTitle, c.SeoKeywords, c.SeoDescription '
					.'FROM cat_categories c '
					.'WHERE c.Code = ?s';
			$this->category = $this->db->getRow($query, $this->categoryCode);
			if (!$this->category) {
				$this->notFound = true;
			}

			$this->pageTitle = $this->category['SeoTitle'] ?: $this->category['Title'];
			$this->breadCrumbs = GetBreadCrumbs(array(
				$this->lang->GetValue('PAGE_HOME') => '/',
				$this->page['Title'] => '/' . $this->page['Code'] . '/'),
				$this->pageTitle);
		}
	}	

	public function GetResult() {
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

		$this->blocks = krnLoadModuleByName('blocks', $this->page['Id']);
		$blocks = $this->blocks->GetPageBlocks();

		if ($this->project) {
			$content = $this->GetProject();

			$items = $this->db->getAll('SELECT Image AS ImageFull FROM cat_project_photos WHERE ProjectId = ?i AND Lang = ?i ORDER BY IF (`Order`, -100/`Order`, 0) LIMIT ?i, ?i', 
			$this->project['Id'], 
			$this->lang->GetId(),
			$this->recsOnPage,
			$this->totalCount);
			foreach ($items as $item) {
				$content .= '<a href="' . $item['ImageFull'] . '" class="js-lightbox empty" data-gallery="reviews"></a>';
			}

			$more = $this->recsOnPage * $this->pageIndex < $this->totalCount ? GetMore([
				'link'		=> '/projects/' . $this->project['CategoryCode'] . '/' . ($this->pageIndex + 1) . '/',
				'function'	=> 'photosMore();'
			]) : '';

			$result = krnLoadPageByTemplate('project');
			$result = strtr($result, array(
				'<%META_KEYWORDS%>'		=> $this->project['SeoKeywords'] ?: $Config['Site']['Keywords'],
				'<%META_DESCRIPTION%>'	=> $this->project['SeoDescription'] ?: $Config['Site']['Description'],
		    	'<%PAGE_TITLE%>'		=> $this->project['SeoTitle'] ?: $this->pageTitle,
		    	'<%BREAD_CRUMBS%>'		=> $this->breadCrumbs,
		    	'<%TITLE%>'				=> $this->project['Header'] ?: $this->pageTitle,
		    	'<%TEXT%>'				=> $this->project['Text'],
		    	'<%PAGEINDEX%>'			=> $this->pageIndex,
		    	'<%PROJECTCODE%>'		=> $this->projectCode,
		    	'<%CATEGORYCODE%>'		=> $this->project['CategoryCode'],
		    	'<%CONTENT%>'			=> $content,
		    	'<%MORE%>'				=> $more,
			));

		} else {
			$tabs['#projects-new'] = $this->lang->GetValue('CATEGORY_NEW');
			$items = $this->db->getAll('SELECT Title, Code FROM cat_categories WHERE Lang = ?i ORDER BY IF(`Order`, -100/`Order`, 0), Title', $this->lang->GetId());
			foreach ($items as $item) {
				$tabs['#projects-' . $item['Code']] = $item['Title'];
			}
			$tabs = new Tabs([
				'items'	=> $tabs,
				'classHolder' => 'projects-info-header',
			]);

			$content = $this->GetCategory();
			$result = krnLoadPageByTemplate('projects');
			$result = strtr($result, array(
				'<%META_KEYWORDS%>'		=> $this->category['SeoKeywords'] ?: $Config['Site']['Keywords'],
				'<%META_DESCRIPTION%>'	=> $this->category['SeoDescription'] ?: $Config['Site']['Description'],
		    	'<%PAGE_TITLE%>'		=> $this->category['SeoTitle'] ?: $this->pageTitle,
		    	'<%BREAD_CRUMBS%>'		=> $this->breadCrumbs,
		    	'<%TITLE%>'				=> $_LEVEL[3] ? ($this->category['Header'] ?: $this->pageTitle) : ($this->page['Header'] ?: $this->page['Title']),
		    	'<%TABS%>'				=> $tabs->GetTabs(),
		    	'<%CONTENT%>'			=> $content,
			));
		}

		foreach ($blocks as $i => $block) {
			$result = str_replace('<%BLOCK' . ($i + 1) . '%>', $block, $result);
		}
				
		return $result;
	}

	public function GetCategory() {
		$element = LoadTemplate('projects_categories_el');
		$element2 = LoadTemplate('projects_el');
		$content = '';

		// new projects
		$content2 = '';
		$projects = $this->db->getAll('SELECT p.Title, p.Code, p.Text, p.Image1204_766 AS Image, c.Code AS CategoryCode FROM cat_projects p LEFT JOIN cat_categories c ON p.CategoryId = c.Id WHERE p.IsNew = 1 AND p.Lang = ?i ORDER BY IF(p.`Order`, -100/p.`Order`, 0), p.Title', $this->lang->GetId());
		$even = false;
		foreach ($projects as $project) {
			$link = '/' . $this->page['Code'] . '/' . $project['CategoryCode'] . '/' . $project['Code'] . '/';
			$alt = htmlspecialchars($project['Title'], ENT_QUOTES);
			$image = '<a href="' . $link . '" class="projects-item-photo"><img src="' . $project['Image'] . '" alt="' . $alt . '"></a>';
			$content2 .= strtr($element2, [
				'<%LINK%>'	=> $link,
				'<%ALT%>'	=> $alt,
				'<%TITLE%>'	=> $project['Title'],
				'<%IMAGE%>'	=> $project['Image'],
				'<%BEFORE%>'	=> $even ? '' : $image,
				'<%AFTER%>'		=> !$even ? '' : $image,
			]);
			$even = !$even;
		}

		$content .= strtr($element, [
			'<%CLASS%>'		=> ' active',
			'<%CODE%>'		=> 'new',
			'<%CONTENT%>'	=> $content2,
		]);

		// categories
		$items = $this->db->getAll('SELECT Id, Title, Code FROM cat_categories WHERE Lang = ?i ORDER BY IF(`Order`, -100/`Order`, 0), Title', $this->lang->GetId());
		foreach ($items as $item) {
			$content2 = '';
			$projects = $this->db->getAll('SELECT Title, Code, Text, Image1204_766 AS Image FROM cat_projects WHERE CategoryId =?i AND Lang = ?i ORDER BY IF(`Order`, -100/`Order`, 0), Title', $item['Id'], $this->lang->GetId());
			$even = false;
			foreach ($projects as $project) {
				$link = '/' . $this->page['Code'] . '/' . $item['Code'] . '/' . $project['Code'] . '/';
				$alt = htmlspecialchars($project['Title'], ENT_QUOTES);
				$image = '<a href="' . $link . '" class="projects-item-photo"><img src="' . $project['Image'] . '" alt="' . $alt . '"></a>';
				$content2 .= strtr($element2, [
					'<%LINK%>'	=> $link,
					'<%ALT%>'	=> $alt,
					'<%TITLE%>'	=> $project['Title'],
					'<%IMAGE%>'	=> $project['Image'],
					'<%BEFORE%>'	=> $even ? '' : $image,
					'<%AFTER%>'		=> !$even ? '' : $image,
				]);
				$even = !$even;
			}

			$content .= strtr($element, [
				'<%CLASS%>'		=> '',
				'<%CODE%>'		=> $item['Code'],
				'<%CONTENT%>'	=> $content2,
			]);
		}

		return $content;
	}

	public function GetProject() {
		$element = LoadTemplate('project_photos');
		$elementPhoto = LoadTemplate('project_photos_el');
		$elementPhoto2 = LoadTemplate('project_photos_el2');
		$content = '';

		$items = $this->db->getAll('SELECT Title, Image1570_1262 AS ImageBig, Image1062_628 AS ImageMid, Image AS ImageFull FROM cat_project_photos WHERE ProjectId = ?i AND Lang = ?i ORDER BY IF (`Order`, -100/`Order`, 0) LIMIT ?i, ?i', 
			$this->project['Id'], 
			$this->lang->GetId(),
			($this->pageIndex - 1) * $this->recsOnPage,
			$this->recsOnPage);
		$counter = (($this->pageIndex - 1) * $this->recsOnPage) % 3;
		$even = (($this->pageIndex - 1) * $this->recsOnPage) % 2 != 0;
		foreach ($items as $item) {
			if (!$even) {
				if ($counter == 1) $content2 .= '<div class="photo-item">';
				$content2 .= strtr($counter == 0 ? $elementPhoto : $elementPhoto2, [
					'<%ALT%>'	=> htmlspecialchars($item['Title'], ENT_QUOTES),
					'<%IMAGE%>'	=> '/' . $item[$counter == 0 ? 'ImageBig' : 'ImageMid'],
					'<%IMAGEFULL%>' => '/' . $item['ImageFull'],
				]);
				if ($counter == 2) $content2 .= '</div>';
				
			} else {
				if ($counter == 0) $content2 .= '<div class="photo-item">';
				$content2 .= strtr($counter == 2 ? $elementPhoto : $elementPhoto2, [
					'<%ALT%>'	=> htmlspecialchars($item['Title'], ENT_QUOTES),
					'<%IMAGE%>'	=> '/' . $item[$counter == 2 ? 'ImageBig' : 'ImageMid'],
					'<%IMAGEFULL%>' => '/' . $item['ImageFull'],
				]);
				if ($counter == 1) $content2 .= '</div>';
			}
			
			if ($counter >= 2) {
				$content .= SetContent($element, $content2);

				$counter = 0;
				$content2 = '';
				$even = !$even;

			} else $counter++;
		}
		if ($content2) {
			$content .= SetContent($element, $content2);
		}

		return $content;
	}

	public function GetProjectsCount() {
		return $this->db->getAll('SELECT COUNT(Id) FROM cat_projects WHERE CategoryId =?i AND Lang = ?i', $item['Id'], $this->lang->GetId());
	}

	public function GetPhotosCount() {
		return $this->db->getOne('SELECT COUNT(Id) FROM cat_project_photos WHERE ProjectId = ?i AND Lang = ?i', $this->project['Id'], $this->lang->GetId());
	}

	public function GetMorePhotosByPage() {
		$result = $this->GetProject();
		$more = $this->recsOnPage * $this->pageIndex < $this->totalCount;

		if ($more) {
			$items = $this->db->getAll('SELECT Image AS ImageFull FROM cat_project_photos WHERE ProjectId = ?i AND Lang = ?i ORDER BY IF (`Order`, -100/`Order`, 0) LIMIT ?i, ?i', 
			$this->project['Id'], 
			$this->lang->GetId(),
			$this->$this->recsOnPage * ($this->pageIndex + 1),
			$this->totalCount);
			foreach ($items as $item) {
				$result .= '<a href="' . $item['ImageFull'] . '" class="js-lightbox empty" data-gallery="reviews"></a>';
			}
		}

		$json = array(
			'more' => $more,
			'html' => $result,
		);
		
		return json_encode($json);
	}
}

?>