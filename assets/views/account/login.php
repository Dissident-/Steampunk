<?php

	$_validate($errors);

	$_form('/auth/login', 'loginform', 'body');

	$_inputline('Username', isset($this->post['Username']) ? $this->post['Username'] : '');
	$_inputline('Password', isset($this->post['Password']) ? $this->post['Password'] : '', null, 'password');
	
	$_submit('Login');
	
	$_form();
	
?>