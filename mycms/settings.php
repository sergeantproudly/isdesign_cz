<?php

   $Config['Db']['Host']   = 'localhost';
   $Config['Db']['Login']  = 'isdesign';
   $Config['Db']['Pswd']   = 'isdesign';
   $Config['Db']['DbName'] = 'isdesign_cz_db';
   	
   $Config['Site']['Title']      = 'ISDesign group - архитектурно-строительная компания';
   $Config['Site']['Email']      = '';
   $Config['Site']['Keywords']      = ''; 
   $Config['Site']['Description']   = '';
   $Config['Site']['Url']        = 'https://isdesign.cz';
      
   $Config['Smtp']['Server']  = 'smtp.savana.cz';
   $Config['Smtp']['Port']    = '465';
   $Config['Smtp']['Email']   = 'info@isdesigngroup.ru';
   $Config['Smtp']['Password']   = 'Vsa26121989';
   $Config['Smtp']['Secure']  = 'ssl';
   	
   	error_reporting (E_ALL & ~E_NOTICE);

	// constants
   define ('TEMPLATES_DIR', 'templates/');
   define ('TOOLS_DIR', 'tools/');
   define ('IMAGES_DIR', 'images/');
   define ('MODULES_DIR', 'modules/');
   define ('LIBRARY_DIR', 'library/');
   define ('LIBRARY_SITE_DIR', '../library/');
   define ('UPLOADS_DIR', '../uploads/');
   define ('TEMP_DIR', 'uploads/temp/');
   	
   define ('ABS_PATH', $_SERVER['DOCUMENT_ROOT'].'/mycms/');
   define ('ROOT_PATH', $_SERVER['DOCUMENT_ROOT'].'/');
   define ('ROOT_DIR', '../');

?>