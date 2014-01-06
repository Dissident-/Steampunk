<?php
return array(
    'default' => array(
        'model' => 'Account',
		
		'hash' => 'sha1',
		
        //Login providers
        'login' => array(
            'password' => array(
                'login_field' => 'Username',
                'password_field' => 'Password'
            )
        ),
 
        //Role driver configuration
        'roles' => array(
            'driver' => 'relation',
            'type' => 'has_many',
 
            //Field in the roles table
            //that holds the models name
            'name_field' => 'RoleName',
            'relation' => 'Role'
        )
    )
);