<?php

krnLoadLib('define');
krnLoadLib('settings');
krnLoadLib('tabs');

class blocks extends krn_abstract{
	
	private $page_id;
	private $blocks_sequence = array();
	private $blocks_info = array();
	private $forms_info = array();
	private $rel_codes_methods = array(
	);
	private $flag = false;

	public function __construct($pageId = false) {
		parent::__construct();

		global $Params;
		$this->page_id = $pageId ?: $Params['Site']['Page']['Id'];
		
		$query = 'SELECT p2b.*, b.Code '
				.'FROM `rel_pages_blocks` AS p2b '
				.'LEFT JOIN `blocks` AS b ON p2b.BlockId = b.Id '
				.'LEFT JOIN `static_pages` AS s ON p2b.PageId = s.Id '
				.'WHERE s.Id = ?s AND IsActive = 1 AND p2b.Lang = ?i '
				.'ORDER BY IF(p2b.`Order`,-30/p2b.`Order`,0)';
		$blocks = $this->db->getAll($query, $this->page_id, $this->lang->GetId());
		foreach ($blocks as $block) {
			$this->blocks_sequence[] = $block['Code'];
			$this->blocks_info[$block['Code']][] = $block;
		}
		
		$forms = $this->db->getAll('SELECT * FROM `forms`');
		foreach ($forms as $form) {
			$this->forms_info[$form['Code']] = $form;
		}
	}
	
	public function GetResult() {}

	public function GetPageBlocks($data = array()) {
		$html = [];
		$counter = [];
		foreach ($this->blocks_sequence as $code) {
			if (!isset($counter[$code])) $counter[$code] = 0;

			if (isset($this->rel_codes_methods[$code])) {
				$func = $this->rel_codes_methods[$code];
				$code_param = $code;
			} else {
				$func = 'Block';
				foreach (explode('_', $code) as $fragments) {
					$func .= ucfirst($fragments);
				}
			}

			$info['Index'] = $counter[$code];
			$info['Code'] = isset($code_param) ? $code_param : $code;
			if (method_exists($this, $func)) {
				$html[] = $this->$func($info, $data);
			} else {
				$html[] = $this->BlockText($info, $data);
			}

			$counter[$code]++;
		}
		return $html;
	}

	public function GetBlockParams($blockCode, $index = 0) {
		$params = [];
		foreach (explode(';', $this->blocks_info[$blockCode][$index]['Params']) as $line) {
			list($param, $value) = explode(':', $line, 2);
			$params[ucfirst(trim($param))] = trim($value);
		}
		return $params;
	}
	
	/** Блок - Текстовый */
	public function BlockText($data = array()) {
		$code = $data['Code'];
		$index = $data['Index'];
		$params = $this->GetBlockParams($code, $index);

		$result = LoadTemplate($code ? 'bl_'.$code : 'bl_text');
		$result = strtr($result, array(
			'<%CLASS%>'		=> $params['Class'] ?: '',
			'<%HEADER%>'	=> $this->blocks_info[$code][$index]['Header'] ? '<h2>'.$this->blocks_info[$code][$index]['Header'].'</h2>' : '',
			'<%TITLE%>'		=> $this->blocks_info[$code][$index]['Header'] ? '<h2>'.$this->blocks_info[$code][$index]['Header'].'</h2>' : '',
			'<%CONTENT%>'	=> $this->blocks_info[$code][$index]['Content'],
			'<%HR%>'		=> $params['Hr'] == 1 ? '<hr class="hr">' : '',
		));
		if ($params['ExcludedClass']) $result = str_replace($params['ExcludedClass'], '', $result);
		return $result;
	}

	/** Блок - Форма */
	public function BlockForm($data = array()) {
		$code = $data['Code'];
		$index = $data['Index'];
		$result = LoadTemplate($code);
		$result = strtr($result, array(
			'<%TITLE%>'	=> $this->forms_info[$code][$index]['Title'],
			'<%TEXT%>'	=> $this->forms_info[$code][$index]['Text'],
			'<%CODE%>'	=> $this->forms_info[$code][$index]['Code']
		));
		return $result;
	}

	/** Блок - Услуги шахматами */
	public function BlockServices($data = array()) {
		$code = 'services';
		$index = $data['Index'];

		$element = LoadTemplate('bl_services_el');
		$content = '';
		$items = $this->db->getAll('SELECT Title, TitleOnMain, Code, ImageOnMain1204_766 AS Image, Announce AS Text FROM services WHERE OnMain = 1 AND Lang = ?i ORDER BY IF(`Order`, -100/`Order`, 0)', $this->lang->GetId());
		$even = false;
		foreach ($items as $item) {
			$title = $item['TitleOnMain'] ?: $item['Title'];
			$link = '/' . $item['Code'] . '/';
			$image = '<a href="' . $link . '" class="projects-item-photo"><img src="' . $item['Image'] . '" alt="' . htmlspecialchars($title, ENT_QUOTES) . '"></a>';
			$content .= strtr($element, [
				'<%TITLE%>'		=> $title,
				'<%LINK%>'		=> $link,
				'<%TEXT%>'		=> $item['Text'],
				'<%BEFORE%>'	=> !$even ? '' : $image,
				'<%AFTER%>'		=> $even ? '' : $image,
			]);
			$even = !$even;
		}

		$result = LoadTemplate('bl_services');
		$result = $content ? strtr($result, array(
			'<%TITLE%>'		=> $this->blocks_info[$code][$index]['Header'],
			'<%CONTENT%>'	=> $content,
		)) : '';
		return $result;
	}

	/** Блок - Услуги с затемнением */
	public function BlockServices2($data = array()) {
		$code = 'services';
		$index = $data['Index'];

		$element = LoadTemplate('bl_services2_el');
		$content = '';
		$items = $this->db->getAll('SELECT Title, TitleOnMain, Code, ImageOnMain1196_600 AS Image FROM services WHERE OnMain2 = 1 AND Lang = ?i ORDER BY IF(`Order`, -100/`Order`, 0)', $this->lang->GetId());
		foreach ($items as $item) {
			$title = $item['TitleOnMain'] ?: $item['Title'];
			$link = '/' . $item['Code'] . '/';
			$content .= strtr($element, [
				'<%TITLE%>'		=> $title,
				'<%LINK%>'		=> $link,
				'<%IMAGE%>'		=> $item['Image'],
			]);
		}

		$result = LoadTemplate('bl_services2');
		$result = $content ? strtr($result, array(
			'<%TITLE%>'		=> $this->blocks_info[$code][$index]['Header'],
			'<%CONTENT%>'	=> $content,
		)) : '';
		return $result;
	}

	/** Блок - Проекты */
	public function BlockProjects($data = array()) {
		$code = 'projects';
		$index = $data['Index'];

		$query = 'SELECT pp.Title, Image3840 AS Image, c.Code AS CategoryCode, p.IsNew '
				.'FROM cat_project_photos pp '
				.'LEFT JOIN cat_projects p ON pp.ProjectId = p.Id '
				.'LEFT JOIN cat_categories c ON p.CategoryId = c.Id '
				.'WHERE pp.OnMain = 1 AND pp.Lang = ?i '
				.'ORDER BY p.CategoryId, IF(pp.`Order`, -100/pp.`Order`, 0)';
		$items = $this->db->getAll($query, $this->lang->GetId());
		$photos = [];
		foreach ($items as $item) {
			$photos[$item['CategoryCode']][] = $item;
			if ($item['IsNew']) $photos['new'][] = $item;
		}

		$items = $this->db->getAll('SELECT Title, Code FROM cat_categories WHERE Lang = ?i ORDER BY IF(`Order`, -100/`Order`, 0)', $this->lang->GetId());
		$categories[] = 'new';
		$tabs['#projects-new'] = $this->lang->GetValue('CATEGORY_NEW');
		foreach ($items as $item) {
			$categories[] = $item['Code'];
			if (isset($photos[$item['Code']]) && count($photos[$item['Code']]))
				$tabs['#projects-' . $item['Code']] = $item['Title'];
		}

		$tabs = new Tabs([
			'items'	=> $tabs,
			'classHolder' => 'btn-wrap',
		]);

		$element = LoadTemplate('bl_projects_el');
		$element2 = LoadTemplate('bl_projects_el2');
		$autoseconds = $this->settings->GetSetting('SliderAutotime');
		$content = '';

		foreach ($categories as $categoryCode) {
			$content2 = '';
			$dots = '';
			if (isset($photos[$categoryCode]) && count($photos[$categoryCode])) {
				foreach ($photos[$categoryCode] as $photo) {
					$dots .= '<span class="dot' . ($content2 ? '' : ' dot-active') . '"><i></i></span>';
					$content2 .= strtr($element2, [
						'<%TITLE%>'	=> htmlspecialchars($photo['Title']),
						'<%CLASS%>'	=> $content2 ? '' : ' active',
						'<%IMAGE%>'	=> $photo['Image'],
					]);
				}
				$content .= strtr($element, [
					'<%CLASS%>'			=> $content ? '' : ' active',
					'<%ID%>'			=> 'projects-' . $categoryCode,
					'<%AUTOSECONDS%>'	=> $autoseconds, 
					'<%CONTENT%>'		=> $content2,
					'<%DOTS%>'			=> $dots,
				]);
			}
		}

		$result = LoadTemplate('bl_projects');
		$result = $content ? strtr($result, array(
			'<%TITLE%>'		=> $this->blocks_info[$code][$index]['Header'],
			'<%TABS%>'		=> $tabs->getTabs(),
			'<%CONTENT%>'	=> $content,
		)) : '';
		return $result;
	}

	/** Блок - Компания */
	public function BlockCompany($data = array()) {
		$code = 'company';
		$index = $data['Index'];
		$params = $this->GetBlockParams($code, $index);

		$element = LoadTemplate('bl_about_el');
		$content = '';
		$items = $this->db->getAll('SELECT Name, Image490_597 AS Image, Post FROM team WHERE Image <> "" AND Lang = ?i ORDER BY IF(`Order`, -100/`Order`, 0)', $this->lang->GetId());
		$fst = false;
		foreach ($items as $item) {
			if (!$fst) $fst = $item;
			else $content .= strtr($element, [
				'<%NAME%>'		=> $item['Name'],
				'<%ALT%>'		=> htmlspecialchars($item['Name'], ENT_QUOTES),
				'<%POST%>'		=> $item['Post'],
				'<%IMAGE%>'		=> $item['Image'],
			]);
		}

		$result = LoadTemplate('bl_about');
		$result = $content ? strtr($result, array(
			'<%TITLE%>'		=> $this->blocks_info[$code][$index]['Header'],
			'<%TEXT%>'		=> $this->blocks_info[$code][$index]['Content'],
			'<%CLASS%>'		=> $params['Class'] ? ' class="' . $params['Class'] . '"' : '',
			'<%NAME%>'		=> $fst['Name'],
			'<%ALT%>'		=> htmlspecialchars($fst['Name'], ENT_QUOTES),
			'<%POST%>'		=> $fst['Post'],
			'<%IMAGE%>'		=> $fst['Image'],
			'<%CONTENT%>'	=> $content,
		)) : '';
		return $result;
	}

	/** Блок - Наши партнеры */
	public function BlockPartners($data = array()) {
		$code = 'partners';
		$index = $data['Index'];

		$element = LoadTemplate('bl_partners_el');
		$content = '';
		$items = $this->db->getAll('SELECT Title, Image155_68 AS Image FROM partners WHERE Image155_68 <> "" AND Lang = ?i ORDER BY IF(`Order`, -100/`Order`, 0)', $this->lang->GetId());
		foreach ($items as $item) {
			$content .= strtr($element, [
				'<%ALT%>'		=> htmlspecialchars($item['Title'], ENT_QUOTES),
				'<%IMAGE%>'		=> $item['Image'],
			]);
		}

		$result = LoadTemplate('bl_partners');
		$result = $content ? strtr($result, array(
			'<%TITLE%>'		=> $this->blocks_info[$code][$index]['Header'],
			'<%CONTENT%>'	=> $content,
		)) : '';
		return $result;
	}

	/** Блок - Instagram */
	public function BlockInstagram($data = array()) {
		$code = 'instagram';
		$index = $data['Index'];
		$params = $this->GetBlockParams($code, $index);

		$element = LoadTemplate('bl_instagram_el');
		$content = '';
		$items = $this->db->getAll('SELECT Title, Image AS ImageFull, Image624_624 AS Image FROM instagram_photos WHERE Image <> "" ORDER BY RAND()');
		foreach ($items as $item) {
			$content .= strtr($element, [
				'<%ALT%>'		=> htmlspecialchars($item['Title'], ENT_QUOTES),
				'<%IMAGEFULL%>'	=> $item['ImageFull'],
				'<%IMAGE%>'		=> $item['Image'],
			]);
		}

		$result = LoadTemplate('bl_instagram');
		$result = strtr($result, array(
			'<%TITLE%>'		=> $this->blocks_info[$code][$index]['Header'],
			'<%LINK%>'		=> $this->db->getOne('SELECT Link FROM social WHERE Id = ?i', INSTAGRAM_ID),
			'<%CONTENT%>'	=> $content,
		));
		return $result;
	}

	/** Блок - Контакты */
	public function BlockContacts($data = array()) {
		$code = 'contacts';
		$index = $data['Index'];
		$params = $this->GetBlockParams($code, $index);

		$contactsContent = '';
		$contact = $this->db->GetRow('SELECT * FROM contacts WHERE Lang = ?i', $this->lang->GetId());
		if ($contact['Address']) $contactsContent .= '<p>' . $contact['Address'] . '</p>';
		if ($contact['Tel1'] || $contact['Tel2']) $contactsContent .= '<p id="tel">' . ($contact['Tel1'] ? '<a href="tel:'.preg_replace('/[^\d\+]/', '', $contact['Tel1']).'">'.$contact['Tel1'].'</a><br>' : '') . ($contact['Tel2'] ? '<a href="tel:'.preg_replace('/[^\d\+]/', '', $contact['Tel2']).'">'.$contact['Tel2'].'</a>' : '') . '</p>';
		if ($contact['Email1'] || $contact['Email2']) $contactsContent .= '<p id="email">' . ($contact['Email1'] ? '<a href="mailto:'.$contact['Email1'].'">'.$contact['Email1'].'</a>' : '') . ($contact['Email2'] ? '<a href="mailto:'.$contact['Email2'].'">'.$contact['Email2'].'</a>' : '') . '</p>';
		
		$element = LoadTemplate('bl_contacts_el');
		$content = '';
		$items = $this->db->getAll('SELECT Title, Image, Link FROM social WHERE Image <> "" ORDER BY IF(`Order`, -100/`Order`, 0)');
		foreach ($items as $item) {
			$content .= strtr($element, [
				'<%ALT%>'		=> htmlspecialchars($item['Title'], ENT_QUOTES),
				'<%LINK%>'		=> $item['Link'],
				'<%IMAGE%>'		=> $item['Image'],
			]);
		}

		$result = LoadTemplate('bl_contacts');
		$result = strtr($result, array(
			'<%TITLE%>'		=> $this->blocks_info[$code][$index]['Header'],
			'<%TEXT%>'		=> $contactsContent,
			'<%CONTENT%>'	=> $content,
			'<%MAPCODE%>'	=> $contact['MapCode'],
		));
		return $result;
	}

	/** Блок - Преимущества компании */
	public function BlockAdvantages($data, $service) {
		$code = 'advantages';
		$index = $data['Index'];
		$params = $this->GetBlockParams($code, $index);

		$element = LoadTemplate('bl_advantages_el');
		$content = '';
		$items = $this->db->getAll('SELECT a.Title, a.Icon AS Image FROM rel_services_advantages a2s LEFT JOIN advantages a ON a2s.AdvantageId = a.Id WHERE ServiceId = ?i AND a.Lang = ?i ORDER BY IF(a2s.`Order`, -100/a2s.`Order`, 0), IF(a.`Order`, -100/a.`Order`, 0)', $service['Id'], $this->lang->GetId());
		foreach ($items as $item) {
			$content .= strtr($element, [
				'<%ALT%>'		=> htmlspecialchars($item['Title'], ENT_QUOTES),
				'<%TITLE%>'		=> $item['Title'],
				'<%IMAGE%>'		=> $item['Image'],
			]);
		}

		$result = LoadTemplate('bl_advantages');
		$result = strtr($result, array(
			'<%CONTENT%>'	=> $content,
			'<%HR%>'		=> $params['Hr'] == 1 ? '<hr class="hr">' : '',
		));
		return $result;
	}

	/** Блок - Преимущества (подробно) */
	public function BlockPoints($data, $service) {
		$code = $data['Code'];
		$index = $data['Index'];
		$params = $this->GetBlockParams($code, $index);
		
		$element = LoadTemplate('bl_points_el');
		$content = '';
		$items = $this->db->getAll('SELECT Title, Text, `Order` FROM points p WHERE ServiceId = ?i AND Lang = ?i ORDER BY IF(`Order`, -100/`Order`, 0)', $service['Id'], $this->lang->GetId());
		foreach ($items as $i => $item) {
			$counter = $i + 1;
			$order = str_pad($item['Order'] ?: $counter, 2, '0', STR_PAD_LEFT);
			$content .= strtr($element, [
				'<%I%>'		=> $order,
				'<%TITLE%>'	=> $item['Title'],
				'<%TEXT%>'	=> nl2br($item['Text'])
			]);
		}

		$result = LoadTemplate('bl_points');
		$result = strtr($result, array(
			'<%TITLE%>'		=> $this->blocks_info[$code][$index]['Header'],
			'<%TEXT%>'		=> $this->blocks_info[$code][$index]['Content'],
			'<%CONTENT%>'	=> $content,
			'<%DISPLAY%>'	=> $params['Subheader'] || $params['Cite'] || $params['Author'] ? '' : ' style="display: none"',
			'<%SUBHEADER%>'	=> $params['Subheader'] ? '<h4>'.$params['Subheader'].'</h4>' : '',
			'<%CITE%>'		=> $params['Cite'] ? '<p>'.$params['Cite'].'</p>' : '',
			'<%AUTHOR%>'	=> $params['Author'] ? '<span>'.$params['Author'].'</span>' : '',
		));
		return $result;
	}

	/** Блок - Портфолио услуги */
	public function BlockServicePortfolio($data, $service) {
		$code = $data['Code'];
		$index = $data['Index'];
		$params = $this->GetBlockParams($code, $index);

		$recsOnPage = $this->settings->GetSetting('ServiceProjectsRecsOnPage', 3);
		$query = 'SELECT COUNT(p.Id) '
				.'FROM cat_projects p '
				.'LEFT JOIN rel_projects_services p2s ON p2s.ProjectId = p.Id '
				.'WHERE p2s.ServiceId = ?i AND p.Lang = ?i';
		$totalCount = $this->db->getOne($query, $service['Id'], $this->lang->GetId());
		
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
			$service['Id'], 
			$this->lang->GetId(),
			0,
			$recsOnPage);
		$even = false;
		foreach ($items as $i => $item) {
			$link = '/projects/' . $item['CategoryCode'] . '/' . $item['Code'] . '/';
			$alt = htmlspecialchars($item['Title'], ENT_QUOTES);
			$image = '<a href="' . $link . '" class="projects-item-photo"><img src="' . $item['Image'] . '" alt="' . $alt . '"></a>';
			$content .= strtr($element, [
				'<%LINK%>'		=> $link,
				'<%TITLE%>'		=> $item['Title'],
				'<%ALT%>'		=> $alt,
				'<%BEFORE%>'	=> $even ? '' : $image,
				'<%AFTER%>'		=> !$even ? '' : $image,
			]);
			$even = !$even;
		}

		$more = $recsOnPage < $totalCount ? GetMore([
				'function'	=> 'serviceProjectsMore();'
			]) : '';

		$result = LoadTemplate('bl_service_portfolio');
		$result = $content ? strtr($result, array(
			'<%TITLE%>'		=> $this->blocks_info[$code][$index]['Header'],
			'<%TEXT%>'		=> $this->blocks_info[$code][$index]['Content'],
			'<%CONTENT%>'	=> $content,
			'<%SERVICECODE%>' => $service['Code'],
			'<%PAGEINDEX%>'	=> 1,
			'<%MORE%>'		=> $more,
			'<%HR%>'		=> $params['Hr'] ? '<hr class="hr">' : '',
		)) : '';
		return $result;
	}

	/** Блок - Форма захвата */
	public function BlockTarget($data) {
		global $Params;

		$code = 'target';
		$index = $data['Index'];
		$params = $this->GetBlockParams($code, $index);

		$result = LoadTemplate('bl_target');
		$result = strtr($result, array(
			'<%TITLE%>'		=> $this->blocks_info[$code][$index]['Header'],
			'<%ALT%>'		=> htmlspecialchars(strip_tags($this->blocks_info[$code][$index]['Content']), ENT_QUOTES),
			'<%TEXT%>'		=> strip_tags($this->blocks_info[$code][$index]['Content']),
			'<%ACTION%>'	=> '/ajax--act-Feedback/',
			'<%BUTTON%>'	=> $params['Button'],
			'<%INDEX%>'		=> isset($params['Type']) ? $params['Type'] : 1,
			'<%CLASS%>'		=> isset($params['Type']) ? 'type' . $params['Type'] : '',
			'<%CODE%>'		=> 'target',
			'<%REFERER%>'	=> $Params['Site']['Page']['Code'],
			'<%SUCCESSHEADER%>'	=> $params['SuccessHeader'] ?: $this->forms_info[$code]['SuccessHeader'],
			'<%SUCCESS%>'	=> strip_tags($params['Success'] ?: $this->forms_info[$code]['Success']),
			'<%HR%>'		=> $params['Hr'] ? '<hr class="hr">' : '',
		));
		return $result;
	}

	/** Блок - Этапы разработки */
	public function BlockServiceStages($data, $service) {
		$code = $data['Code'];
		$index = $data['Index'];
		$params = $this->GetBlockParams($code, $index);
		
		$element = LoadTemplate('bl_service_stages_el');
		$content = '';
		$items = $this->db->getAll('SELECT Title, Text, `Order` FROM stages p WHERE ServiceId = ?i AND Lang = ?i ORDER BY IF(`Order`, -100/`Order`, 0)', $service['Id'], $this->lang->GetId());
		foreach ($items as $i => $item) {
			$counter = $i + 1;
			$order = str_pad($item['Order'] ?: $counter, 2, '0', STR_PAD_LEFT);
			$content .= strtr($element, [
				'<%I%>'		=> $order,
				'<%TITLE%>'	=> $item['Title'],
				'<%TEXT%>'	=> nl2br($item['Text'])
			]);
		}

		$result = LoadTemplate('bl_service_stages');
		$result = strtr($result, array(
			'<%TITLE%>'		=> $this->blocks_info[$code][$index]['Header'],
			'<%TEXT%>'		=> $this->blocks_info[$code][$index]['Content'],
			'<%CONTENT%>'	=> $content,
		));
		return $result;
	}

	/** Блок - Получить скидку */
	public function BlockDiscount($data) {
		$code = $data['Code'];
		$index = $data['Index'];
		$params = $this->GetBlockParams($code, $index);

		$result = LoadTemplate('bl_discount');
		$result = strtr($result, array(
			'<%TITLE%>'		=> $this->blocks_info[$code][$index]['Header'],
			'<%TEXT%>'		=> $this->blocks_info[$code][$index]['Content'],
			'<%BUTTON%>'	=> $params['Button'],
		));
		return $result;
	}

	public function BlockApproach($data, $service) {
		$code = $data['Code'];
		$index = $data['Index'];
		$params = $this->GetBlockParams($code, $index);
		
		$element = LoadTemplate('bl_approach_el');
		$content = '';
		$items = $this->db->getAll('SELECT Title, Text, Image FROM approaches WHERE ServiceId = ?i AND Lang = ?i ORDER BY IF(`Order`, -100/`Order`, 0)', $service['Id'], $this->lang->GetId());
		$even = false;
		foreach ($items as $item) {
			$alt = htmlspecialchars($item['Title'], ENT_QUOTES);
			$image = '<img src="' . $item['Image'] . '" alt="' . $alt . '">';
			$content .= strtr($element, [
				'<%TITLE%>'		=> $item['Title'],
				'<%TEXT%>'		=> nl2br($item['Text']),
				'<%BEFORE%>'	=> !$even ? $image : '',
				'<%AFTER%>'		=> $even ? $image : '',
			]);

			$even = !$even;
		}

		$result = LoadTemplate('bl_approach');
		$result = strtr($result, array(
			'<%TITLE%>'		=> $this->blocks_info[$code][$index]['Header'],
			'<%TEXT%>'		=> $this->blocks_info[$code][$index]['Content'],
			'<%CONTENT%>'	=> $content,
		));
		return $result;
	}

	public function BlockApproach2($data, $service) {
		$code = $data['Code'];
		$index = $data['Index'];
		$params = $this->GetBlockParams($code, $index);
		
		$element = LoadTemplate('bl_approach2_el');
		$content = '';
		$items = $this->db->getAll('SELECT Title, Text, Image FROM approaches WHERE ServiceId = ?i AND Lang = ?i ORDER BY IF(`Order`, -100/`Order`, 0)', $service['Id'], $this->lang->GetId());
		foreach ($items as $item) {
			$content .= strtr($element, [
				'<%TITLE%>'		=> $item['Title'],
				'<%IMAGE%>'		=> $item['Image'],
				'<%ALT%>'		=> htmlspecialchars($item['Title'], ENT_QUOTES),
				'<%TEXT%>'		=> nl2br($item['Text']),
			]);
		}

		$result = LoadTemplate('bl_approach2');
		$result = strtr($result, array(
			'<%TITLE%>'		=> $this->blocks_info[$code][$index]['Header'],
			'<%TEXT%>'		=> $this->blocks_info[$code][$index]['Content'],
			'<%CONTENT%>'	=> $content,
		));
		return $result;
	}
}
?>