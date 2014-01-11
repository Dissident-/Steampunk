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
	
	protected $deltas = array();
	
	public function SpendAP($amount = 1)
	{
		if($this->ActionPoints > 0)
		{
			$this->ActionPoints = $this->ActionPoints - $amount;
			if(isset($deltas['ActionPoints'])) $deltas['ActionPoints'] = $deltas['ActionPoints'] - $amount; else $deltas['ActionPoints'] = 0 - $amount;
			return true;
		}
		else
		{
			return false;
		}
	}
	
	public function AlterXP($amount)
	{
		$this->Experience = $this->Experience + $amount;
		if(isset($this->deltas['Experience'])) $this->deltas['Experience'] = $this->deltas['Experience'] + $amount; else $this->deltas['Experience'] = $amount;
		return true;

	}
	
	public function AlterHP($amount)
	{
		$this->HitPoints = $this->HitPoints + $amount;
		if(isset($this->deltas['HitPoints'])) $this->deltas['HitPoints'] = $this->deltas['HitPoints'] + $amount; else $this->deltas['HitPoints'] = $amount;
		if($this->HitPoints <= 0) $this->Kill();
		return true;

	}
	
	public function Kill()
	{
		$action = $this->pixie->orm->get('ActivityLog');
		$action->CharacterID = $this->CharacterID;
		if($this->HitPoints > 0)
		{
			$this->HitPoints = 0;
			$action->Activity = '<span class="log-died">You have been exterminated!</span>'; // Different message for this?
		}
		else
		{
			$action->Activity = '<span class="log-died">You have died :(</span>';
		}
		$this->LocationID = null;
		$this->save();
		$action->save();
		$this->add('ActivityLog', $action);
	}
	
	public function deltas()
	{
		$updateparts = array();
		foreach($this->deltas as $k => $v)
		{
			if($v != 0) $updateparts[$k] = $this->pixie->db->expr('`'.$k.'` '.($v > 0 ? ' + '.$v : ' - '.abs($v)));
		}
		if(count($updateparts) > 0) 
		{
			$this->pixie->db->query('update')->table('character')->data($updateparts)->where('CharacterID',$this->CharacterID)->execute();
		}
		$this->deltas = array();
	}
	
	public function save()
	{
		$this->deltas = array();
		parent::save();
	}
	
	public function Respawn()
	{
		$this->LocationID = 1;
		$this->HitPoints = 50;
		$this->save();
		$action = $this->pixie->orm->get('ActivityLog');
		$action->CharacterID = $this->CharacterID;
		$action->Activity = '<span class="log-respawn">You have respawned.</span>';
		$action->save();
		$this->add('ActivityLog', $action);
	}
	
	public function get($property)
	{
		if($property == 'Link')
		{
			return '<a href="/character/'.$this->CharacterID.'">'.$this->CharName.'</a>';
		}
		else if($property == 'Weaponry')
		{
			$weaponrydata = $this->pixie->db->query('select')->table('item_instance')->fields('item_instance.*', 'item_type.*', 'item_usage_attribute.AttributeName', 'item_usage_attribute.AttributeValue')->join('item_type', array('item_instance.ItemTypeID', 'item_type.ItemTypeID'))->join('item_type_usage', array('item_type.ItemTypeID', 'item_type_usage.ItemTypeID'))->join('item_usage', array('item_type_usage.ItemUsageID', 'item_usage.ItemUsageID'))->join('item_usage_attribute',array(array('item_instance.ItemInstanceID','item_usage_attribute.ItemInstanceID'), array('or', array('item_type.ItemTypeID', 'item_usage_attribute.ItemTypeID'))),'left')->where('item_instance.CharacterID', $this->CharacterID)->where('item_usage.ItemUsageName', 'weapon')->execute()->as_array();
			$weaponry = array();
			
			$weapontypes = array();
			$weaponinstances = array();
			foreach($weaponrydata as $weaponattribute)
			{
				if(!isset($weaponry[$weaponattribute->ItemInstanceID]))
				{
					$weaponattribute->Article = 'their';
					$weaponry[$weaponattribute->ItemInstanceID] = $weaponattribute;
					if($weaponattribute->AttributeName != null)
					{
						$weaponry[$weaponattribute->ItemInstanceID]->Attributes = array($weaponattribute);
						$a = $weaponattribute->AttributeName;
						$weaponry[$weaponattribute->ItemInstanceID]->$a =  $weaponattribute->AttributeValue;
					}
					else
					{
						$weaponry[$weaponattribute->ItemInstanceID]->Attributes = array();
					}
				}
				else
				{
					$weaponry[$weaponattribute->ItemInstanceID]->Attributes[] = $weaponattribute;
					$a = $weaponattribute->AttributeName;
					if(isset($weaponry[$weaponattribute->ItemInstanceID]->$a))
					{
						if(is_numeric($weaponry[$weaponattribute->ItemInstanceID]->$a) && is_numeric($weaponattribute->AttributeValue))
						{
					
							$weaponry[$weaponattribute->ItemInstanceID]->$a = $weaponry[$weaponattribute->ItemInstanceID]->$a + $weaponattribute->AttributeValue;
						}
						else
						{
							// shit, how do we handle non-numeric duplicate attributes?
							// lets go for the last one, so that things can override one another
							$weaponry[$weaponattribute->ItemInstanceID]->$a =  $weaponattribute->AttributeValue;
						}
					}
					else
					{
						$weaponry[$weaponattribute->ItemInstanceID]->$a =  $weaponattribute->AttributeValue;
					}
				}
			}
			$working_weaponry = array();
			foreach($weaponry as $k => $v)
			{
				if(isset($v->Damage) && isset($v->DamageType) && isset($v->HitChance))
					$working_weaponry[$k] = $v;
			}
			return $working_weaponry;
		}
	}
}