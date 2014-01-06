<?php
namespace App\Model;
class ItemCategory extends \PHPixie\ORM\Model{
 
    //Specify the PRIMARY KEY
    public $id_field='ItemCategoryID';
 
    //Specify table name
    public $table='item_category';
 
    //Specify which connection to use
    public $connection = 'default';
	
	protected $has_many=array(
        'ItemType'=>array(
            'model'=>'ItemType',
            'key'=>'ItemCategoryID'
        )
    );
}