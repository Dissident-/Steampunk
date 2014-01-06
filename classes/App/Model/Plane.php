<?php
namespace App\Model;
class Plane extends \PHPixie\ORM\Model{
 
    //Specify the PRIMARY KEY
    public $id_field='PlaneID';
 
    //Specify table name
    public $table='plane';
 
    //Specify which connection to use
    public $connection = 'default';
	
	protected $has_many=array(
        'Location'=>array(
            'model'=>'Location',
            'key'=>'PlaneID'
        )
    );

	
}