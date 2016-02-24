<?php
	require 'validate.php';
	
	$validation = new Validate($_POST, array(
		'gender'			=>	'boolean|required|default,0',
		'city' 				=>	'text|required|min_length,1|max_length,40',
		'state'				=>	'text|min_length,1|max_length,40',
		'country' 			=>	'int|min,1',
		'bdayYear,bdayMonth,bdayDay' =>	'date|required|rename,birthdate',
		'socialsecurity' 	=>	'text|min_length,10|max_length,20',
		'maritalstatus' 	=>	'int|min,0|max,6|default,0',
		'race' 				=>	'int|min,0|max,4|default,0',
		'about' 			=>	'text|min_length,1|max_length,160',
		'telnr_c' 			=>	'telnr|min_length,1|max_length,20',
		'telnr_o' 			=>	'telnr|min_length,1|max_length,20',
		'telnr_f' 			=>	'telnr|min_length,1|max_length,20'
	));

	if ($validation -> isValid()) {
		
		// Your code here
		
	}
?>