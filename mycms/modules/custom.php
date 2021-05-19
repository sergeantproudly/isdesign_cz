<?php

krnLoadLib('define');
krnLoadLib('settings');
krnLoadLib('images');
krnLoadLib('files');
krnLoadLib('common');

class custom extends krn_abstract {

	const TEMPLATE_SETTING_ID = 1;
	const STEP_EXCLUSION_SETTING_ID = 3;
	
	public function __construct(){
		parent::__construct();
	}
	
	public function GetResult(){
	}

	public function BrowseStepVariantId($rec) {
		$variant = dbGetRecordFromDb('SELECT cv.Title, cs.Title AS StepTitle FROM quiz_variants cv LEFT JOIN quiz_steps cs ON cv.StepId = cs.Id WHERE cv.Id = ' . $rec['VariantId'],__FILE__,__LINE__);
		$result = $variant ? ($variant['StepTitle'] . ' : ' . $variant['Title']) : '';
		return $result;
	}

	public function ModifyStepVariantId($rec, $name) {
		$res = dbDoQuery('SELECT cv.Id, cv.Title, cs.Title AS StepTitle FROM quiz_variants cv LEFT JOIN quiz_steps cs ON cv.StepId = cs.Id WHERE cs.TemplateId = ' . $rec['TemplateId'] . ' ORDER BY IF(cs.`Order`, -100/cs.`Order`, 0), IF(cv.`Order`, -100/cv.`Order`, 0)', __FILE__, __LINE__);
		while($rec2 = dbGetRecord($res)) {
			if ($rec['VariantId'] == $rec2['Id']) {
				$default = [
					'Title' => $rec2['StepTitle'] . ' : ' . $rec2['Title'],
					'Value' => $rec2['Id']
				];
				$options.='<span class="item current" value="'.$rec2['Id'].'">'.$rec2['StepTitle'] . ' : ' . $rec2['Title'].'</span>';
			} else {
				$options.='<span class="item" value="'.$rec2['Id'].'">'.$rec2['StepTitle'] . ' : ' . $rec2['Title'].'</span>';
			}
		}
		if (!$default) {
			$default = [
				'Title' => '',
				'Value' => 0
			];
			$options = '<span class="item current" value="0">&nbsp;</span>' . $options;
		} else {
			$options = '<span class="item" value="0">&nbsp;</span>' . $options;
		}

		$result = strtr(LoadTemplate('inp_select'), array(
			'<%IDNUM%>'			=> '',
			'<%CALLBACK%>'		=> '',
			'<%NAME%>'			=> $name,
			'<%DEFAULT_TITLE%>'	=> $default['Title'],
			'<%DEFAULT_VALUE%>'	=> $default['Value'],
			'<%OPTIONS%>'		=> $options,
			'<%ATTRIBUTES%>'	=> ''
		));
		return $result;
	}
	
}

?>