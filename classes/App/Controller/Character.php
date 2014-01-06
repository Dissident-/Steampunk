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
		$chars = $this->pixie->orm->get('Character')->with('Location.Plane')->where('AccountID', $this->pixie->auth->user()->AccountID)->find_all()->as_array();
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
		
			$validate = $this->pixie->validate->get($this->request->post());
			$pixie = $this->pixie;
			$validate->field('CharName', true)->rule('callback', function($value) use ($pixie){
				return $pixie->orm->get('Character')->where('CharName', $value)->count_all() === 0 ? false : true;
			})->error('Character Name is already taken.');
			$validate->field('CharName')->rules('filled')->error('Character Name required (duh)');
			
			if($validate->valid())
			{
				$post = $this->request->post();
			
				
				$char = $this->pixie->orm->get('Character');
				$char->CharName = $post['CharName'];
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