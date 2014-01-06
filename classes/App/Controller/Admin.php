<?php 
namespace App\Controller;
class Admin extends \App\Page{

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

	public function action_index()
	{
		echo 42; // the answer
	}
	
	public function action_tiletype()
	{
		$action = $this->request->get('action');
		$tiletype = $this->pixie->orm->get('TileType');
		$post = $this->request->post();
		

		switch($action)
		{
			case 'list':
			{
				$this->view = $this->pixie->view('json'); // Faster to count in PHP probably, but if/when pagination is used we'll have to use a count_all query anyway, and these aren't exactly intensive
				$this->view->json = array('Result' => 'OK', 'TotalRecordCount' => $this->pixie->orm->get('TileType')->count_all(), 'Records' => $tiletype->find_all()->as_array(true));
				break;
			}
			case 'create':
			{
				$tiletype->TypeName = $post['TypeName'];
				$tiletype->Colour = $post['Colour'];
				$tiletype->TileIcon = $post['TileIcon'];
				$tiletype->DefaultDescription = $post['DefaultDescription'];
				$tiletype->save();
				
				$this->view = $this->pixie->view('json');
				$this->view->json = array('Result' => 'OK', 'Record' => $tiletype->as_array(true));
				
				break;
			}
			case 'update':
			{
				$this->view = $this->pixie->view('json');
				$tiletype = $tiletype->where('TileTypeID', $post['TileTypeID'])->find();
				
				if($tiletype->loaded())
				{
					
					$tiletype->TypeName = $post['TypeName'];
					$tiletype->Colour = $post['Colour'];
					$tiletype->TileIcon = $post['TileIcon'];
					$tiletype->DefaultDescription = $post['DefaultDescription'];
					$tiletype->save();
					
					$this->view->json = array('Result' => 'OK', 'Record' => $tiletype->as_array(true));
				}
				else
				{
					$this->view->json = array('Result' => 'ERROR', 'Message' => 'Record not found in database!');
				}
				break;
			}
			case 'delete':
			{
				$this->view = $this->pixie->view('json');
				$tiletype->where('TileTypeID', $post['TileTypeID'])->delete_all();
				$this->view->json = array('Result' => 'OK');
				break;
			}
			default:
			{
				$this->view->subview = 'admin/tiletype';
			}
		}
	}
	
	public function action_itemcategory()
	{
		$action = $this->request->get('action');
		$itemcategory = $this->pixie->orm->get('ItemCategory');
		$post = $this->request->post();
		
		switch($action)
		{
			case 'list':
			{
				$this->view = $this->pixie->view('json');
				$this->view->json = array('Result' => 'OK', 'TotalRecordCount' => $this->pixie->orm->get('ItemCategory')->count_all(), 'Records' => $itemcategory->find_all()->as_array(true));
				break;
			}
			case 'create':
			{
				$itemcategory->CategoryName = $post['CategoryName'];
				$itemcategory->Icon = $post['Icon'];
				$itemcategory->save();
				
				$this->view = $this->pixie->view('json');
				$this->view->json = array('Result' => 'OK', 'Record' => $itemcategory->as_array(true));
				
				break;
			}
			case 'update':
			{
				$this->view = $this->pixie->view('json');
				$itemcategory = $itemcategory->where('ItemCategoryID', $post['ItemCategoryID'])->find();
				
				if($itemcategory->loaded())
				{
					
					$itemcategory->CategoryName = $post['CategoryName'];
					$itemcategory->Icon = $post['Icon'];
					$itemcategory->save();
					
					$this->view->json = array('Result' => 'OK', 'Record' => $itemcategory->as_array(true));
				}
				else
				{
					$this->view->json = array('Result' => 'ERROR', 'Message' => 'Record not found in database!');
				}
				break;
			}
			case 'delete':
			{
				$this->view = $this->pixie->view('json');
				$itemcategory->where('ItemCategoryID', $post['ItemCategoryID'])->delete_all();
				$this->view->json = array('Result' => 'OK');
				break;
			}
			default:
			{
				$this->view->subview = 'admin/itemcategory';
			}
		}
	}
	
	public function action_itemtype()
	{
		$action = $this->request->get('action');
		$itemtype = $this->pixie->orm->get('ItemType');
		$post = $this->request->post();
		
		switch($action)
		{
			case 'list':
			{
				$this->view = $this->pixie->view('json');
				$this->view->json = array('Result' => 'OK', 'TotalRecordCount' => $this->pixie->orm->get('ItemType')->count_all(), 'Records' => $itemtype->find_all()->as_array(true));
				break;
			}
			case 'categories':
			{
				$array = array();
				$cats = $this->pixie->orm->get('ItemCategory')->find_all()->as_array(true);
				foreach($cats as $cat)
				{
					$array[] = array('Value' => $cat->ItemCategoryID, 'DisplayText' => $cat->CategoryName);
				}
				$this->view = $this->pixie->view('json');
				$this->view->json = array('Result' => 'OK', 'Options' => $array);
				break;
			}
			case 'create':
			{
				$itemtype->ItemCategoryID = $post['ItemCategoryID'];
				$itemtype->ItemTypeName = $post['ItemTypeName'];
				$itemtype->BaseWeight = $post['BaseWeight'];
				$itemtype->save();
				
				$this->view = $this->pixie->view('json');
				$this->view->json = array('Result' => 'OK', 'Record' => $itemtype->as_array(true));
				
				break;
			}
			case 'update':
			{
				$this->view = $this->pixie->view('json');
				$itemtype = $itemtype->where('ItemTypeID', $post['ItemTypeID'])->find();
				
				if($itemtype->loaded())
				{
					
					$itemtype->ItemTypeID = $post['ItemTypeID'];
					$itemtype->ItemCategoryID = $post['ItemCategoryID'];
					$itemtype->ItemTypeName = $post['ItemTypeName'];
					$itemtype->BaseWeight = $post['BaseWeight'];
					$itemtype->save();
					
					$this->view->json = array('Result' => 'OK', 'Record' => $itemtype->as_array(true));
				}
				else
				{
					$this->view->json = array('Result' => 'ERROR', 'Message' => 'Record not found in database!');
				}
				break;
			}
			case 'delete':
			{
				$this->view = $this->pixie->view('json');
				$itemtype->where('ItemTypeID', $post['ItemTypeID'])->delete_all();
				$this->view->json = array('Result' => 'OK');
				break;
			}
			default:
			{
				$this->view->subview = 'admin/itemtype';
			}
		}
	}
	
	
	public function action_searchodds()
	{
		$action = $this->request->get('action');
		$post = array_merge($this->request->get(), $this->request->post());
		
		switch($action)
		{
			case 'list':
			{
				$this->view = $this->pixie->view('json');
				$odds = $this->pixie->db->query('select')->table('search_odds')->where('TileTypeID',$post['TileTypeID'])->execute()->as_array();
				$this->view->json = array('Result' => 'OK', 'TotalRecordCount' => count($odds), 'Records' => $odds);
				break;
			}
			case 'items':
			{
				$array = array();
				$cats = $this->pixie->orm->get('ItemType')->find_all()->as_array(true);
				foreach($cats as $cat)
				{
					$array[] = array('Value' => $cat->ItemTypeID, 'DisplayText' => $cat->ItemTypeName);
				}
				$this->view = $this->pixie->view('json');
				$this->view->json = array('Result' => 'OK', 'Options' => $array);
				break;
			}
			case 'create':
			{
				$searchodd = array();
				$searchodd['ItemTypeID'] = $post['ItemTypeID'];
				$searchodd['TileTypeID'] = $post['TileTypeID'];
				$searchodd['ChanceWeight'] = $post['ChanceWeight'];
				
				$this->pixie->db->query('insert')->table('search_odds')->data($searchodd)->execute();
				
				$this->view = $this->pixie->view('json');
				$this->view->json = array('Result' => 'OK', 'Record' => $searchodd);
				
				break;
			}
			case 'update':
			{
				$this->view = $this->pixie->view('json');
				
				$searchodd = array();
				$searchodd['ItemTypeID'] = $post['ItemTypeID'];
				$searchodd['TileTypeID'] = $post['TileTypeID'];
				$searchodd['ChanceWeight'] = $post['ChanceWeight'];
				$this->pixie->db->query('update')->table('search_odds')->data($searchodd)->where('ItemTypeID', $searchodd['ItemTypeID'])->where('TileTypeID', $searchodd['TileTypeID'])->execute();
				$this->view->json = array('Result' => 'OK', 'Record' => $searchodd);
				break;
			}
			case 'delete':
			{
				$this->view = $this->pixie->view('json');
				$searchodd['ItemTypeID'] = $post['ItemTypeID'];
				$searchodd['TileTypeID'] = $post['TileTypeID'];
				$this->pixie->db->query('delete')->table('search_odds')->where('ItemTypeID', $searchodd['ItemTypeID'])->where('TileTypeID', $searchodd['TileTypeID'])->execute();
				$this->view->json = array('Result' => 'OK');
				break;
			}
			default:
			{
				$this->view->subview = 'admin/tiletype';
			}
		}
	}
}