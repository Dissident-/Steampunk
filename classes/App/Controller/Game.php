<?php 
namespace App\Controller;
class Game extends \App\Page{

	private $character;
	private $surroundings;
	
	private $dynamicjs = false;

	public function before() {
		if(!$this->pixie->auth->user()) // Can't play if not logged in
		{
			$this->execute = false;
			$this->response->body = 'Permission Denied';
			return false;
		}
		
		$this->character = $this->pixie->orm->get('Character')->with('Location.Plane')->where('AccountID', $this->pixie->auth->user()->AccountID)->where('CharacterID', $this->request->param('CharacterID'))->find();
	
		if(!$this->character->loaded() || $this->pixie->auth->user()->AccountID != $this->character->AccountID) // Can't play other people's characters
		{
			$this->execute = false;
			$this->response->body = 'Permission Denied';
			return false;
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
		if(!$this->dynamicjs) $this->view->subview = 'game/main';
		$this->view->right = 'map';
		$this->view->errors = '';
		$this->view->action = '';
		$this->view->warnings = '';
		$this->view->character = $this->character;
		$char = $this->character;
		$this->view->inventory = $this->pixie->orm->get('ItemInstance')->with('Type.Category')->where('CharacterID', $this->character->CharacterID)->find_all()->as_array();
		$this->view->map = $this->pixie->orm->get('Location')->with('Type')->where('CoordinateX', '>', $char->Location->CoordinateX - 3)->where('CoordinateX', '<', $char->Location->CoordinateX + 3)->where('CoordinateY', '>', $char->Location->CoordinateY - 3)->where('CoordinateX', '<', $char->Location->CoordinateY + 3)->where('PlaneID', '=', $char->Location->PlaneID)->where('CoordinateZ', $char->Location->CoordinateZ)->order_by('CoordinateY','asc')->order_by('CoordinateX','asc')->find_all()->as_array();
		$this->view->post = $this->request->post();
		$this->view->get = $this->request->get();
		
		$this->view->activitylog = $this->view->character->ActivityLog->order_by('Timestamp','DESC')->limit(25)->find_all()->as_array();
	}

	public function action_index()
	{
		// Don't need to do anything here because the before handles it all :D
	}
	
	
	public function action_move()
	{
		$char = $this->character;
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
		
			if($char->ActionPoints <= 0) // Need those sweet sweet APs
			{
				$this->view->warnings = 'You are too tired to move.';
				return;
			}
			$char->ActionPoints = $char->ActionPoints - 1;
			$char->Save();
			$this->pixie->db->query('update')->table('character')->data(array('LocationID' => $loc->LocationID))->where('CharacterID',$char->CharacterID)->execute();
			// Would be nice if it wasn't necessary to do another DB query
			$this->character = $this->pixie->orm->get('Character')->with('Location.Plane')->where('CharacterID', $char->CharacterID)->find();
			$this->surroundings = $this->pixie->orm->get('Location')->with('Type')->where('CoordinateX', '>', $char->Location->CoordinateX - 3)->where('CoordinateX', '<', $char->Location->CoordinateX + 3)->where('CoordinateY', '>', $char->Location->CoordinateY - 3)->where('CoordinateX', '<', $char->Location->CoordinateY + 3)->where('PlaneID', '=', $char->Location->PlaneID)->where('CoordinateZ', $char->Location->CoordinateZ)->order_by('CoordinateY','asc')->order_by('CoordinateX','asc')->find_all()->as_array();
			$this->view->character = $this->character;
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
			$activity->CharacterID = $this->character->CharacterID;
			if(strpos($this->request->post('speech'), '/me ') === 0) // Handle emotes
			{
				$activity->Activity = '<a href="/character/'.$activity->CharacterID.'">'.$this->character->CharName.'</a> '.substr($this->request->post('speech'), 4);
			}
			else
			{
				$activity->Activity = '<a href="/character/'.$activity->CharacterID.'">'.$this->character->CharName.'</a> said \''.$this->request->post('speech').'\'';
			}
			$activity->save();
			
			// Find everyone on the tile who witness this drivel
			$characters = $this->character->Location->Character->find_all()->as_array();
			
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
		$char = $this->character;
		
		$item = $this->pixie->orm->get('ItemInstance')->with('Type')->where('ItemInstanceID', $this->request->param('arg1'))->find();
		
		if($item->loaded() && $item->CharacterID == $char->CharacterID) // Ensure item exists and belongs to correct player
		{	
			if($this->dynamicjs)
			{
				$this->view->subview = 'game/dynamicjs/dropitem';
				$this->view->ItemInstanceID = $item->ItemInstanceID;
			}
			else
			{
				// Avoidable query by removing from array instead, probably faster later on
				$this->view->inventory = $this->pixie->orm->get('ItemInstance')->with('Type.Category')->where('CharacterID', $this->character->CharacterID)->find_all()->as_array();
			}
			$this->view->action = 'You drop your '.$item->Type->ItemTypeName;
			$item->delete();
		}
		else
		{
			$this->view->warnings = 'That item doesn\'t exist!';
		}
	}
	
	public function action_search()
	{
		$this->view->right = 'inventory';
		$char = $this->character;
		
		
		if($char->ActionPoints <= 0)
		{
			$this->view->warnings = 'You are too tired to move.';
			return;
		}
		$char->ActionPoints = $char->ActionPoints - 1;
		$char->Save();
		$this->character = $char;
		
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
			
			$this->view->inventory = $this->pixie->orm->get('ItemInstance')->with('Type.Category')->where('CharacterID', $this->character->CharacterID)->find_all()->as_array();
			// No more "a(n)"!
			$this->view->action = 'You search and find '.(preg_match('/^[aeiou]|s\z/i', $item->ItemTypeName) ? 'an' : 'a').' '.$item->ItemTypeName.'!';
		}
	}
}