<?php
namespace App\Model;
class Usage extends \PHPixie\ORM\Model{
 
    //Specify the PRIMARY KEY
    public $id_field='UsageID';
 
    //Specify table name
    public $table='usage';
 
    //Specify which connection to use
    public $connection = 'default';
	
	protected $has_many=array(
 		'Skill'=>array(
            'model'=>'Skill',
			'through'=>'skill_usage',
            'key'=>'ItemUsageID',
			'foreign_key'=>'ItemTypeID'
        ),
		'Item'=>array(
            'model'=>'ItemType',
			'through'=>'item_type_usage',
            'key'=>'ItemUsageID',
			'foreign_key'=>'ItemTypeID'
        )
    );
}