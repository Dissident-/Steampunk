<?php
namespace App\Model;
class Account extends \PHPixie\ORM\Model{
 
    //Specify the PRIMARY KEY
    public $id_field='AccountID';
 
    //Specify table name
    public $table='account';
 
    //Specify which connection to use
    public $connection = 'default';
	
	protected $has_many=array(
        'Character'=>array(
            'model'=>'Character',
            'key'=>'AccountID'
        ),
		'Role'=>array(
            'model'=>'Role',
			'through'=>'account_role',
            'key'=>'AccountID',
			'foreign_key'=>'RoleID'
        )
    );

}