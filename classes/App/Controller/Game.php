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
		$this->view->inventory = $this->pixie->orm->get('ItemInstance')->with('Type.Category')->where('CharacterID', $this->view->character->CharacterID)->find_all()->as_array();
		if($this->view->character->HitPoints > 0) $this->view->map = $this->pixie->orm->get('Location')->with('Type')->where('CoordinateX', '>', $character->Location->CoordinateX - 3)->where('CoordinateX', '<', $character->Location->CoordinateX + 3)->where('CoordinateY', '>', $character->Location->CoordinateY - 3)->where('CoordinateX', '<', $character->Location->CoordinateY + 3)->where('PlaneID', '=', $character->Location->PlaneID)->where('CoordinateZ', $character->Location->CoordinateZ)->order_by('CoordinateY','asc')->order_by('CoordinateX','asc')->find_all()->as_array();
		$this->view->post = $this->request->post();
		$this->view->get = $this->request->get();
		
		// TODO: Select more records if there's been a lot of activity since last user login
		$this->view->activitylog = $this->view->character->ActivityLog->order_by('Timestamp','DESC')->limit(25)->find_all()->as_array();
	}

	public function action_index()
	{
		if($this->view->character->HitPoints <= 0) $this->view->subview = 'game/dead';
	}
	
	
	public function action_move()
	{
		$char = $this->view->character;
		// Fetch dest tile
		$loc = $this->pixie->orm->get('Location')->with('Plane')->where('LocationID', $this->request->param('arg1'))->find();
		
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
		
			if(!$char->SpendAP(1)) // Need those sweet sweet APs
			{
				$this->view->warnings = 'You are too tired to move.';
				return;
			}
			$this->pixie->db->query('update')->table('character')->data(array('LocationID' => $loc->LocationID))->where('CharacterID',$char->CharacterID)->execute();
			// Would be nice if it wasn't necessary to do another DB query
			$this->view->character = $this->pixie->orm->get('Character')->with('Location.Plane')->where('CharacterID', $char->CharacterID)->find();
			$this->surroundings = $this->pixie->orm->get('Location')->with('Type')->where('CoordinateX', '>', $char->Location->CoordinateX - 3)->where('CoordinateX', '<', $char->Location->CoordinateX + 3)->where('CoordinateY', '>', $char->Location->CoordinateY - 3)->where('CoordinateX', '<', $char->Location->CoordinateY + 3)->where('PlaneID', '=', $char->Location->PlaneID)->where('CoordinateZ', $char->Location->CoordinateZ)->order_by('CoordinateY','asc')->order_by('CoordinateX','asc')->find_all()->as_array();
			$this->view->character = $char;
			$this->view->map = $this->surroundings;
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
			
			$this->view->activitylog = $this->view->character->ActivityLog->order_by('Timestamp','DESC')->limit(25)->find_all()->as_array();
		}
	}
	
	public function action_drop()
	{
		$this->view->right = 'inventory';
		$char = $this->view->character;
		
		$item = $this->pixie->orm->get('ItemInstance')->with('Type')->where('ItemInstanceID', $this->request->param('arg1'))->find();
		
		if($item->loaded() && $item->CharacterID == $char->CharacterID) // Ensure item exists and belongs to correct player
		{	
			$this->view->action = 'You drop your '.$item->Type->ItemTypeName;
			$this->view->ItemInstanceID = $item->ItemInstanceID;
			$item->delete();
			if($this->dynamicjs)
			{
				$this->view->subview = 'game/dynamicjs/dropitem';
			}
			else
			{
				// Avoidable query by removing from array instead, probably faster later on
				$this->view->inventory = $this->pixie->orm->get('ItemInstance')->with('Type.Category')->where('CharacterID', $this->view->character->CharacterID)->find_all()->as_array();
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
		$char = $this->view->character;
		
		
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
		}
		else
		{
			$item = $this->pixie->orm->get('ItemType')->where('ItemTypeID', $found)->find();
			
			$myitem = $this->pixie->orm->get('ItemInstance');
			$myitem->CharacterID = $char->CharacterID;
			$myitem->ItemTypeID = $found;
			$myitem->Save();
			
			$this->view->inventory = $this->pixie->orm->get('ItemInstance')->with('Type.Category')->where('CharacterID', $this->view->character->CharacterID)->find_all()->as_array();
			// No more "a(n)"!
			$this->view->action = 'You search and find '.$item->Article.' '.$item->ItemTypeName.'!';
		}
	}
	
	public function action_attack()
	{
		$char = $this->view->character;
		$target = $this->pixie->orm->get('Character')->where('CharacterID', $this->request->post('CharacterID'))->find();
		$this->view->selectedchar = $this->request->post('CharacterID');
		if(!$target->loaded() || $char->LocationID != $target->LocationID)
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
			$action = $this->pixie->orm->get('ActivityLog');
			$action->CharacterID = $char->CharacterID;
			$action->Activity = '<span class="log-attack-miss">'.$this->view->action.'</span>';
			$action->save();
			$char->add('ActivityLog', $action);
			//Target log
			$action = $this->pixie->orm->get('ActivityLog');
			$action->CharacterID = $char->CharacterID;
			$action->Activity = '<span class="log-defend-miss">'.$char->Link.' attacked you with '.$weapon->Article.' '.$weapon->ItemTypeName.' and missed.</span>';
			$action->save();
			$target->add('ActivityLog', $action);
			return;
		}
		
		// TODO: Soaks
		$this->view->action = 'You attack '.$target->Link.' with your '.$weapon->ItemTypeName.', dealing '.$weapon->Damage.' '.$weapon->DamageType.' damage and gaining '.$weapon->Damage. 'XP!';

		$char->Experience = $char->Experience + $weapon->Damage;
		$char->save();
		$target->HitPoints = $target->HitPoints - $weapon->Damage;
		$target->save();
        
		if($target->HitPoints <= 0)
		{
			$target->Kill();
			$this->view->action .= ' This was enough to kill them!';
            //Everyone in area log
            $action = $this->pixie->orm->get('ActivityLog');
            $action->CharacterID = $char->CharacterID;
            $action->Activity = '<span class="log-player-kill">'.$char->Link.' attacked '.$target->Link.' with '.$weapon->Article.', killing them!</span>';
			$characters = $this->view->character->Location->Character->find_all()->as_array();
			$data = array();
			foreach($characters as $loopchar)
			{
				if ($loopchar->CharacterID != $char->CharacterID && $loopchar->CharacterID != $target->CharacterID)
                    $$data[] = array('CharacterID' => $loopchar->CharacterID, 'ActivityLogID' => $action->ActivityLogID);
			}
			$this->pixie->db->query('insert')->table('activity_log_reader')->data($data)->execute(); // Wind contributed batch inserts to Pixie, because Wind is pretty great
		}
		
        //Attacker log
		$action = $this->pixie->orm->get('ActivityLog');
		$action->CharacterID = $char->CharacterID;
		$action->Activity = '<span class="log-attack-hit">'.$this->view->action.'</span>';
		$action->save();
		$char->add('ActivityLog', $action);
        //Target log
		$action = $this->pixie->orm->get('ActivityLog');
		$action->CharacterID = $char->CharacterID;
		$action->Activity = '<span class="log-defend-hit">'.$char->Link.' attacked you with '.$weapon->Article.' '.$weapon->ItemTypeName.' and hit, dealing '.$weapon->Damage.' '.$weapon->DamageType.' damage.</span>';
		$action->save();
		$target->add('ActivityLog', $action);
		
		// Need to do this query again in order to pick up on new stuff. TODO: Figure out why adding this into the after() function breaks shit
		$this->view->activitylog = $this->view->character->ActivityLog->order_by('Timestamp','DESC')->limit(25)->find_all()->as_array();
	}
	
	public function action_respawn()
	{
		$this->view->character->Respawn();
		$this->view->character = $this->pixie->orm->get('Character')->with('Location.Plane')->where('AccountID', $this->pixie->auth->user()->AccountID)->where('CharacterID', $this->request->param('CharacterID'))->find();
		$this->view->map = $this->pixie->orm->get('Location')->with('Type')->where('CoordinateX', '>', $this->view->character->Location->CoordinateX - 3)->where('CoordinateX', '<', $this->view->character->Location->CoordinateX + 3)->where('CoordinateY', '>', $this->view->character->Location->CoordinateY - 3)->where('CoordinateX', '<', $this->view->character->Location->CoordinateY + 3)->where('PlaneID', '=', $this->view->character->Location->PlaneID)->where('CoordinateZ', $this->view->character->Location->CoordinateZ)->order_by('CoordinateY','asc')->order_by('CoordinateX','asc')->find_all()->as_array();
		$this->view->action = 'You have respawned.';
		
		// Need to do this query again in order to pick up on new stuff. TODO: Figure out why adding this into the after() function breaks shit
		$this->view->activitylog = $this->view->character->ActivityLog->order_by('Timestamp','DESC')->limit(25)->find_all()->as_array();
	}
}