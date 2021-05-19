<?php

krnLoadLib('settings');
krnLoadLib('define');

define('QUIZ_STEP_RADIO_TYPE_ID', 2);
define('QUIZ_STEP_CHECKBOX_TYPE_ID', 1);
define('QUIZ_STEP_RADIO_IMAGE_TYPE_ID', 4);
define('QUIZ_STEP_CHECKBOX_IMAGE_TYPE_ID', 3);
define('QUIZ_STEP_INPUT_TYPE_ID', 5);
define('QUIZ_STEP_TEXTAREA_TYPE_ID', 6);

class Quiz {

	protected $db;
	protected $settings;
	protected $lang;

	protected $quizTemplate = 'quiz';
	protected $quizStateTemplate = 'quiz_state';
	protected $quizStateVariantRadioTemplate = 'quiz_state_variant_radio';
	protected $quizStateVariantCheckboxTemplate = 'quiz_state_variant_checkbox';
	protected $quizStateVariantRadioImageTemplate = 'quiz_state_variant_radio_image';
	protected $quizStateVariantCheckboxImageTemplate = 'quiz_state_variant_checkbox_image';
	protected $quizStateVariantInputTemplate = 'quiz_state_variant_input';
	protected $quizStateVariantTextareaTemplate = 'quiz_state_variant_textarea';
	protected $quizStateFinalTemplate = 'quiz_state_final';

	protected $serviceId;
	protected $quiz = [];
	
	public function __construct($params = []) {
		global $Params;
		global $Settings;
		global $Lang;
		$this->db = $Params['Db']['Link'];
		$this->settings = $Settings;
		$this->lang = $Lang;

		if ($params['Service'])	$this->serviceId = $params['Service']['Id'];
		elseif ($params['ServiceId']) $this->serviceId = $params['ServiceId'];
	}

	protected function LoadAllFromDb() {
		$query = 'SELECT v.Id AS AnswerId, v.Title AS AnswerVariant, v.Image278_278 AS AnswerImage, '
				.'s.Title AS Question, s.TypeId AS QuestionTypeId, s.Button AS StateButton, s.VariantId AS ParentAnswerVariantId, s.AnswerRequired, s.`Order` AS QuestionNumber, '
				.'q.Header AS QuizHeader, q.Text AS QuizText, q.Button AS StartButton, q.FinalButton, q.Image1670_1122 AS BgImage, q.ImageMob640_1170 AS BgImageMob '
				.'FROM quiz_variants v '
				.'LEFT JOIN quiz_steps s ON v.StepId = s.Id '
				.'LEFT JOIN quiz_templates q ON s.TemplateId = q.Id '
				.'WHERE q.ServiceId = ?i AND v.Lang = ?i '
				.'ORDER BY s.`Order`, v.`Order`';
		$items = $this->db->getAll($query,
				$this->serviceId,
				$this->lang->GetId()
			);
		foreach ($items as $counter => $item) {
			if (empty($this->quiz)) {
				$this->quiz = [
					'Header' => $item['QuizHeader'],
					'Text' => $item['QuizText'],
					'StartButton' => $item['StartButton'],
					'FinalButton' => $item['FinalButton'],
					'Bg' => $item['BgImage'],
					'BgMob' => $item['BgImageMob'] ? $item['BgImageMob'] : $item['BgImage'],
					'States' => []
				];
			}
			if (!isset($this->quiz['States'][$item['QuestionNumber']])) {
				$this->quiz['States'][$item['QuestionNumber']] = [
					'Question' => $item['Question'],
					'TypeId' => $item['QuestionTypeId'],
					'Button' => $item['StateButton'],
					'ParentVariantId' => $item['ParentAnswerVariantId'],
					'AnswerRequired' => $item['AnswerRequired'],
					'QuestionNumber' => $item['QuestionNumber'],
					'Variants' => []
				];
			}
			$this->quiz['States'][$item['QuestionNumber']]['Variants'][$item['AnswerId']] = [
				'Answer' => $item['AnswerVariant'],
				'Image' => $item['AnswerImage']
			];
		}
	}

	public function GetData() {
		if (empty($this->quiz)) $this->LoadAllFromDb();
		return $this->quiz;
	}

	protected function GetTemplateByStepTypeId($typeId) {
		if ($typeId == QUIZ_STEP_RADIO_TYPE_ID) {
			return $this->quizStateVariantRadioTemplate;
		} elseif ($typeId == QUIZ_STEP_CHECKBOX_TYPE_ID) {
			return $this->quizStateVariantCheckboxTemplate;
		} elseif ($typeId == QUIZ_STEP_RADIO_IMAGE_TYPE_ID) {
			return $this->quizStateVariantRadioImageTemplate;
		} elseif ($typeId == QUIZ_STEP_CHECKBOX_IMAGE_TYPE_ID) {
			return $this->quizStateVariantCheckboxImageTemplate;
		} elseif ($typeId == QUIZ_STEP_INPUT_TYPE_ID) {
			return $this->quizStateVariantInputTemplate;
		} elseif ($typeId == QUIZ_STEP_TEXTAREA_TYPE_ID) {
			return $this->quizStateVariantTextareaTemplate;
		}
		return false;
	}

	public function GenerateHtml() {
		global $Params;
		$form = $this->db->getRow('SELECT * FROM `forms` WHERE Code = ?s AND Lang = ?i', 'modal-quiz', $this->lang->GetId());

		$stateTpl = LoadTemplate($this->quizStateTemplate);
		$finalTpl = LoadTemplate($this->quizStateFinalTemplate);

		if (empty($this->quiz)) $this->LoadAllFromDb();

		$states = '';
		$total = count($this->quiz['States']);
		$i = 0;
		foreach ($this->quiz['States'] as $counter => $state) {
			$i++;
			$variants = '';
			foreach ($state['Variants'] as $variantId => $variant) {
				$variants .= strtr(LoadTemplate($this->GetTemplateByStepTypeId($state['TypeId'])), [
					'<%ID%>'		=> $variantId,
					'<%NUMBER%>'	=> $state['QuestionNumber'],
					'<%TITLE%>'		=> $variant['Answer'],
					'<%IMAGE%>'		=> $variant['Image'],
					'<%IMAGEWEBP%>'	=> $variant['Image'] ? flGetWebpByImage($variant['Image']) : '',
					'<%ALT%>'		=> htmlspecialchars($variant['Answer'], ENT_QUOTES)
				]);
			}

			if ($i < $total) {
				$after = '<button class="btn">' . $state['Button'] . '<span><img src="assets/images/arrow-right-white.svg" loading="lazy" alt=""></span></button>';
			} else {
				$after = $this->lang->ProcessTemplate(strtr($finalTpl, array(
					'<%SERVICEID%>'	=> $this->serviceId,
					'<%REFERER%>'	=> $Params['Site']['Page']['Code'],
					'<%CODE%>'		=> $form['Code'],
					'<%BUTTON%>'	=> $this->quiz['FinalButton']
				)));
			}

			$class = [];
			if ($state['TypeId'] == QUIZ_STEP_CHECKBOX_TYPE_ID || $state['TypeId'] == QUIZ_STEP_CHECKBOX_IMAGE_TYPE_ID) $class[] = 'cbs';
			if ($state['TypeId'] == QUIZ_STEP_RADIO_IMAGE_TYPE_ID || $state['TypeId'] == QUIZ_STEP_CHECKBOX_IMAGE_TYPE_ID) {
				$class[] = 'images';
				$class[] = 'row-' . count($state['Variants']);
			}

			$states .= strtr($stateTpl, [
				'<%COUNTER%>'	=> $counter,
				'<%REQUIRED%>'	=> $state['AnswerRequired'],
				'<%PARENT%>'	=> $state['ParentVariantId'] ? ' data-parent-variant-id="' . $state['ParentVariantId'] . '"' : '',
				'<%TOTAL%>'		=> $total,
				'<%QUESTION%>'	=> $state['Question'],
				'<%CLASS%>'		=> !empty($class) ? ' ' . implode(' ', $class) : '',
				'<%VARIANTS%>'	=> $variants,
				'<%AFTER%>'		=> $after,
			]);
		}

		$result = strtr(LoadTemplate($this->quizTemplate), [
			'<%HEADER%>'	=> $this->quiz['Header'],
			'<%TEXT%>'		=> $this->quiz['Text'],
			'<%BUTTON%>'	=> $this->quiz['StartButton'],
			'<%BG%>'		=> $this->quiz['Bg'] ? 'background-image: url(' . flGetWebpByImage($this->quiz['Bg']) . ');' : '',
			'<%BGNOWEBP%>'	=> $this->quiz['Bg'],
			'<%BGMOB%>'		=> flGetWebpByImage($this->quiz['BgMob']),
			'<%BGMOBNOWEBP%>' => $this->quiz['BgMob'],
			'<%STATES%>'	=> $states
		]);
		return $result;
	}

	public function GetResultsTextByData($data = []) {
		if (empty($this->quiz)) $this->LoadAllFromDb();

		$text = '';
		foreach ($this->quiz['States'] as $counter => $state) {
			$text .= $state['QuestionNumber'] . '. ' . $state['Question'] . "\r\n";
			if ($state['TypeId'] == QUIZ_STEP_RADIO_TYPE_ID || $state['TypeId'] == QUIZ_STEP_RADIO_IMAGE_TYPE_ID) {
				$text .= ($data[$state['QuestionNumber']] ? $state['Variants'][$data[$state['QuestionNumber']]]['Answer'] : 'Ответ не выбран') . "\r\n\r\n";

			} elseif ($state['TypeId'] == QUIZ_STEP_CHECKBOX_TYPE_ID || $state['TypeId'] == QUIZ_STEP_CHECKBOX_IMAGE_TYPE_ID) {
				if (isset($data[$state['QuestionNumber']])) {
					foreach ($data[$state['QuestionNumber']] as $variantId => $v) {
						$text .= $state['Variants'][$variantId]['Answer'] . "\r\n";
					}
					$text .= "\r\n";
				} else {
					$text .= 'Ответ не выбран' . "\r\n\r\n";
				}

			} elseif ($state['TypeId'] == QUIZ_STEP_INPUT_TYPE_ID || $state['TypeId'] == QUIZ_STEP_TEXTAREA_TYPE_ID) {
				$text .= ($data[$state['QuestionNumber']] ? $data[$state['QuestionNumber']] : 'Ответ не выбран') . "\r\n\r\n";
			}
		}

		return $text;
	}
}

?>