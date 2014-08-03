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
				$this->view->json = array('Result' => 'OK', 'TotalRecordCount' => $this->pixie->orm->get('TileType')->count_all(), 'Records' => $tiletype->order_by('TypeName', 'ASC')->find_all()->as_array(true));
				break;
			}
			case 'create':
			{
				$tiletype->TypeName = $post['TypeName'];
				$tiletype->Colour = $post['Colour'];
				$tiletype->TileIcon = $post['TileIcon'];
				$tiletype->DefaultDescription = $post['DefaultDescription'];
				$tiletype->Traversible = $post['Traversible'];
				$tiletype->APCost = $post['APCost'];
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
					$tiletype->Traversible = $post['Traversible'];
					$tiletype->APCost = $post['APCost'];
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
				
				// Items
				$attr1 = $this->pixie->db->query('select')->table('item_usage_attribute')->fields('usage.UsageName', 'item_usage_attribute.AttributeName', 'item_usage_attribute.AttributeType', 'item_usage_attribute.AttributeValue')->join('usage', array('item_usage_attribute.ItemUsageID', 'usage.UsageID'))->where('usage.UsageName', 'customattribute')->group_by('item_usage_attribute.AttributeName')->execute()->as_array();
				// Unique Items
				$attr2 = $this->pixie->db->query('select')->table('special_item_attribute')->fields('usage.UsageName', 'special_item_attribute.AttributeName', 'special_item_attribute.AttributeType', 'special_item_attribute.AttributeValue')->join('usage', array('special_item_attribute.ItemUsageID', 'usage.UsageID'))->where('usage.UsageName', 'customattribute')->group_by('special_item_attribute.AttributeName')->execute()->as_array();
				// Skills
				$attr3 = $this->pixie->db->query('select')->table('skill_effect')->fields('usage.UsageName', 'skill_effect.AttributeName', 'skill_effect.AttributeType', 'skill_effect.AttributeValue')->join('usage', array('skill_effect.SkillUsageID', 'usage.UsageID'), 'inner')->where('usage.UsageName', 'customattribute')->group_by('skill_effect.AttributeName')->execute()->as_array();
				$attrs = array_merge($attr1, $attr2, $attr3);
				$tags = array();
			
			
				$tags['Always'] = 'Always';
				$tags['Never'] = 'Never';
			
				foreach($attrs as $attr)
				{
					$tags['WithAttribute '.$attr->AttributeName] = $attr->AttributeName.' Required';
					$tags['WithoutAttribute '.$attr->AttributeName] = $attr->AttributeName.' Forbidden';
				}
			
				$this->view->traversaltypes = json_encode($tags);
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
				$this->view->json = array('Result' => 'OK', 'TotalRecordCount' => $this->pixie->orm->get('ItemCategory')->count_all(), 'Records' => $itemcategory->order_by('CategoryName', 'ASC')->find_all()->as_array(true));
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
				$this->view->json = array('Result' => 'OK', 'TotalRecordCount' => $this->pixie->orm->get('ItemType')->count_all(), 'Records' => $itemtype->order_by('ItemTypeName', 'ASC')->find_all()->as_array(true));
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
	
	public function action_itemusage()
	{
		$action = $this->request->get('action');
		$post = array_merge($this->request->get(), $this->request->post());
		
		switch($action)
		{
			case 'list':
			{
				$this->view = $this->pixie->view('json');
				$usages = $this->pixie->db->query('select')->table('item_type_usage')->join('usage', array('item_type_usage.ItemUsageID', 'usage.UsageID'))->where('ItemTypeID',$post['ItemTypeID'])->execute()->as_array();
				$this->view->json = array('Result' => 'OK', 'TotalRecordCount' => count($usages), 'Records' => $usages);
				break;
			}
			case 'create':
			{
				$usageitem = array();
				$usageitem['ItemUsageID'] = $post['ItemUsageID'];
				$usageitem['ItemTypeID'] = $post['ItemTypeID'];
				
				$usage = $this->pixie->orm->get('ItemUsage')->where('UsageID', $post['ItemUsageID'])->find();
				
				
				if($usage->UsageName == 'weapon') $this->pixie->db->query('insert')->table('item_usage_attribute')->data(array(array('ItemUsageID' => $usage->UsageID, 'ItemTypeID' => $usageitem['ItemTypeID'], 'AttributeName' => 'Damage', 'AttributeValue' => 0), array('ItemUsageID' => $usage->UsageID, 'ItemTypeID' => $usageitem['ItemTypeID'], 'AttributeName' => 'DamageType', 'AttributeValue' => 'INVALID'), array('ItemUsageID' => $usage->UsageID, 'ItemTypeID' => $usageitem['ItemTypeID'], 'AttributeName' => 'HitChance', 'AttributeValue' => 0)))->execute();
				
				$this->pixie->db->query('insert')->table('item_type_usage')->data($usageitem)->execute();
				
				$usageitem['UsageName'] = $usage->UsageName;
				
				$this->view = $this->pixie->view('json');
				$this->view->json = array('Result' => 'OK', 'Record' => $usageitem);
				
				break;
			}
			case 'delete':
			{
				$this->view = $this->pixie->view('json');
				$usageitem['ItemUsageID'] = $post['ItemUsageID'];
				$usageitem['ItemTypeID'] = $post['ItemTypeID'];
				$this->pixie->db->query('delete')->table('item_usage_attribute')->where('ItemTypeID', $usageitem['ItemTypeID'])->where('ItemUsageID', $usageitem['ItemUsageID'])->execute();
				$this->pixie->db->query('delete')->table('special_item_attribute')->join('item_instance', array('item_instance.ItemInstanceID', 'special_item_attribute.ItemInstanceID'), 'inner')->where('ItemTypeID', $usageitem['ItemTypeID'])->where('ItemUsageID', $usageitem['ItemUsageID'])->execute();
				$this->pixie->db->query('delete')->table('item_type_usage')->where('ItemTypeID', $usageitem['ItemTypeID'])->where('ItemUsageID', $usageitem['ItemUsageID'])->execute();
				$this->view->json = array('Result' => 'OK');
				break;
			}
			default:
			{
				$this->view->subview = 'admin/itemtype';
			}
		}
	}
	
	
	public function action_options()
	{
		$action = $this->request->get('action');
		$post = array_merge($this->request->get(), $this->request->post());
		if(isset($post['ItemUsageID'])) $usage = $post['ItemUsageID'];
		if(isset($post['SkillUsageID'])) $usage = $post['SkillUsageID'];
		switch($action)
		{
			case 'categories':
			{
				$array = array();
				$cats = $this->pixie->orm->get('ItemCategory')->order_by('CategoryName', 'ASC')->find_all()->as_array(true);
				foreach($cats as $cat)
				{
					$array[] = array('Value' => $cat->ItemCategoryID, 'DisplayText' => $cat->CategoryName);
				}
				$this->view = $this->pixie->view('json');
				$this->view->json = array('Result' => 'OK', 'Options' => $array);
				break;
			}
			case 'attributes':
			{
				$array = array();
				$usages = array();
				$usage = $this->pixie->orm->get('ItemUsage')->where('UsageID', $usage)->find();
				
				if($usage->UsageName == 'weapon')
				{
					$usages = array('Damage' => 'Damage', 'DamageType' => 'Damage Type', 'HitChance' => 'Hit Chance %', 'Tag' => 'Tag');
				}
				else if($usage->UsageName == 'armour')
				{
					$usages = array('Soak' => 'Soak Value', 'Resist' => 'Resist %');
				}
				else if($usage->UsageName == 'weaponbuff')
				{
					$usages = array('Damage' => 'Damage', 'DamageType' => 'Damage Type', 'HitChance' => 'Hit Chance %');
				}
				else if($usage->UsageName == 'activated')
				{
					$usages = array('APCost' => 'AP Cost', 'SetSelf' => 'Set Self Property', 'AlterSelf' => 'Alter Self Property', 'AlterAdjacent' => 'Alter Adjacent Players Property', 'SetAdjacent' => 'Set Adjacent Players Property', 'MessageCurrentTile' => 'Message Players On Tile');
				}
				foreach($usages as $use => $desc)
				{
					$array[] = array('Value' => $use, 'DisplayText' => $desc);
				}
				$this->view = $this->pixie->view('json');
				$this->view->json = array('Result' => 'OK', 'Options' => $array);
				break;
			}
			case 'types':
			{
				$array = array();
				$usage = $this->pixie->orm->get('ItemUsage')->where('UsageID', $usage)->find();
				if($usage->UsageName == 'armour')
				{
					$usages = $this->pixie->db->query('select')->table('item_usage_attribute')->fields($this->pixie->db->expr('DISTINCT `AttributeValue`'))->where('AttributeName', 'DamageType')->order_by('AttributeName', 'ASC')->execute()->as_array();
					foreach($usages as $use)
					{
						$array[] = array('Value' => $use->AttributeValue, 'DisplayText' => $use->AttributeValue);
					}
				}
				else if($usage->UsageName == 'weaponbuff')
				{
					$usages = $this->pixie->db->query('select')->table('item_usage_attribute')->fields($this->pixie->db->expr('DISTINCT `AttributeValue`'))->where('AttributeName', 'Tag')->order_by('AttributeName', 'ASC')->execute()->as_array();
					foreach($usages as $use)
					{
						$array[] = array('Value' => $use->AttributeValue, 'DisplayText' => $use->AttributeValue);
					}
				}
				$this->view = $this->pixie->view('json');
				$this->view->json = array('Result' => 'OK', 'Options' => $array);
				break;
			}
			case 'usages':
			{
				$array = array();
				$usages = $this->pixie->orm->get('ItemUsage')->order_by('UsageName', 'ASC')->find_all()->as_array(true);
				foreach($usages as $use)
				{
					$array[] = array('Value' => $use->UsageID, 'DisplayText' => $use->UsageName);
				}
				$this->view = $this->pixie->view('json');
				$this->view->json = array('Result' => 'OK', 'Options' => $array);
				break;
			}
			default:
			{
				break;
			}
		}
	}
	
	
	
	public function action_itemusageattribute()
	{
		$action = $this->request->get('action');
		$post = array_merge($this->request->get(), $this->request->post());
		
		switch($action)
		{
			case 'list':
			{
				$this->view = $this->pixie->view('json');
				$usages = $this->pixie->db->query('select')->table('item_usage_attribute')->where('ItemTypeID',$post['ItemTypeID'])->where('ItemUsageID',$post['ItemUsageID'])->order_by('AttributeName', 'ASC')->execute()->as_array();
				$this->view->json = array('Result' => 'OK', 'TotalRecordCount' => count($usages), 'Records' => $usages);
				break;
			}
			case 'update':
			{
				$this->view = $this->pixie->view('json');

				
				$attribute = array();
				$attribute['ItemUsageAttributeID'] = $post['ItemUsageAttributeID'];
				$attribute['ItemUsageID'] = $post['ItemUsageID'];
				$attribute['ItemTypeID'] = $post['ItemTypeID'];
				$attribute['AttributeName'] = $post['AttributeName'];
				if(isset($post['AttributeType'])) $attribute['AttributeType'] = $post['AttributeType'];
				$attribute['AttributeValue'] = $post['AttributeValue'];
				$this->pixie->db->query('update')->table('item_usage_attribute')->data($attribute)->where('ItemUsageAttributeID', $attribute['ItemUsageAttributeID'])->execute();
				
				$this->view->json = array('Result' => 'OK', 'Record' => $attribute);

				break;
			}	
			case 'create':
			{
				$attribute = array();
				$attribute['ItemUsageID'] = $post['ItemUsageID'];
				$attribute['ItemTypeID'] = $post['ItemTypeID'];
				$attribute['AttributeName'] = $post['AttributeName'];
				if(isset($post['AttributeType'])) $attribute['AttributeType'] = $post['AttributeType'];
				$attribute['AttributeValue'] = $post['AttributeValue'];
				
				$this->pixie->db->query('insert')->table('item_usage_attribute')->data($attribute)->execute();
				
				$this->view = $this->pixie->view('json');
				$this->view->json = array('Result' => 'OK', 'Record' => $attribute);
				
				break;
			}
			case 'delete':
			{
				$this->view = $this->pixie->view('json');
				$this->pixie->db->query('delete')->table('item_usage_attribute')->where('ItemUsageAttributeID', $post['ItemUsageAttributeID'])->execute();
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
				$cats = $this->pixie->orm->get('ItemType')->order_by('ItemTypeName', 'ASC')->find_all()->as_array(true);
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
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	public function action_skill()
	{
		$action = $this->request->get('action');
		$skill = $this->pixie->orm->get('Skill');
		$post = $this->request->post();
		
		switch($action)
		{
			case 'list':
			{
				$this->view = $this->pixie->view('json');
				$this->view->json = array('Result' => 'OK', 'TotalRecordCount' => $skill->count_all(), 'Records' => $skill->order_by('SkillName', 'ASC')->find_all()->as_array(true));
				break;
			}
			case 'create':
			{
				$skill->SkillName = $post['SkillName'];
				$skill->SkillBaseCost = $post['SkillBaseCost'];
				$skill->Icon = $post['Icon'];
				$skill->save();
				
				$this->view = $this->pixie->view('json');
				$this->view->json = array('Result' => 'OK', 'Record' => $skill->as_array(true));
				
				break;
			}
			case 'update':
			{
				$this->view = $this->pixie->view('json');
				$skill = $skill->where('SkillID', $post['SkillID'])->find();
				
				if($skill->loaded())
				{
					$skill->SkillID = $post['SkillID'];
					$skill->SkillName = $post['SkillName'];
					$skill->SkillBaseCost = $post['SkillBaseCost'];
					$skill->Icon = $post['Icon'];
					$skill->save();
					
					$this->view->json = array('Result' => 'OK', 'Record' => $skill->as_array(true));
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
				$skill->where('SkillID', $post['SkillID'])->delete_all();
				$this->view->json = array('Result' => 'OK');
				break;
			}
			default:
			{
				$this->view->subview = 'admin/skill';
			}
		}
	}
	
	public function action_skillusage()
	{
		$action = $this->request->get('action');
		$post = array_merge($this->request->get(), $this->request->post());
		
		switch($action)
		{
			case 'list':
			{
				$this->view = $this->pixie->view('json');
				$usages = $this->pixie->db->query('select')->table('skill_usage')->join('usage', array('skill_usage.SkillUsageID', 'usage.UsageID'))->where('SkillID',$post['SkillID'])->execute()->as_array();
				$this->view->json = array('Result' => 'OK', 'TotalRecordCount' => count($usages), 'Records' => $usages);
				break;
			}
			case 'create':
			{
				$skillusage = array();
				$skillusage['SkillUsageID'] = $post['SkillUsageID'];
				$skillusage['SkillID'] = $post['SkillID'];
				
				$usage = $this->pixie->orm->get('Usage')->where('UsageID', $post['SkillUsageID'])->find();
				
				
				
				if($usage->UsageName == 'weapon') $this->pixie->db->query('insert')->table('skill_effect')->data(array(array('SkillUsageID' => $usage->UsageID, 'SkillID' => $skillusage['SkillID'], 'AttributeName' => 'Damage', 'AttributeValue' => 0), array('SkillUsageID' => $usage->UsageID, 'SkillID' => $skillusage['SkillID'], 'AttributeName' => 'DamageType', 'AttributeValue' => 'INVALID'), array('SkillUsageID' => $usage->UsageID, 'SkillID' => $skillusage['SkillID'], 'AttributeName' => 'HitChance', 'AttributeValue' => 0)))->execute();
				
				$this->pixie->db->query('insert')->table('skill_usage')->data($skillusage)->execute();
				$skillusage['UsageName'] = $usage->UsageName;
				$this->view = $this->pixie->view('json');
				$this->view->json = array('Result' => 'OK', 'Record' => $skillusage);
				
				break;
			}
			case 'delete':
			{
				$this->view = $this->pixie->view('json');
				$skillusage['SkillUsageID'] = $post['SkillUsageID'];
				$skillusage['SkillID'] = $post['SkillID'];
				$this->pixie->db->query('delete')->table('skill_effect')->where('SkillID', $skillusage['SkillID'])->where('SkillUsageID', $skillusage['SkillUsageID'])->execute();
				$this->pixie->db->query('delete')->table('skill_usage')->where('SkillID', $skillusage['SkillID'])->where('SkillUsageID', $skillusage['SkillUsageID'])->execute();
				$this->view->json = array('Result' => 'OK');
				break;
			}
			default:
			{
				$this->view->subview = 'admin/itemtype';
			}
		}
	}
	
	
	
	public function action_skilleffect()
	{
		$action = $this->request->get('action');
		$post = array_merge($this->request->get(), $this->request->post());
		
		switch($action)
		{
			case 'list':
			{
				$this->view = $this->pixie->view('json');
				$usages = $this->pixie->db->query('select')->table('skill_effect')->where('SkillID',$post['SkillID'])->where('SkillUsageID',$post['SkillUsageID'])->order_by('AttributeName', 'ASC')->execute()->as_array();
				$this->view->json = array('Result' => 'OK', 'TotalRecordCount' => count($usages), 'Records' => $usages);
				break;
			}
			case 'update':
			{
				$this->view = $this->pixie->view('json');

				
				$attribute = array();
				$attribute['SkillEffectID'] = $post['SkillEffectID'];
				$attribute['SkillUsageID'] = $post['SkillUsageID'];
				$attribute['SkillID'] = $post['SkillID'];
				$attribute['AttributeName'] = $post['AttributeName'];
				if(isset($post['AttributeType'])) $attribute['AttributeType'] = $post['AttributeType'];
				$attribute['AttributeValue'] = $post['AttributeValue'];
				$this->pixie->db->query('update')->table('skill_effect')->data($attribute)->where('SkillEffectID', $attribute['SkillEffectID'])->execute();
				
				$this->view->json = array('Result' => 'OK', 'Record' => $attribute);

				break;
			}	
			case 'create':
			{
				$attribute = array();
				$attribute['SkillUsageID'] = $post['SkillUsageID'];
				$attribute['SkillID'] = $post['SkillID'];
				$attribute['AttributeName'] = $post['AttributeName'];
				if(isset($post['AttributeType'])) $attribute['AttributeType'] = $post['AttributeType'];
				$attribute['AttributeValue'] = $post['AttributeValue'];
				
				$this->pixie->db->query('insert')->table('skill_effect')->data($attribute)->execute();
				
				$this->view = $this->pixie->view('json');
				$this->view->json = array('Result' => 'OK', 'Record' => $attribute);
				
				break;
			}
			case 'delete':
			{
				$this->view = $this->pixie->view('json');
				$this->pixie->db->query('delete')->table('skill_effect')->where('SkillEffectID', $post['SkillEffectID'])->execute();
				$this->view->json = array('Result' => 'OK');
				break;
			}
			default:
			{
				$this->view->subview = 'admin/skill';
			}
		}
	}
	
	
	
}