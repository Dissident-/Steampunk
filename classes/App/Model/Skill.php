<?php
namespace App\Model;
class Character extends \PHPixie\ORM\Model{
 
    //Specify the PRIMARY KEY
    public $id_field='SkillID';
 
    //Specify table name
    public $table='skill';
 
    //Specify which connection to use
    public $connection = 'default';
	
	protected $has_many=array(
        'Character'=>array(
            'model'=>'Character',
			'through'=>'skill_instance',
            'key'=>'SkillID',
			'foreign_key'=>'CharacterID'
        ),
		'RequiredSkill'=>array(
            'model'=>'Skill',
			'through'=>'skill_prerequisite',
            'key'=>'RequiredSkillID',
			'foreign_key'=>'SkillID'
        ),
        'ChildSkill'=>array(
            'model'=>'Skill',
			'through'=>'skill_prerequisite',
            'key'=>'SkillID',
			'foreign_key'=>'RequiredSkillID'
        )
    );
	
}