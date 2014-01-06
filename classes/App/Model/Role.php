<?php
namespace App\Model;
class Role extends \PHPixie\ORM\Model{
 
    //Specify the PRIMARY KEY
    public $id_field='RoleID';
 
    //Specify table name
    public $table='role';
 
    //Specify which connection to use
    public $connection = 'default';
	
	protected $has_many=array(
        'Account'=>array(
            'model'=>'Account',
			'through'=>'account_role',
            'key'=>'RoleID',
			'foreign_key'=>'AccountID'
        )
    );
}