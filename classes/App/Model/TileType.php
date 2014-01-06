<?php
namespace App\Model;
class TileType extends \PHPixie\ORM\Model{
 
    //Specify the PRIMARY KEY
    public $id_field='TileTypeID';
 
    //Specify table name
    public $table='tile_type';
 
    //Specify which connection to use
    public $connection = 'default';
	
	
	protected $has_many=array(
        'Location'=>array(
            'model'=>'Location',
            'key'=>'TileTypeID'
        ),
		'SearchOdds'=>array(
            'model'=>'ItemType',
			'through'=>'search_odds',
            'key'=>'TileTypeID',
			'foreign_key'=>'ItemTypeID'
        )
    );
	
}