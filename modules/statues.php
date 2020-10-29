<?php

krnLoadLib('define');
krnLoadLib('settings');
krnLoadLib('tabs');

class statues extends krn_abstract {

	public function __construct(){
		global $_LEVEL;
		parent::__construct();

		$this->page = $this->db->getRow('SELECT Id, Title, Code, Content, Header, SeoTitle, SeoKeywords, SeoDescription FROM static_pages WHERE Code = ?s AND Lang = ?i', 'statues', $this->lang->GetId());

		if ($_LEVEL[2] && !preg_match('/^[\d]+$/', $_LEVEL[2])) {
			$this->statueCode = $_LEVEL[2];
			$query = 'SELECT s.Title, s.Code, s.Date, s.ImageFull, s.Text, s.Header, s.SeoTitle, s.SeoKeywords, s.SeoDescription '
					.'FROM statues s '
					.'WHERE s.Code = ?s AND Lang = ?i';
			$this->statue = $this->db->getRow($query, $this->statueCode, $this->lang->GetId());
			if (!$this->statue) {
				$this->notFound = true;
			}

			$this->pageTitle = $this->statue['SeoTitle'] ?: $this->statue['Title'];
			$this->breadCrumbs = GetBreadCrumbs(array(
				$this->lang->GetValue('PAGE_HOME') => '/',
				$this->page['Title'] => '/' . $this->page['Code'] . '/'),
				$this->pageTitle);

		} else {
			if (preg_match('/^[\d]+$/', $_LEVEL[2], $m)) {
				$this->pageIndex = $m[0];
			}
			$this->recsOnPage = $this->settings->GetSetting('StatuesRecsOnPage', 12);
			$this->totalCount = $this->GetStatuesCount();

			$this->pageTitle = $this->page['SeoTitle'] ?: $this->page['Title'];
			$this->breadCrumbs = '';
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

		if ($this->statue) {
			$result = krnLoadPageByTemplate('statue');
			$result = strtr($result, array(
				'<%META_KEYWORDS%>'		=> $this->statue['SeoKeywords'] ?: $Config['Site']['Keywords'],
				'<%META_DESCRIPTION%>'	=> $this->statue['SeoDescription'] ?: $Config['Site']['Description'],
		    	'<%PAGE_TITLE%>'		=> $this->statue['SeoTitle'] ?: $this->pageTitle,
		    	'<%BREAD_CRUMBS%>'		=> $this->breadCrumbs,
		    	'<%IMAGE%>'				=> $this->statue['ImageFull'],
		    	'<%TITLE%>'				=> $this->statue['Header'] ?: $this->pageTitle,
		    	'<%TEXT%>'				=> $this->statue['Text'],
			));

		} else {
			$content = $this->GetStatues();
			
			$more = $this->recsOnPage * $this->pageIndex < $this->totalCount ? GetMore([
				'link'		=> '/statues/' . ($this->pageIndex + 1) . '/',
				'function'	=> 'statuesMore();'
			]) : '';

			$result = krnLoadPageByTemplate('statues');
			$result = strtr($result, array(
				'<%META_KEYWORDS%>'		=> $this->page['SeoKeywords'] ?: $Config['Site']['Keywords'],
				'<%META_DESCRIPTION%>'	=> $this->page['SeoDescription'] ?: $Config['Site']['Description'],
		    	'<%PAGE_TITLE%>'		=> $this->page['SeoTitle'] ?: $this->pageTitle,
		    	'<%BREAD_CRUMBS%>'		=> $this->breadCrumbs,
		    	'<%TITLE%>'				=> $this->page['Header'] ?: $this->pageTitle,
		    	'<%PAGEINDEX%>'			=> $this->pageIndex,
		    	'<%CONTENT%>'			=> $content,
		    	'<%MORE%>'				=> $more,
			));
		}

		foreach ($blocks as $i => $block) {
			$result = str_replace('<%BLOCK' . ($i + 1) . '%>', $block, $result);
		}
				
		return $result;
	}

	public function GetStatuesCount() {
		return $this->db->getOne('SELECT COUNT(Id) FROM statues WHERE Lang = ?i', $this->lang->GetId());
	}

	public function GetStatues() {
		$element = LoadTemplate('statues_el');
		$element2 = LoadTemplate('statues_el2');
		$content = '';

		$items = $this->db->getAll('SELECT Title, Code, Image1026_1160 AS Image, Image1026_636 AS Image2, Announce AS Text, Date FROM statues WHERE Lang = ?i ORDER BY Date DESC LIMIT ?i, ?i', 
			$this->lang->GetId(),
			($this->pageIndex - 1) * $this->recsOnPage,
			$this->recsOnPage);
		$mod = count($items) % 2;
		$even = (($this->pageIndex - 1) * $this->recsOnPage) % 2 != 0;
		$counter = 0;
		foreach ($items as $item) {
			$counter++;
			if (!$mod && $counter == (count($items) / 2) + 1) {
				$even = !$even;
			}
			$content .= strtr(!$even ? $element : $element2, [
				'<%LINK%>'		=> '/' . $this->page['Code'] . '/' . $item['Code'] . '/',
				'<%IMAGE%>'		=> $item[!$even ? 'Image' : 'Image2'],
				'<%TITLE%>'		=> $item['Title'],
				'<%ALT%>'		=> htmlspecialchars($item['Title'], ENT_QUOTES),
				'<%TEXT%>'		=> $item['Text'],
				'<%DATE%>'		=> ModifiedDate($item['Date']),
			]);

			$even = !$even;
		}

		return $content;
	}

	public function GetMoreByPage() {		
		$result = $this->GetStatues();
		$more = $this->recsOnPage * $this->pageIndex < $this->totalCount;

		$json = array(
			'more' => $more,
			'html' => $this->lang->ProcessTemplate($result),
		);
		
		return json_encode($json);
	}
}

?>