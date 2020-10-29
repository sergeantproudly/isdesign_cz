<?php

class actions extends krn_abstract{
	
	function __construct(){
		parent::__construct();
	}
	
	function GetResult(){
	}
	
	/** System */
	function SystemMultiSelect($params){
		$storageTable=$params['storageTable'];
		$storageSelfField=$params['storageSelfField'];
		$storageField=$params['storageField'];
		$selfValue=$params['selfValue'];
		dbDoQuery('DELETE FROM `'.$storageTable.'` WHERE `'.$storageSelfField.'`="'.$selfValue.'"',__FILE__,__LINE__);
		if(isset($params['values'])){
			foreach($params['values'] as $value){
				dbDoQuery('INSERT INTO `'.$storageTable.'` SET `'.$storageSelfField.'`="'.$selfValue.'", `'.$storageField.'`="'.$value.'"',__FILE__,__LINE__);
			}
		}
	}

	/** Service */
	function OnAddService($newRecord) {
		if (!$newRecord['Code']) {
			krnLoadLib('chars');
			$code = mb_strtolower(chrTranslit($newRecord['Title']));
			$code = strtr($code, array(','=>'',' '=>'_','*'=>'','!'=>'','?'=>'','@'=>'','#'=>'','$'=>'','%'=>'','^'=>'','('=>'',')'=>'','+'=>'','-'=>'_','«'=>'','»'=>'','—'=>'',':'=>'',';'=>'','ь'=>''));
			dbDoQuery('UPDATE services SET `Code`="'.$code.'", LastModTime='.time().' WHERE Id='.$newRecord['Id'],__FILE__,__LINE__);
		} else {
			$code = $newRecord['Code'];
		}

		if ($code) {
			krnLoadLib('routing');
			Routing::SetRouting($code, 'services');
		}
	}
	
	function OnEditService($newRecord,$oldRecord){
		if (!$newRecord['Code']) {
			krnLoadLib('chars');
			$code = mb_strtolower(chrTranslit($newRecord['Title']));
			$code = strtr($code, array(','=>'',' '=>'_','*'=>'','!'=>'','?'=>'','@'=>'','#'=>'','$'=>'','%'=>'','^'=>'','('=>'',')'=>'','+'=>'','-'=>'_','«'=>'','»'=>'','—'=>'',':'=>'',';'=>'','ь'=>''));
			dbDoQuery('UPDATE services SET `Code`="'.$code.'", LastModTime='.time().' WHERE Id='.$newRecord['Id'],__FILE__,__LINE__);
		} else {
			$code = $newRecord['Code'];
		}

		if ($code) {
			krnLoadLib('routing');
			Routing::SetRouting($code, 'services');
		}
	}

	function OnDeleteService($oldRecord) {
		$code = dbGetValueFromDb('SELECT Code FROM services WHERE Id='.$oldRecord['Id'],__FILE__,__LINE__);

		if ($code) {
			krnLoadLib('routing');
			Routing::DeleteRouting($code);
		}
	}

	/** Projects Categories */
	function OnAddCategory($newRecord) {
		if (!$newRecord['Code']) {
			krnLoadLib('chars');
			$code = mb_strtolower(chrTranslit($newRecord['Title']));
			$code = strtr($code, array(','=>'',' '=>'_','*'=>'','!'=>'','?'=>'','@'=>'','#'=>'','$'=>'','%'=>'','^'=>'','('=>'',')'=>'','+'=>'','-'=>'_','«'=>'','»'=>'','—'=>'',':'=>'',';'=>'','ь'=>''));
			dbDoQuery('UPDATE cat_categories SET `Code`="'.$code.'", LastModTime='.time().' WHERE Id='.$newRecord['Id'],__FILE__,__LINE__);
		} else {
			$code = $newRecord['Code'];
		}

		if ($code) {
			krnLoadLib('routing');
			Routing::SetRouting($code, 'projects');
		}
	}
	
	function OnEditCategory($newRecord,$oldRecord){
		if (!$newRecord['Code']) {
			krnLoadLib('chars');
			$code = mb_strtolower(chrTranslit($newRecord['Title']));
			$code = strtr($code, array(','=>'',' '=>'_','*'=>'','!'=>'','?'=>'','@'=>'','#'=>'','$'=>'','%'=>'','^'=>'','('=>'',')'=>'','+'=>'','-'=>'_','«'=>'','»'=>'','—'=>'',':'=>'',';'=>'','ь'=>''));
			dbDoQuery('UPDATE cat_categories SET `Code`="'.$code.'", LastModTime='.time().' WHERE Id='.$newRecord['Id'],__FILE__,__LINE__);
		} else {
			$code = $newRecord['Code'];
		}

		if ($code) {
			krnLoadLib('routing');
			Routing::SetRouting($code, 'projects');
		}
	}

	function OnDeleteCategory($oldRecord) {
		$code = dbGetValueFromDb('SELECT Code FROM cat_categories WHERE Id='.$oldRecord['Id'],__FILE__,__LINE__);

		if ($code) {
			krnLoadLib('routing');
			Routing::DeleteRouting($code);
		}
	}

	/** Projects */
	function OnAddProject($newRecord) {
		if (!$newRecord['Code']) {
			krnLoadLib('chars');
			$code = mb_strtolower(chrTranslit($newRecord['Title']));
			$code = strtr($code, array(','=>'',' '=>'_','*'=>'','!'=>'','?'=>'','@'=>'','#'=>'','$'=>'','%'=>'','^'=>'','('=>'',')'=>'','+'=>'','-'=>'_','«'=>'','»'=>'','—'=>'',':'=>'',';'=>'','ь'=>''));
			dbDoQuery('UPDATE cat_projects SET `Code`="'.$code.'", LastModTime='.time().' WHERE Id='.$newRecord['Id'],__FILE__,__LINE__);
		} else {
			$code = $newRecord['Code'];
		}

		if ($code) {
			krnLoadLib('routing');
			Routing::SetRouting($code, 'projects');
		}
	}
	
	function OnEditProject($newRecord,$oldRecord){
		if (!$newRecord['Code']) {
			krnLoadLib('chars');
			$code = mb_strtolower(chrTranslit($newRecord['Title']));
			$code = strtr($code, array(','=>'',' '=>'_','*'=>'','!'=>'','?'=>'','@'=>'','#'=>'','$'=>'','%'=>'','^'=>'','('=>'',')'=>'','+'=>'','-'=>'_','«'=>'','»'=>'','—'=>'',':'=>'',';'=>'','ь'=>''));
			dbDoQuery('UPDATE cat_projects SET `Code`="'.$code.'", LastModTime='.time().' WHERE Id='.$newRecord['Id'],__FILE__,__LINE__);
		} else {
			$code = $newRecord['Code'];
		}

		if ($code) {
			krnLoadLib('routing');
			Routing::SetRouting($code, 'projects');
		}
	}

	function OnDeleteProject($oldRecord) {
		$code = dbGetValueFromDb('SELECT Code FROM cat_projects WHERE Id='.$oldRecord['Id'],__FILE__,__LINE__);

		if ($code) {
			krnLoadLib('routing');
			Routing::DeleteRouting($code);
		}
	}
	
	/** Statue */
	function OnAddStatue($newRecord){
		if (!$newRecord['Code']) {
			krnLoadLib('chars');
			$code = mb_strtolower(chrTranslit($newRecord['Title']));
			$code = strtr($code,array(','=>'',' '=>'_','*'=>'','!'=>'','?'=>'','@'=>'','#'=>'','$'=>'','%'=>'','^'=>'','('=>'',')'=>'','+'=>'','-'=>'_','«'=>'','»'=>'','—'=>'',':'=>'',';'=>'','ь'=>''));
			dbDoQuery('UPDATE statues SET `Code`="'.$code.'", LastModTime='.time().' WHERE Id='.$newRecord['Id'],__FILE__,__LINE__);
		} else {
			$code = $newRecord['Code'];
		}

		if ($code) {
			krnLoadLib('routing');
			Routing::SetRouting($code, 'statues');
		}
	}
	
	function OnEditStatue($newRecord,$oldRecord){
		if (!$newRecord['Code']) {
			krnLoadLib('chars');
			$code = mb_strtolower(chrTranslit($newRecord['Title']));
			$code = strtr($code,array(','=>'',' '=>'_','*'=>'','!'=>'','?'=>'','@'=>'','#'=>'','$'=>'','%'=>'','^'=>'','('=>'',')'=>'','+'=>'','-'=>'_','«'=>'','»'=>'','—'=>'',':'=>'',';'=>'','ь'=>''));
			dbDoQuery('UPDATE statues SET `Code`="'.$code.'", LastModTime='.time().' WHERE Id='.$newRecord['Id'],__FILE__,__LINE__);
		} else {
			$code = $newRecord['Code'];
		}

		if ($code) {
			krnLoadLib('routing');
			Routing::SetRouting($code, 'statues');
		}
	}

	function OnDeleteStatue($oldRecord) {
		$code = dbGetValueFromDb('SELECT Code FROM statues WHERE Id='.$oldRecord['Id'],__FILE__,__LINE__);

		if ($code) {
			krnLoadLib('routing');
			Routing::DeleteRouting($code);
		}
	}
	
	/** Static pages */
	function OnAddStaticPage($newRecord){
		if(!$newRecord['Code']){
			krnLoadLib('chars');
			$code=mb_strtolower(chrTranslit($newRecord['Title']));
			$code=strtr($code,array(','=>'',' '=>'_','*'=>'','!'=>'','?'=>'','@'=>'','#'=>'','$'=>'','%'=>'','^'=>'','('=>'',')'=>'','+'=>'','-'=>'_','«'=>'','»'=>'','—'=>'',':'=>'',';'=>'','ь'=>''));
			dbDoQuery('UPDATE static_pages SET `Code`="'.$code.'", LastModTime='.time().' WHERE Id='.$newRecord['Id'],__FILE__,__LINE__);
		}else{
			dbDoQuery('UPDATE static_pages SET LastModTime='.time().' WHERE Id='.$newRecord['Id'],__FILE__,__LINE__);
		}		
	}
	
	function OnEditStaticPage($newRecord,$oldRecord){
		if(!$newRecord['Code']){
			krnLoadLib('chars');
			$code=mb_strtolower(chrTranslit($newRecord['Title']));
			$code=strtr($code,array(','=>'',' '=>'_','*'=>'','!'=>'','?'=>'','@'=>'','#'=>'','$'=>'','%'=>'','^'=>'','('=>'',')'=>'','+'=>'','-'=>'_','«'=>'','»'=>'','—'=>'',':'=>'',';'=>'','ь'=>''));
			dbDoQuery('UPDATE static_pages SET `Code`="'.$code.'", LastModTime='.time().' WHERE Id='.$newRecord['Id'],__FILE__,__LINE__);
		}else{
			dbDoQuery('UPDATE static_pages SET LastModTime='.time().' WHERE Id='.$newRecord['Id'],__FILE__,__LINE__);
		}	
	}
}

?>