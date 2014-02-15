<?php 
namespace App\Controller;
class Game extends \App\Page{

	private $surroundings;
	private $character;
	private $dynamicjs = false;

	public function before() {
		if(!$this->pixie->auth->user()) // Can't play if not logged in
		{
			$this->execute = false;
			$this->response->body = 'Permission Denied';
			return;
		}
		
		$character = $this->pixie->orm->get('Character')->with('Location.Plane')->where('AccountID', $this->pixie->auth->user()->AccountID)->where('CharacterID', $this->request->param('CharacterID'))->find();
		if(!$character->loaded() || $this->pixie->auth->user()->AccountID != $character->AccountID) // Can't play other people's characters
		{
			$this->execute = false;
			$this->response->body = 'Permission Denied';
			return;
		}
		if($character->HitPoints <= 0) // Dead!
		{
		
			if($this->request->route->name != 'respawn' && $this->request->route->name != 'gameindex')
			{
				if($this->request->get('ajax'))
				{
					$this->response->body = '<script type="text/javascript">location.reload(true);</script>';
				}
				else
				{
					$this->response->body = $this->redirect('/game/'.$this->request->param('CharacterID'));
				}
				$this->execute = false;
				return;
			}
		
		
			if($this->request->get('ajax')) // AJAX requests won't require the entire page
			{
				if($this->request->get('target') == '#dynamicjs') // We are returning some javascript to execute
				{
					$this->view = $this->pixie->view('dynamicjs');
					$this->dynamicjs = true;
				}
				else
				{
					$this->view = $this->pixie->view('blank');
				}
			}
			else
			{
				$this->view = $this->pixie->view('index');
			}
			

		}
	
		if($this->request->get('ajax')) // AJAX requests won't require the entire page
		{
			if($this->request->get('target') == '#dynamicjs') // We are returning some javascript to execute
			{
				$this->view = $this->pixie->view('dynamicjs');
				$this->view->subview = '';
				$this->dynamicjs = true;
			}
			else
			{
				$this->view = $this->pixie->view('blank');
			}
		}
		else
		{
			$this->view = $this->pixie->view('index');
		}
		if(!$this->dynamicjs)	$this->view->subview = 'game/main';
		$this->view->right = 'map';
		$this->view->errors = '';
		$this->view->action = '';
		$this->view->warnings = '';
		$this->view->character = $character;
		$this->view->post = $this->request->post();
		$this->view->get = $this->request->get();
	}

	public function after()
	{
		if(!$this->dynamicjs)
		{
			$character = $this->view->character;
			$this->view->activitylog = $this->view->character->ActivityLog->order_by('Timestamp','DESC')->limit(25)->find_all()->as_array();
			$this->view->inventory = $this->pixie->orm->get('ItemInstance')->with('Type.Category')->where('CharacterID', $this->view->character->CharacterID)->find_all()->as_array();
			if($this->view->character->HitPoints > 0) $this->view->map = $this->pixie->orm->get('Location')->with('Type')->where('CoordinateX', '>', $character->Location->CoordinateX - 3)->where('CoordinateX', '<', $character->Location->CoordinateX + 3)->where('CoordinateY', '>', $character->Location->CoordinateY - 3)->where('CoordinateY', '<', $character->Location->CoordinateY + 3)->where('PlaneID', '=', $character->Location->PlaneID)->where('CoordinateZ', $character->Location->CoordinateZ)->order_by('CoordinateY','asc')->order_by('CoordinateX','asc')->find_all()->as_array();
			$this->view->activated_skills = $this->pixie->db->query('select')->table('skill')->join('skill_instance', array('skill.SkillID', 'skill_instance.SkillID'),'inner')->join('skill_usage', array('skill.SkillID', 'skill_usage.SkillID'),'inner')->join('usage', array('skill_usage.SkillUsageID', 'usage.UsageID'),'inner')->where('usage.UsageName', 'activated')->where('skill_instance.CharacterID', $character->CharacterID)->execute()->as_array();
		}
		parent::after();
	}
	
	public function action_index()
	{
		if($this->view->character->HitPoints <= 0) $this->view->subview = 'game/dead';
	}
	
	
	public function action_move()
	{
		$char = $this->view->character;
		// Fetch dest tile
		$loc = $this->pixie->orm->get('Location')->with('Plane')->with('Type')->where('LocationID', $this->request->param('arg1'))->find();
		
		if(!$loc->loaded()) // Hah, good try
		{
			$this->view->warnings = 'That location doesn\'t exist!';
			return;
		}
		
		$deltaX = abs($char->Location->CoordinateX - $loc->CoordinateX);
		$deltaY = abs($char->Location->CoordinateY - $loc->CoordinateY);
		$deltaZ = abs($char->Location->CoordinateZ - $loc->CoordinateZ);
		
		if(($deltaX <= 1 && $deltaY <= 1 && $deltaZ == 0) || ($deltaX == 0 && $deltaY == 0 && $deltaZ <= 1)) // We're either moving up/down or to adjacent tile
		{
		
			if($deltaX == 0 && $deltaY == 0 && $deltaZ == 0) // Let's not move to the same tile though
			{
				$this->view->warnings = 'You\'re already here.';
				return;
			}
			
			if($loc->Type->Traversible == 'Never' || $loc->Type->Traversible == 'WithAttribute') // Nope.jpg TODO: Check for attribute
			{
				$this->view->warnings = 'You can\'t move there!';
				return;
			}
		
			if(!$char->SpendAP($loc->Type->APCost)) // Need those sweet sweet APs
			{
				$this->view->warnings = 'You are too tired to move.';
				return;
			}
			$this->pixie->db->query('update')->table('character')->data(array('LocationID' => $loc->LocationID, 'ActionPoints' => $this->pixie->db->expr('`ActionPoints` - 1')))->where('CharacterID',$char->CharacterID)->execute();
			// Would be nice if it wasn't necessary to do another DB query
			$this->view->character = $this->pixie->orm->get('Character')->with('Location.Plane')->where('CharacterID', $char->CharacterID)->find();
			$this->view->character = $char;
		}
		else // Someone wants to break shit or is lagging like hell and button mashing
		{
			$this->view->warnings = 'That location isn\'t accessible from here!';
		}
			
	}
	
	public function action_speak()
	{
		if(trim($this->request->post('speech')) != '') // PHPixie automatically filters out dangerous HTML PHP and JS, so just check if there is content
		{
			$activity = $this->pixie->orm->get('ActivityLog');
			$activity->CharacterID = $this->view->character->CharacterID;
			if(strpos($this->request->post('speech'), '/me ') === 0) // Handle emotes
			{
				$activity->Activity = $this->view->character->Link.' '.substr($this->request->post('speech'), 4);
			}
			else
			{
				$activity->Activity = $this->view->character->Link.' said \''.$this->request->post('speech').'\'';
			}
			$activity->save();
			
			// Find everyone on the tile who witness this drivel
			$characters = $this->view->character->Location->Character->find_all()->as_array();
			
			$data = array();
			
			foreach($characters as $char)
			{
				$data[] = array('CharacterID' => $char->CharacterID, 'ActivityLogID' => $activity->ActivityLogID);
			}
			
			$this->pixie->db->query('insert')->table('activity_log_reader')->data($data)->execute(); // I contributed batch inserts to PHPixie :D
		}
	}
	
	public function action_drop()
	{
		$this->view->right = 'inventory';
		$char = $this->view->character;
		
		$item = $this->pixie->orm->get('ItemInstance')->with('Type')->where('CharacterID', $char->CharacterID)->where('ItemInstanceID', $this->request->param('arg1'))->find();
		
		if($item->loaded()) // Ensure item exists and belongs to correct player
		{	
			$this->view->action = 'You drop your '.$item->Type->ItemTypeName;
			$this->view->ItemInstanceID = $item->ItemInstanceID;
			$this->pixie->db->get()->execute("START TRANSACTION");
			$this->pixie->db->query('delete')->table('item_usage_attribute')->where('ItemInstanceID', $item->ItemInstanceID)->execute();
			$item->delete();
			$this->pixie->db->get()->execute("COMMIT");
			if($this->dynamicjs)
			{
				$this->view->subview = 'game/dynamicjs/dropitem';
			}
		}
		else
		{
			$this->view->warnings = 'That item doesn\'t exist!';
		}
	}
	
	public function action_search()
	{
		$this->view->right = 'inventory';
		$char =& $this->view->character;
		
		
		if(!$char->SpendAP(1))
		{
			$this->view->warnings = 'You are too tired to move.';
			return;
		}
		
		$searchodds = $this->pixie->db->query('select')->table('search_odds')->where('TileTypeID',$char->Location->TileTypeID)->execute()->as_array();
		
		$sum = 0;
		// Sum search odds
		foreach($searchodds as $odd)
		{
			$sum += $odd->ChanceWeight;
		}
		
		if($sum <= 10000) $sum = 10000; // Ensures chance to find nothing if odds add up to less than 10000
		
		$result = mt_rand(0, $sum);
		$found = null;
		
		/* Example of searching in action
		
		-------------
		Duck  | 5
		Goose | 10
		Lemon | 100
		
		A random value of 7 will find a Goose.
		*/
		
		
		foreach($searchodds as $odd) // Iterate over items until finding right one
		{
			$sum -= $odd->ChanceWeight; // Should double check this to avoid off by one horrors
			if($sum <= $result)
			{
				$found = $odd->ItemTypeID;
				break;
			}
		}
		
		if($found == null)
		{
			$this->view->action = 'You search and find nothing';
			$char->deltas();
		}
		else
		{
			$item = $this->pixie->orm->get('ItemType')->where('ItemTypeID', $found)->find();
			
			$myitem = $this->pixie->orm->get('ItemInstance');
			$myitem->CharacterID = $char->CharacterID;
			$myitem->ItemTypeID = $found;
			$this->pixie->db->get()->execute("START TRANSACTION");
			$char->deltas();
			$myitem->Save();
			$this->pixie->db->get()->execute("COMMIT");
			// No more "a(n)"!
			$this->view->action = 'You search and find '.$item->Article.' '.$item->ItemTypeName.'!';
		}
	}
	
	public function action_attack()
	{
		$char =& $this->view->character;
		$target = $this->pixie->orm->get('Character')->where('CharacterID', $this->request->post('CharacterID'))->find();
		$this->view->selectedchar = $this->request->post('CharacterID');
		if(!$target->loaded() || $char->LocationID != $target->LocationID || $target->HitPoints <= 0)
		{
			$this->view->warnings = 'That character isn\'t here!';
			return;
		}
		if(!isset($char->Weaponry[$this->request->post('ItemInstanceID')]))
		{
			$this->view->warnings = 'You can\'t attack with that!';
			return;	
		}
		$this->view->selectedweapon = $this->request->post('ItemInstanceID');
		if(!$char->SpendAP(1))
		{
			$this->view->warnings = 'You are too tired to attack.';
			return;
		}
		
		$weapon = $char->Weaponry[$this->request->post('ItemInstanceID')];
		$result = mt_rand(0, 99);
		
		if($result >= $weapon->HitChance) // missed
		{
			$this->view->action = 'You attack '.$target->Link.' with your '.$weapon->ItemTypeName.' and miss';
            
            //Attacker log
			$actionattacker = $this->pixie->orm->get('ActivityLog');
			$actionattacker->CharacterID = $char->CharacterID;
			$actionattacker->Activity = '<span class="log-attack-miss">'.$this->view->action.'</span>';
			
			
			//Target log
			$actiondefender = $this->pixie->orm->get('ActivityLog');
			$actiondefender->CharacterID = $target->CharacterID;
			$actiondefender->Activity = '<span class="log-defend-miss">'.$char->Link.' attacked you with '.$weapon->Article.' '.$weapon->ItemTypeName.' and missed.</span>';
			
			$this->pixie->db->get()->execute("START TRANSACTION");
			$char->deltas();
			$actionattacker->save();
			$actiondefender->save();
			$this->pixie->db->query('insert')->table('activity_log_reader')->data(array(array('CharacterID' => $actionattacker->CharacterID, 'ActivityLogID' => $actionattacker->ActivityLogID), array('CharacterID' => $actiondefender->CharacterID, 'ActivityLogID' => $actiondefender->ActivityLogID)))->execute();
			$this->pixie->db->get()->execute("COMMIT");
			
			return;
		}
		
		$damage = $weapon->Damage;
		
		if(isset($target->Defenses[$weapon->DamageType]))
		{
			$damage = $damage - $target->Defenses[$weapon->DamageType]['Soak'];
			$resist = $target->Defenses[$weapon->DamageType]['Resist'];
			if($resist > 0) $damage = round($damage * (100 - $resist) / 100);
			if($weapon->Damage > 0 && $resist < 100)  $damage = max($damage, 1);
		}
		
		$soaked = $weapon->Damage - $damage;

		$this->view->action = 'You attack '.$target->Link.' with your '.$weapon->ItemTypeName.', dealing '.$damage.' '.$weapon->DamageType.' damage and gaining '.$damage. 'XP!';
		if($soaked > 0) $this->view->action .= ' Damage reduced by '.$soaked.' due to soaks and resists.';

		$char->AlterXP($damage);
		$target->AlterHP(0 - $damage);    
		$actionreaderinserts = array();
		
		if($target->HitPoints <= 0)
		{
			$this->view->action .= ' This was enough to kill them!';
            //Everyone in area log
            $action = $this->pixie->orm->get('ActivityLog');
            $action->CharacterID = $char->CharacterID;
            $action->Activity = '<span class="log-player-kill">'.$char->Link.' attacked '.$target->Link.' with '.$weapon->Article.' '.$weapon->ItemTypeName.', killing them!</span>';
			$action->save();
			$characters = $this->view->character->Location->Character->find_all()->as_array();
			foreach($characters as $loopchar)
			{
				if ($loopchar->CharacterID != $char->CharacterID && $loopchar->CharacterID != $target->CharacterID)
                    $actionreaderinserts[] = array('CharacterID' => $loopchar->CharacterID, 'ActivityLogID' => $action->ActivityLogID);
			}
			
		}
		
        //Attacker log
		$actionattacker = $this->pixie->orm->get('ActivityLog');
		$actionattacker->CharacterID = $char->CharacterID;
		$actionattacker->Activity = '<span class="log-attack-hit">'.$this->view->action.'</span>';

        //Target log
		$actiondefender = $this->pixie->orm->get('ActivityLog');
		$actiondefender->CharacterID = $target->CharacterID;
		$actiondefender->Activity = '<span class="log-defend-hit">'.$char->Link.' attacked you with '.$weapon->Article.' '.$weapon->ItemTypeName.' and hit, dealing '.$damage.' '.$weapon->DamageType.' damage'.($soaked > 0 ? ' (reduced by '.$soaked.' due to soaks and resists)' : '').'</span>';
		$this->pixie->db->get()->execute("START TRANSACTION");
		$actionattacker->save();
		$actionreaderinserts[] = array('CharacterID' => $actionattacker->CharacterID, 'ActivityLogID' => $actionattacker->ActivityLogID);
		$actiondefender->save();
		$actionreaderinserts[] = array('CharacterID' => $actiondefender->CharacterID, 'ActivityLogID' => $actiondefender->ActivityLogID);
		$this->pixie->db->query('insert')->table('activity_log_reader')->data($actionreaderinserts)->execute(); // Wind contributed batch inserts to Pixie, because Wind is pretty great
		$char->deltas();
		$target->deltas();
		$this->pixie->db->get()->execute("COMMIT");	
	}
	
	public function action_respawn()
	{
		$this->view->character->Respawn();
		$this->view->character = $this->pixie->orm->get('Character')->with('Location.Plane')->where('AccountID', $this->pixie->auth->user()->AccountID)->where('CharacterID', $this->request->param('CharacterID'))->find();
		$this->view->map = $this->pixie->orm->get('Location')->with('Type')->where('CoordinateX', '>', $this->view->character->Location->CoordinateX - 3)->where('CoordinateX', '<', $this->view->character->Location->CoordinateX + 3)->where('CoordinateY', '>', $this->view->character->Location->CoordinateY - 3)->where('CoordinateX', '<', $this->view->character->Location->CoordinateY + 3)->where('PlaneID', '=', $this->view->character->Location->PlaneID)->where('CoordinateZ', $this->view->character->Location->CoordinateZ)->order_by('CoordinateY','asc')->order_by('CoordinateX','asc')->find_all()->as_array();
		$this->view->action = 'You have respawned.';
	}
	
	public function action_activate()
	{
		if(!$this->request->param('arg1'))
		{
			$this->view->warnings = 'You can\'t use that!';
			return;
		}
		$char =& $this->view->character;
		$skill = $this->pixie->db->query('select')->table('skill')->join('skill_instance', array('skill.SkillID', 'skill_instance.SkillID'),'inner')->join('skill_usage', array('skill.SkillID', 'skill_usage.SkillID'),'inner')->join('usage', array('skill_usage.SkillUsageID', 'usage.UsageID'),'inner')->where('skill_instance.SkillID', $this->request->param('arg1'))->where('skill_instance.CharacterID', $char->CharacterID)->execute()->as_array();
		if(count($skill) != 1)
		{
			$this->view->warnings = 'You can\'t use that skill!';
			return;
		}

		$skill = $skill[0];
			
		$effects = $this->pixie->db->query('select')->table('skill_effect')->join('usage', array('skill_effect.SkillUsageID', 'usage.UsageID'),'inner')->where('skill_effect.SkillID', $this->request->param('arg1'))->where('usage.UsageName', 'activated')->execute()->as_array();
		
		// Pass 1 - Determine if we meet all the requirements to activate this skill
		
		foreach($effects as $effect)
		{
			switch($effect->AttributeName)
			{
				case 'APCost':
				{
					if($char->ActionPoints < $effect->AttributeValue)
					{
						$this->view->warnings = 'You need '.$effect->AttributeValue.'AP to do that!';
						return;
					}
					break;
				}
				default:
				{
					break;
				}
			}
		}
		
		$setplayer_whitelist = array('ActionPoints', 'HitPoints', 'SkillPoints', 'Experience', 'LocationID');
		
		$oldlocation = $char->LocationID;
		
		$this->pixie->db->get()->execute("START TRANSACTION");
		foreach($effects as $effect)
		{
			switch($effect->AttributeName)
			{
				case 'APCost':
				{
					$char->ActionPoints = $char->ActionPoints - $effect->AttributeValue;
					break;
				}
				case 'SetSelf':
				{
					$tar = $effect->AttributeType;
					if(in_array($tar, $setplayer_whitelist)) $char->$tar = $effect->AttributeValue;
					if($tar == 'HitPoints' && $effect->AttributeValue <= 0) $checkdeaths = true;
					break;
				}			
				case 'AlterSelf':
				{
					$tar = $effect->AttributeType;
					if(in_array($tar, $setplayer_whitelist)) $char->$tar = $char->$tar + $effect->AttributeValue;
					if($tar == 'HitPoints' && $effect->AttributeValue < 0) $checkdeaths = true;
					break;
				}	
				case 'AlterAdjacent':
				{
					$tar = $effect->AttributeType;
					if(in_array($tar, $setplayer_whitelist)) $this->pixie->db->query('update')->table('character')->data(array($tar => $this->pixie->db->expr('`'.$tar.'`'.($effect->AttributeValue >= 0 ? ' + '.intval($effect->AttributeValue) : ' - '.(0 - intval($effect->AttributeValue))))))->where('LocationID', $char->LocationID)->where('CharacterID', '<>' , $char->CharacterID)->execute();
					if($tar == 'HitPoints' && $effect->AttributeValue < 0)
					{
						$characters = $this->pixie->orm->get('Character')->where('LocationID', '=', $oldlocation)->where('HitPoints', '<=', 0)->find_all()->as_array();
						foreach($characters as $character)
						{
							$character->Kill(true);
						}
					}
					break;
				}
				case 'SetAdjacent':
				{
					$tar = $effect->AttributeType;
					if(in_array($tar, $setplayer_whitelist)) if(in_array($tar, $setplayer_whitelist)) $this->pixie->db->query('update')->table('character')->data(array($tar => $effect->AttributeValue))->where('LocationID', $char->LocationID)->where('CharacterID', '<>' , $char->CharacterID)->execute();
					if($tar == 'HitPoints' && $effect->AttributeValue <= 0)
					{
						$characters = $this->pixie->orm->get('Character')->where('LocationID', '=', $oldlocation)->where('HitPoints', '<=', 0)->find_all()->as_array();
						foreach($characters as $character)
						{
							$character->Kill(true);
						}
					}
					break;
				}
				case 'MessageCurrentTile':
				{
					$messageforall = $this->pixie->orm->get('ActivityLog');
					$messageforall->CharacterID = $char->CharacterID;
					$messageforall->Activity = '<span class="'.$effect->AttributeType.'">'.str_replace('%%PLAYER%%', $char->Link ,$effect->AttributeValue).'</span>';
					$messageforall->Save();
					$characters = $char->Location->Character->find_all()->as_array();
					$actionreaderinserts = array();
					foreach($characters as $loopchar)
					{
						$actionreaderinserts[] = array('CharacterID' => $loopchar->CharacterID, 'ActivityLogID' => $messageforall->ActivityLogID);
					}
					$this->pixie->db->query('insert')->table('activity_log_reader')->data($actionreaderinserts)->execute();
					break;
				}
				default:
				{
					break;
				}
			}
		}
		$char->Save();
		$this->pixie->db->get()->execute("COMMIT");
		if($char->HitPoints <= 0)
		{
			$char->Kill(false);
			$this->response->body = $this->redirect('/game/'.$this->request->param('CharacterID'));
			$this->execute = false;
		}
	}
	
	public function action_skills()
	{
		$char =& $this->view->character;
		if($this->request->param('arg1'))
		{
			$skill = $this->pixie->orm->get('Skill')->where('SkillID', $this->request->param('arg1'))->find();
			$hasit_check = $this->pixie->db->query('select')->table('skill_instance')->where('skill_instance.SkillID', $skill->SkillID)->where('skill_instance.CharacterID', $char->CharacterID)->execute()->as_array();
			if(!$skill->Loaded())
			{
				$this->view->warnings = 'That skill doesn\'t exist!';
			}
			else if(count($hasit_check) > 0)
			{
				$this->view->warnings = 'You already know this skill...';
			}
			else if(!$char->SpendSP($skill->SkillBaseCost))
			{
				$this->view->warnings = 'You need more Skill Points to learn that skill!';
			}
			else
			{
				$this->pixie->db->query('insert')->table('skill_instance')->data(array('SkillID' => $skill->SkillID, 'CharacterID' => $char->CharacterID))->execute();
				$this->view->action = 'You have learnt '.$skill->SkillName.'!';
			}
		}
	
		$this->view->subview = 'game/skills';
		$this->view->skills = $this->pixie->db->query('select')->table('skill')->execute()->as_array();
		$myskills = array();
		$mine = $this->pixie->db->query('select')->table('skill_instance')->where('skill_instance.CharacterID', $this->request->param('CharacterID'))->execute()->as_array();
		foreach($mine as $v)
		{
			$myskills[] = $v->SkillID;
		}
		$this->view->myskills = $myskills;
	}
}