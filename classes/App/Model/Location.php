<?php
namespace App\Model;
class Location extends \PHPixie\ORM\Model{
 
    //Specify the PRIMARY KEY
    public $id_field='LocationID';
 
    //Specify table name
    public $table='location';
 
    //Specify which connection to use
    public $connection = 'default';
	
	protected $belongs_to=array(
 
        //Set the name of the relation, this defines the
        //name of the property that you can access this relation with
        'Plane'=>array(
 
            //name of the model to link
            'model'=>'Plane',
 
            'key'=>'PlaneID'
        ),
		     'Type'=>array(
 
            //name of the model to link
            'model'=>'TileType',
 
            'key'=>'TileTypeID'
        )
    );
	
	protected $has_many=array(
        'Character'=>array(
            'model'=>'Character',
            'key'=>'LocationID'
        )
    );
	
	
}