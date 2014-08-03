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
			if(isset($this->deltas['ActionPoints'])) $this->deltas['ActionPoints'] = $this->deltas['ActionPoints'] - $amount; else $this->deltas['ActionPoints'] = 0 - $amount;
			return true;
		}
		else
		{
			return false;
		}
	}
	
	public function SpendSP($amount = 0)
	{
		if($this->SkillPoints > 0)
		{
			$this->SkillPoints = $this->SkillPoints - $amount;
			if(isset($this->deltas['SkillPoints'])) $this->deltas['SkillPoints'] = $this->deltas['SkillPoints'] - $amount; else $this->deltas['SkillPoints'] = 0 - $amount;
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
		if($this->HitPoints <= 0) $this->Kill(false);
		return true;

	}
	
	public function Kill($public = true)
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
		
		if($public)
		{
			$action2 = $this->pixie->orm->get('ActivityLog');
            $action2->CharacterID = $this->CharacterID;
            $action2->Activity = '<span class="log-died">'.$this->Link.' has died!</span>';
			$action2->save();
			$characters = $this->pixie->orm->get('Character')->where('LocationID', '=',  $this->LocationID)->where('CharacterID', '<>',  $this->CharacterID)->find_all()->as_array();
			$actionreaderinserts = array();
			foreach($characters as $loopchar)
			{
                $actionreaderinserts[] = array('CharacterID' => $loopchar->CharacterID, 'ActivityLogID' => $action2->ActivityLogID);
			}
			if(count($actionreaderinserts) > 0) $this->pixie->db->query('insert')->table('activity_log_reader')->data($actionreaderinserts)->execute();
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
			//var_dump($this->pixie->db->query('update')->table('character')->data($updateparts)->where('CharacterID',$this->CharacterID)->query());
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
			// Weapons
			$weaponrydata = $this->pixie->db->query('select')->table('item_instance')->fields('item_usage_attribute.AttributeType', 'item_instance.*', 'item_type.*', 'item_usage_attribute.AttributeName', 'item_usage_attribute.AttributeValue')->join('item_type', array('item_instance.ItemTypeID', 'item_type.ItemTypeID'))->join('item_usage_attribute',array('item_instance.ItemTypeID', 'item_usage_attribute.ItemTypeID'),'left')->join('usage', array('item_usage_attribute.ItemUsageID', 'usage.UsageID'))->where('item_instance.CharacterID', $this->CharacterID)->where('usage.UsageName', 'weapon')->execute()->as_array();
			// Unique Weapons
			$weaponrydata2 = $this->pixie->db->query('select')->table('item_instance')->fields('item_instance.ItemInstanceID', 'special_item_attribute.AttributeName', 'special_item_attribute.AttributeValue')->join('item_type', array('item_instance.ItemTypeID', 'item_type.ItemTypeID'), 'inner')->join('special_item_attribute',array('item_instance.ItemInstanceID', 'special_item_attribute.ItemInstanceID'),'inner')->join('usage', array('special_item_attribute.ItemUsageID', 'usage.UsageID'), 'inner')->where('item_instance.CharacterID', $this->CharacterID)->where('usage.UsageName', 'weapon')->execute()->as_array();
			// Skills
			$weaponrydata3 = $this->pixie->db->query('select')->table('skill')->fields($this->pixie->db->expr('`skill`.`SkillName` AS \'ItemTypeName\''), $this->pixie->db->expr('CONCAT(\'S\', `skill`.`SkillID`) AS \'ItemInstanceID\''), 'skill_effect.AttributeName', 'skill_effect.AttributeValue')->join('skill_instance',array('skill.SkillID', 'skill_instance.SkillID'),'inner')->join('skill_effect',array('skill.SkillID', 'skill_effect.SkillID'),'inner')->join('usage', array('skill_effect.SkillUsageID', 'usage.UsageID'), 'inner')->where('skill_instance.CharacterID', $this->CharacterID)->where('usage.UsageName', 'weapon')->execute()->as_array();
			
			$weaponrydata = array_merge($weaponrydata, $weaponrydata2, $weaponrydata3);
			$weaponry = array();
			
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
						if($a == 'Tag')
						{
							$array = $weaponry[$weaponattribute->ItemInstanceID]->$a;
							$array[] = $weaponattribute->AttributeValue;
							$weaponry[$weaponattribute->ItemInstanceID]->$a = $array;
						}
						else if(is_numeric($weaponry[$weaponattribute->ItemInstanceID]->$a) && is_numeric($weaponattribute->AttributeValue))
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
						if($a == 'Tag')
						{
							$weaponry[$weaponattribute->ItemInstanceID]->$a = array($weaponattribute->AttributeValue);
						}
						else
						{
							$weaponry[$weaponattribute->ItemInstanceID]->$a =  $weaponattribute->AttributeValue;
						}
					}
				}
			}
			
			$tags = $this->WeaponBuffs;
			
			foreach($weaponry as $k => $v)
			{
				if(isset($v->Tag))
				{
					foreach($v->Tag as $t)
					{
						if(isset($tags[$t]))
						{
							foreach($tags[$t] as $attr => $value)
							{
								if(isset($v->$attr))
								{
									if($attr == 'Tag')
									{
										$v->$attr = array($value);
									}
									else if(is_numeric($v->$attr) && is_numeric($value))
									{
										$v->$attr = $v->$attr + $value;
									}
									else
									{
										// shit, how do we handle non-numeric duplicate attributes?
										// lets go for the last one, so that things can override one another
										$v->$attr = $value;
									}
								}
								else
								{
									if($a == 'Tag')
									{
										$array = $v->$attr;
										$array[] = $v->attr;
										$v->$attr = $array;
									}
									else
									{
										$v->$attr = $value;
									}
								}
							}
						
						}
					}
				}
				$weaponry[$k] = $v;
			}
			
			
			$working_weaponry = array();
			foreach($weaponry as $k => $v)
			{
				if(isset($v->Damage) && isset($v->DamageType) && isset($v->HitChance))
					$working_weaponry[$k] = $v;
			}
			return $working_weaponry;
		}
		else if($property == 'Defenses')
		{
			// Base items
			$def1 = $this->pixie->db->query('select')->table('item_instance')->fields($this->pixie->db->expr('MAX(CAST(`item_usage_attribute`.`AttributeValue` AS SIGNED)) AS \'Value\''), 'item_usage_attribute.AttributeName', 'item_usage_attribute.AttributeType')->join('item_type_usage', array('item_instance.ItemTypeID', 'item_type_usage.ItemTypeID'))->join('usage', array('item_type_usage.ItemUsageID', 'usage.UsageID'))->join('item_usage_attribute',array('item_instance.ItemTypeID', 'item_usage_attribute.ItemTypeID'),'left')->where('item_instance.CharacterID', $this->CharacterID)->where('usage.UsageName', $this->pixie->db->expr('\'armour\' AND (`item_usage_attribute`.`AttributeName` = \'Soak\' OR `item_usage_attribute`.`AttributeName` = \'Resist\')'))->group_by('item_usage_attribute.AttributeName', 'item_usage_attribute.AttributeType')->execute()->as_array();
			// Unique items
			$def2 = $this->pixie->db->query('select')->table('item_instance')->fields($this->pixie->db->expr('MAX(CAST(`special_item_attribute`.`AttributeValue` AS SIGNED)) AS \'Value\''), 'special_item_attribute.AttributeName', 'special_item_attribute.AttributeType')->join('item_type_usage', array('item_instance.ItemTypeID', 'item_type_usage.ItemTypeID'))->join('usage', array('item_type_usage.ItemUsageID', 'usage.UsageID'))->join('special_item_attribute',array('item_instance.ItemTypeID', 'special_item_attribute.ItemInstanceID'),'left')->where('item_instance.CharacterID', $this->CharacterID)->where('usage.UsageName', $this->pixie->db->expr('\'armour\' AND (`special_item_attribute`.`AttributeName` = \'Soak\' OR `special_item_attribute`.`AttributeName` = \'Resist\')'))->group_by('special_item_attribute.AttributeName', 'special_item_attribute.AttributeType')->execute()->as_array();
			// Skills
			$def3 = $this->pixie->db->query('select')->table('skill_instance')->fields($this->pixie->db->expr('MAX(CAST(`skill_effect`.`AttributeValue` AS SIGNED)) AS \'Value\''), 'skill_effect.AttributeName', 'skill_effect.AttributeType')->join('skill_effect',array('skill_effect.SkillID', 'skill_instance.SkillID'))->join('skill_usage', array('skill_effect.SkillUsageID', 'skill_usage.SkillUsageID'), 'inner')->join('usage', array('skill_usage.SkillUsageID', 'usage.UsageID'), 'inner')->where('skill_instance.CharacterID', $this->CharacterID)->where('usage.UsageName', $this->pixie->db->expr('\'armour\' AND (`skill_effect`.`AttributeName` = \'Soak\' OR `skill_effect`.`AttributeName` = \'Resist\')'))->group_by('skill_effect.AttributeName', 'skill_effect.AttributeType')->execute()->as_array();

			
			$def = array_merge($def1, $def2, $def3);
			
			$defenses = array();
			
			foreach($def as $defstat)
			{
				if(!isset($defenses[$defstat->AttributeType])) $defenses[$defstat->AttributeType] = array('Soak' => 0, 'Resist' => 0);
				$defenses[$defstat->AttributeType][$defstat->AttributeName] = max($defenses[$defstat->AttributeType][$defstat->AttributeName], $defstat->Value);
			}
			
			return $defenses;
		}
		else if($property == 'WeaponBuffs')
		{
			// Items
			$buff1 = $this->pixie->db->query('select')->table('item_instance')->fields('usage.UsageName', 'item_usage_attribute.AttributeName', 'item_usage_attribute.AttributeType', 'item_usage_attribute.AttributeValue')->join('item_usage_attribute',array('item_instance.ItemTypeID', 'item_usage_attribute.ItemTypeID'),'inner')->join('usage', array('item_usage_attribute.ItemUsageID', 'usage.UsageID'))->where('item_instance.CharacterID', $this->CharacterID)->where('usage.UsageName', 'weaponbuff')->execute()->as_array();
			// Unique Items
			$buff2 = $this->pixie->db->query('select')->table('item_instance')->fields('usage.UsageName', 'special_item_attribute.AttributeName', 'special_item_attribute.AttributeType', 'special_item_attribute.AttributeValue')->join('special_item_attribute',array('item_instance.ItemInstanceID', 'special_item_attribute.ItemInstanceID'),'inner')->join('usage', array('special_item_attribute.ItemUsageID', 'usage.UsageID'))->where('item_instance.CharacterID', $this->CharacterID)->where('usage.UsageName', 'weaponbuff')->execute()->as_array();
			// Skills
			$buff3 = $this->pixie->db->query('select')->table('skill')->fields('usage.UsageName', 'skill_effect.AttributeName', 'skill_effect.AttributeType', 'skill_effect.AttributeValue')->join('skill_effect',array('skill.SkillID', 'skill_effect.SkillID'),'inner')->join('usage', array('skill_effect.SkillUsageID', 'usage.UsageID'), 'inner')->join('skill_instance', array('skill.SkillID', 'skill_instance.SkillID'), 'inner')->where('skill_instance.CharacterID', $this->CharacterID)->where('usage.UsageName', 'weaponbuff')->execute()->as_array();
			$buffs = array_merge($buff1, $buff2, $buff3);
			$tags = array();
			
			foreach($buffs as $buff)
			{
				if(!isset($tags[$buff->AttributeType])) $tags[$buff->AttributeType] = array();
				if(!isset($tags[$buff->AttributeType][$buff->AttributeName]))
				{
					$tags[$buff->AttributeType][$buff->AttributeName] = $buff->AttributeValue;
				}
				else
				{
					if(is_numeric($tags[$buff->AttributeType][$buff->AttributeName]) && is_numeric($buff->AttributeValue))
					{
						$tags[$buff->AttributeType][$buff->AttributeName] = $tags[$buff->AttributeType][$buff->AttributeName] + $buff->AttributeValue;
					}
					else
					{
						// shit, how do we handle non-numeric duplicate attributes?
						// lets go for the last one, so that things can override one another
						$tags[$buff->AttributeType][$buff->AttributeName] = $buff->AttributeValue;
					}
				}
			}
			
			return $tags;
		}
		else if($property == 'CustomAttributes')
		{
			// Items
			$attr1 = $this->pixie->db->query('select')->table('item_instance')->fields('usage.UsageName', 'item_usage_attribute.AttributeName', 'item_usage_attribute.AttributeType', 'item_usage_attribute.AttributeValue')->join('item_usage_attribute',array('item_instance.ItemTypeID', 'item_usage_attribute.ItemTypeID'),'inner')->join('usage', array('item_usage_attribute.ItemUsageID', 'usage.UsageID'))->where('item_instance.CharacterID', $this->CharacterID)->where('usage.UsageName', 'customattribute')->execute()->as_array();
			// Unique Items
			$attr2 = $this->pixie->db->query('select')->table('item_instance')->fields('usage.UsageName', 'special_item_attribute.AttributeName', 'special_item_attribute.AttributeType', 'special_item_attribute.AttributeValue')->join('special_item_attribute',array('item_instance.ItemInstanceID', 'special_item_attribute.ItemInstanceID'),'inner')->join('usage', array('special_item_attribute.ItemUsageID', 'usage.UsageID'))->where('item_instance.CharacterID', $this->CharacterID)->where('usage.UsageName', 'customattribute')->execute()->as_array();
			// Skills
			$attr3 = $this->pixie->db->query('select')->table('skill')->fields('usage.UsageName', 'skill_effect.AttributeName', 'skill_effect.AttributeType', 'skill_effect.AttributeValue')->join('skill_effect',array('skill.SkillID', 'skill_effect.SkillID'),'inner')->join('usage', array('skill_effect.SkillUsageID', 'usage.UsageID'), 'inner')->join('skill_instance', array('skill.SkillID', 'skill_instance.SkillID'), 'inner')->where('skill_instance.CharacterID', $this->CharacterID)->where('usage.UsageName', 'customattribute')->execute()->as_array();
			$attrs = array_merge($attr1, $attr2, $attr3);
			$tags = array();
			
			foreach($attrs as $attr)
			{
				if(!isset($tags[$attr->AttributeName])) $tags[$attr->AttributeName] = $attr->AttributeValue;
				else
				{
					if(is_numeric($tags[$attr->AttributeName]) && is_numeric($attr->AttributeName))
					{
						$tags[$attr->AttributeName] = $tags[$attr->AttributeName] + $attr->AttributeValue;
					}
					else
					{
						// shit, how do we handle non-numeric duplicate attributes?
						// lets go for the last one, so that things can override one another
						$tags[$attr->AttributeName] = $attr->AttributeValue;
					}
				}
			}
			
			return $tags;
		}
	}
}