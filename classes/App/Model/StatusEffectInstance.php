<?php
namespace App\Model;
class StatusEffectInstance extends \PHPixie\ORM\Model{
 
    //Specify the PRIMARY KEY
    public $id_field='TileTypeID';
 
    //Specify table name
    public $table='status_effect_instance';
 
    //Specify which connection to use
    public $connection = 'default';
	
	
	protected $belongs_to=array(
 
        //Set the name of the relation, this defines the
        //name of the property that you can access this relation with
        'Character'=>array(
 
            //name of the model to link
            'model'=>'Character',
 
            'key'=>'CharacterID'
        ),
		'Origin'=>array(
 
            //name of the model to link
            'model'=>'Character',
 
            'key'=>'OriginatingCharacterID'
        ),
		'Type'=>array(
 
            //name of the model to link
            'model'=>'StatusEffect',
 
            'key'=>'StatusEffectTypeID'
        )
    );
	
	
	
}