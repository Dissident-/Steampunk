<?php
namespace App\Model;
class ItemType extends \PHPixie\ORM\Model{
 
    //Specify the PRIMARY KEY
    public $id_field='ItemTypeID';
 
    //Specify table name
    public $table='item_type';
 
    //Specify which connection to use
    public $connection = 'default';
	
	
	protected $has_many=array(
        'ItemInstance'=>array(
            'model'=>'ItemInstance',
            'key'=>'ItemTypeID'
        ),
		'TileType'=>array(
            'model'=>'TileType',
			'through'=>'search_odds',
            'key'=>'ItemTypeID',
			'foreign_key'=>'TileTypeID'
        )
    );
	
	protected $belongs_to=array(
 
        //Set the name of the relation, this defines the
        //name of the property that you can access this relation with
        'Category'=>array(
 
            //name of the model to link
            'model'=>'ItemCategory',
 
            'key'=>'ItemCategoryID'
        )
    );
	
}