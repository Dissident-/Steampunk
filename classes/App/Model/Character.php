<?php
namespace App\Model;
class Character extends \PHPixie\ORM\Model{
 
    //Specify the PRIMARY KEY
    public $id_field='CharacterID';
 
    //Specify table name
    public $table='character';
 
    //Specify which connection to use
    public $connection = 'default';
	
	protected $belongs_to=array(
 
        //Set the name of the relation, this defines the
        //name of the property that you can access this relation with
        'Account'=>array(
 
            //name of the model to link
            'model'=>'Account',
 
            'key'=>'AccountID'
        ),
		'Location'=>array(
 
            //name of the model to link
            'model'=>'Location',
 
            'key'=>'LocationID'
        )
    );
	
	protected $has_many=array(
        'ActivityLog'=>array(
            'model'=>'ActivityLog',
			'through'=>'activity_log_reader',
            'key'=>'CharacterID',
			'foreign_key'=>'ActivityLogID'
        ),
		'Skill'=>array(
            'model'=>'Skill',
			'through'=>'skill_instance',
            'key'=>'CharacterID',
			'foreign_key'=>'SkillID'
        )
    );
	
}