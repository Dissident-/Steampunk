<?php 
namespace App\Controller;
class Character extends \App\Page{

	public function before() {
		if(!$this->pixie->auth->user())
		{
			$this->execute = false;
			$this->response->body = 'Permission Denied';
			return false;
		}
	
		if($this->request->get('ajax'))
		{
			$this->view = $this->pixie->view('blank');
		}
		else
		{
			$this->view = $this->pixie->view('index');
		}
		$this->view->errors = '';
		$this->view->post = $this->request->post();
	}

	
	public function action_list()
	{
		$chars = $this->pixie->orm->get('Character')->with('Location.Plane')->where('AccountID', $this->pixie->auth->user()->AccountID)->order_by('CharName', 'ASC')->find_all()->as_array();
		$post = $this->request->post();
		
		$this->view->characters = $chars;

		$this->view->subview = 'game/characterlist';

	}
	
	public function action_create()
	{
		if($this->pixie->orm->get('Character')->where('AccountID', $this->pixie->auth->user()->AccountID)->count_all() >= 3)
		{
			$this->execute = false;
			$this->response->body = 'You aren\'t allowed more than 3 characters!';
			return;
		}
	
		if($this->request->method == 'POST')
		{
		
			$validate = $this->pixie->validate->get(array('CharName' => trim($this->request->post('CharName'))));
			$pixie = $this->pixie;
			$validate->field('CharName', true)->rule('callback', function($value) use ($pixie){
				return $pixie->orm->get('Character')->where('CharName', $value)->count_all() == 0 ? true : false;
			})->error('Character Name is already taken.');
			$validate->field('CharName')->rules('filled')->error('Character Name required (duh)');
			
			if($validate->valid())
			{
			
				
				$char = $this->pixie->orm->get('Character');
				$char->CharName = trim($this->request->post('CharName'));
				$char->LocationID = 1;
				$char->AccountID = $this->pixie->auth->user()->AccountID;
				$char->save();
				
				$this->action_list();
			}
			else
			{
				$this->view->errors = $validate->errors();
				$this->view->subview = 'game/charactercreate';
			}
		
		}
		else
		{
			$this->view->subview = 'game/charactercreate';
		}
	}
	
	public function action_delete()
	{
		$this->view = $this->pixie->view('json');
		$character = $this->pixie->orm->get('Character')->where('CharacterID', $this->request->post('CharacterID'))->find();
		if($character->loaded() && $character->AccountID == $this->pixie->auth->user()->AccountID)
		{
		
			//TODO: Consider reserving name for a while, or not actually deleting the characters
		
			$this->pixie->db->get()->execute("START TRANSACTION");
		
			$this->pixie->db->query('delete')->table('skill_instance')->where('CharacterID', $character->CharacterID)->execute();
			$this->pixie->db->query('delete')->table('activity_log_reader')->where('CharacterID', $character->CharacterID)->execute();
			$this->pixie->db->query('update')->table('activity_log')->data(array('CharacterID' => null))->where('CharacterID', $character->CharacterID)->execute();
			$this->pixie->db->query('update')->table('status_effect_instance')->data(array('OriginatingCharacterID' => null))->where('OriginatingCharacterID', $character->CharacterID)->execute();
			$this->pixie->db->query('delete')->table('status_effect_instance')->where('CharacterID', $character->CharacterID)->execute();
			$this->pixie->db->query('delete')->table('item_usage_attribute')->join('item_instance', array('item_instance.ItemInstanceID', 'item_usage_attribute.ItemInstanceID'), 'inner')->where('item_instance.CharacterID', $character->CharacterID)->execute();
			$this->pixie->db->query('delete')->table('item_instance')->where('CharacterID', $character->CharacterID)->execute();
			// Great stuff, this query doens't want to work
			//$this->pixie->db->query('delete')->table('character')->where('CharacterID', (int)$character->CharacterID)->execute();
			// GODZOOKS THIS ISN'T ESCAPED they cry. It runs off the value from the database not the one people pass in, so it isn't quite as painfully hideous... just slightly lethal.
			$this->pixie->db->get()->execute('DELETE FROM `character` WHERE `character`.`CharacterID` = '.((int)$character->CharacterID).';');
			$this->pixie->db->get()->execute("COMMIT");
			
			$this->view->json = array('Result' => 'OK');
		}
		else
		{
			$this->view->json = array('Result' => 'ERROR', 'Message' => 'You can\'t delete a character you don\'t control!');
		}
	}
	
	public function action_profile()
	{
		$this->view->subview = 'game/character';
		$this->view->character = $this->pixie->orm->get('Character')->where('CharacterID', $this->request->param('CharacterID'))->find();
		if(!$this->view->character->loaded())
		{
			$this->execute = false;
			$this->response->body = 'Character doesn\'t exist!';
			return false;
		}
		$this->view->isowner = $this->pixie->auth->user() && $this->view->character->AccountID == $this->pixie->auth->user()->AccountID;
	}
}