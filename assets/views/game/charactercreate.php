<?php

	$_validate($errors);

	$_form('/character/create', 'charcreateform');

	$_inputline('CharName', isset($this->post['CharName']) ? $this->post['CharName'] : '', 'Character Name: ');
	
	$_submit('Create');
	
	$_form();
	
?>