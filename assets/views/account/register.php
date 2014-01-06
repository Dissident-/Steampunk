<?php

	$_validate($errors);

	$_form('/auth/register', 'registrationform', 'body');

	$_inputline('Username', isset($this->post['Username']) ? $this->post['Username'] : '');
	$_inputline('Password', isset($this->post['Password']) ? $this->post['Password'] : '', null, 'password');
	$_inputline('EmailAddress', isset($this->post['EmailAddress']) ? $this->post['EmailAddress'] : '', 'Email: ');
	
	$_submit();
	
	$_form();
	
?>