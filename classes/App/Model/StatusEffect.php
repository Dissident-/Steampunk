<?php
namespace App\Model;
class StatusEffect extends \PHPixie\ORM\Model{
 
    //Specify the PRIMARY KEY
    public $id_field='StatusEffectTypeID';
 
    //Specify table name
    public $table='status_effect_type';
 
    //Specify which connection to use
    public $connection = 'default';

	protected $has_many=array(
        'Instance'=>array(
            'model'=>'StatusEffectInstance',
            'key'=>'StatusEffectTypeID'
        )
    );
	
}