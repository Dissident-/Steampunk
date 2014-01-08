<?php
namespace App\Model;
class ItemUsage extends \PHPixie\ORM\Model{
 
    //Specify the PRIMARY KEY
    public $id_field='ItemUsageID';
 
    //Specify table name
    public $table='item_usage';
 
    //Specify which connection to use
    public $connection = 'default';
	
	protected $has_many=array(
 		'ItemType'=>array(
            'model'=>'ItemType',
			'through'=>'item_type_usage',
            'key'=>'ItemUsageID',
			'foreign_key'=>'ItemTypeID'
        )
    );
}