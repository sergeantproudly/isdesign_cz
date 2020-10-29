<?php

	class PreActions{

		private $db;
		
		public function __construct(){
			$this->DoActions();
		}
		
		private function DoActions(){
			global $Params;
			global $Config;
			global $Settings;
			global $Routing;
			global $Site;
			global $Lang;
			global $_LEVEL;
			
			$Params['Db']['Link'] = new SafeMySQL(array(
		   		'host'		=> $Config['Db']['Host'],
		   		'user'		=> $Config['Db']['Login'],
		   		'pass'		=> $Config['Db']['Pswd'],
		   		'db'		=> $Config['Db']['DbName'],
		   		'charset'	=> 'utf8'
		 	));
		 	$this->db = $Params['Db']['Link'];

		 	krnLoadLib('lang');
		 	$domain = $_SERVER['HTTP_HOST'];
		 	$Lang = new Lang($domain);

			krnLoadLib('settings');
		 	$Settings = new Settings();

		 	krnLoadLib('routing');
		 	$Routing = new Routing();

		 	$Site = new Site();

		 	$_LEVEL[1] = $_GET['p_code'];
		 	$_LEVEL[2] = $_GET['p_code2'];
		 	$_LEVEL[3] = $_GET['p_code3'];
		 	$_LEVEL[4] = $_GET['p_code4'];
		} 
		
	}
	
	$PreActions = new PreActions();

?>