<?php 
namespace App\Controller;
class Mapeditor extends \App\Page{

	public function before() {
	
		if(!$this->pixie->auth->user() || !$this->pixie->auth->has_role('Admin')) // Ensure adminage
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
	
	public function after()
	{
		if(!$this->request->get('ajax'))
		{
			$this->action_index();
		}
		parent::after();
	}

	public function action_index()
	{
		if($this->request->get('L'))
		{
			$loc = $this->pixie->orm->get('Location')->where('LocationID', $this->request->get('L'))->find();
			$this->view->offsetX = $loc->CoordinateX;
			$this->view->offsetY = $loc->CoordinateY;
		}
		else
		{
			$this->view->offsetX = $this->request->get('X') ? $this->request->get('X') : 0;
			$this->view->offsetY = $this->request->get('Y') ? $this->request->get('Y') : 0;
		}
		$this->view->map = $this->pixie->orm->get('Location')->where('CoordinateX', '>', $this->view->offsetX - 3)->where('CoordinateX', '<', $this->view->offsetX + 3)->where('CoordinateY', '>', $this->view->offsetY - 3)->where('CoordinateY', '<', $this->view->offsetY + 3)->where('PlaneID', '=', 1)->where('CoordinateZ', 0)->order_by('CoordinateY','asc')->order_by('CoordinateX','asc')->find_all()->as_array();
		$this->view->tiletype = $this->pixie->orm->get('TileType')->order_by('TypeName', 'ASC')->find_all()->as_array();
		$this->view->subview = 'mapedit/main';
	}
	
	public function action_move()
	{
		$this->view->subview = 'mapedit/map';
		if($this->request->get('L'))
		{
			$loc = $this->pixie->orm->get('Location')->where('LocationID', $this->request->get('L'))->find();
			$this->view->offsetX = $loc->CoordinateX;
			$this->view->offsetY = $loc->CoordinateY;
		}
		else
		{
			$this->view->offsetX = $this->request->get('X') ? $this->request->get('X') : 0;
			$this->view->offsetY = $this->request->get('Y') ? $this->request->get('Y') : 0;
		}
		$this->view->map = $this->pixie->orm->get('Location')->where('CoordinateX', '>', $this->view->offsetX - 3)->where('CoordinateX', '<', $this->view->offsetX + 3)->where('CoordinateY', '>', $this->view->offsetY - 3)->where('CoordinateY', '<', $this->view->offsetY + 3)->where('PlaneID', '=', 1)->where('CoordinateZ', 0)->order_by('CoordinateY','asc')->order_by('CoordinateX','asc')->find_all()->as_array();
	}
	
		
	public function action_edit()
	{
		$this->view->subview = 'mapedit/detail';
		$this->view->tiletype = $this->pixie->orm->get('TileType')->order_by('TypeName', 'ASC')->find_all()->as_array();
		if($this->request->get('L'))
		{
			$this->view->selected = $this->pixie->orm->get('Location')->where('LocationID', $this->request->get('L'))->find();
		}
		else
		{
			$loc = $this->pixie->orm->get('Location');
			$loc->CoordinateX = $this->request->get('X') ? $this->request->get('X') : 0;
			$loc->CoordinateY = $this->request->get('Y') ? $this->request->get('Y') : 0;
			$loc->CoordinateZ = 0;
			$loc->PlaneID = 1;
			$this->view->selected = $loc;
		}
	}
	
	public function action_save()
	{
		if($this->request->post('LocationID'))
		{
			$loc = $this->pixie->orm->get('Location')->where('LocationID', $this->request->post('LocationID'))->find();
		}
		else
		{
			$loc = $this->pixie->orm->get('Location');
			$loc->CoordinateX = $this->request->post('CoordinateX');
			$loc->CoordinateY = $this->request->post('CoordinateY');
			$loc->CoordinateZ = $this->request->post('CoordinateZ');
			$loc->PlaneID = $this->request->post('PlaneID');
		}
		$loc->LocationName = $this->request->post('LocationName');
		$loc->TileTypeID = $this->request->post('TileTypeID');
		if($this->request->post('UseDefaultDesc')) $loc->Description = null; else $loc->Description = $this->request->post('Description');
		$loc->save();
		if($this->request->get('ajax'))
		{
			$this->view->subview = 'mapedit/detail';
			$this->view->selected = $loc;
			$this->view->offsetX = $this->request->post('CoordinateX');
			$this->view->offsetY = $this->request->post('CoordinateY');
			$this->view->tiletype = $this->pixie->orm->get('TileType')->order_by('TypeName', 'ASC')->find_all()->as_array();
		}
	}
	
	
}