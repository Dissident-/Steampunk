<?php
namespace App\Model;
class ActivityLog extends \PHPixie\ORM\Model{
 
    //Specify the PRIMARY KEY
    public $id_field='ActivityLogID';
 
    //Specify table name
    public $table='activity_log';
 
    //Specify which connection to use
    public $connection = 'default';
	
	protected $has_many=array(
        'Character'=>array(
            'model'=>'Character',
			'through'=>'activity_log_reader',
            'key'=>'ActivityLogID',
			'foreign_key'=>'CharacterID'
        )
    );
	
}