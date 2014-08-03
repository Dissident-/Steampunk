<div><div class="ui-corner-left ui-widget-content padding-10" style="float:left"><?php $_link('/character/'.$character->CharacterID, $character->CharName); ?></div><div style="float:left" class="ui-state-error padding-10"><?php echo $character->HitPoints ?>HP</div><div style="float:left" class="ui-state-highlight padding-10"><?php echo $character->ActionPoints ?>AP</div><div style="float:left" class="ui-widget-content padding-10"><?php echo $character->Experience ?>XP</div><div style="float:left;border-left:none;" class="ui-widget-content padding-10">Lv<?php echo $character->Level ?></div><div style="float:left;border-left:none;" class="ui-corner-right ui-widget-content padding-10"><?php $_link('/game/'.$character->CharacterID.'/skills', $character->SkillPoints.'SP'); ?></div>
<div class="clear-left" style="padding-top:10px;display:block;min-height:39px;"><?php
	// Yellow and red messages that slide in right of character details
	echo '<div id="game_minor_actions" class="padding-10 ui-state-highlight ui-corner-all'.( $action == '' ? ' ui-helper-hidden' : '' ).'">';
	if($action != '') echo $action;
	echo '</div><div id="game_warnings" class="padding-10 ui-state-error ui-corner-all'.( $warnings == '' ? ' ui-helper-hidden' : '' ).'">';
	if($warnings != '') echo $warnings;
	echo '</div>';
?></div><div class="clear-left"></div>
</div>
<?php /* Inline styles, aren't I terribad? */  ?>
<div class="padding-10" style="position:absolute;height:100%;left:10px;right:405px;">
	<h3 class="ui-corner-top ui-widget-header">Details</h3>
	<div class="ui-corner-bottom ui-widget-content">
	
		<div id="activity_log" class="ui-corner-all ui-widget-content margin-10" style="overflow:auto;height:100px">
		
			<ul class="list-plain">
				<?php
				foreach($activitylog as $log)
				{
					echo '<li>'.$log->Timestamp.' '.$log->Activity.'</li>';
				}
				?>
			</ul>
			
		</div>
		<div class="padding-10">
			<?php
			$_form('/game/'.$character->CharacterID.'/speak', 'speechform');
			echo '<input type="text" name="speech" value="" style="margin-right:1%;display:inline;width:80%;" />' ;
			$_submit('Speak');
			$_form();
			?>
		</div>
	
		<p><b><?php echo $character->Location->LocationName.', '.$character->Location->Type->TypeName.' ('.$character->Location->CoordinateX.', '.$character->Location->CoordinateY.', '.$character->Location->Plane->PlaneName.')'; ?></b></p>
		<p><?php echo $character->Location->Description !== null ? $character->Location->Description : $character->Location->Type->DefaultDescription ?></p>
		<?php $others = $character->Location->Character->where('CharacterID', '<>', $character->CharacterID)->order_by('CharName', 'ASC')->find_all()->as_array();
		if(count($others) > 0)
		{ ?>
		<p>You can see <?php if(count($others) == 1) echo '1 other person'; else echo count($others).' other people'; ?> at this location:</p>
		<ul>
		<?php // Sort these alphabetically and provide profile URLs?
			foreach($others as $person)
			{
				echo '<li>'.$person->CharName.'</li>';
			}
		?>
		</ul>
		<?php } ?>
		<?php $_link('/game/'.$character->CharacterID.'/search', 'Search', 'button margin-10', '#page_content nohash' );
		
		if(count($others) > 0 && count($character->Weaponry) > 0)
		{
			echo '<div class="padding-10">';
			$_form('/game/'.$character->CharacterID.'/attack', 'attackform');
			
			$_submit('Attack');
			echo ' <select name="CharacterID" style="display:inline;">';
			foreach($others as $person)
			{
				echo '<option value="'.$person->CharacterID.'"'.(isset($selectedchar) && $person->CharacterID == $selectedchar ? ' selected' : '').'>'.$person->CharName.'</option>';
			}
			echo '</select> with a <select name="ItemInstanceID">';
			foreach($character->Weaponry as $weapon)
			{
				echo '<option class="item-'.$weapon->ItemInstanceID.'" '.(isset($selectedweapon) && $weapon->ItemInstanceID == $selectedweapon ? ' selected' : '').' value="'.$weapon->ItemInstanceID.'">'.$weapon->ItemTypeName.' ('.$weapon->Damage.' '.$weapon->DamageType.' @ '.$weapon->HitChance.'%)</option>';
			}
			echo '</select>';
			$_form();
			echo '</div>';
		} ?>
		
		<div>
			<?php
				
				foreach($activated_skills as $skill)
				{
					$_link('/game/'.$character->CharacterID.'/activate/'.$skill->SkillID, $skill->SkillName, 'button margin-10', 'no_ajax' );
				}
			?>
		</div>
		<div style="display:block"></div>
		
	</div>
</div>

<?php /* OH GOD MORE INLINE STYLES */ ?>
<div class="padding-10" style="position:absolute;width:375px;height:100%;right:10px;margin-top:15px">
	<div id="right_panel">
	<ul>
		<li><a href="#map" rel="no_ajax">Map</a></li>
		<li><a href="#inventory" rel="no_ajax">Inventory</a></li>
	</ul>
	<div id="map" class="ui-corner-bottom ui-widget-content" style="padding:0px">
	
		<?php
		
		$x = -2; // Top left of minimap
		$y = -2;
		$clear = true;
		
		// Iterate over map tiles
		foreach($map as $tile)
		{
			// Loop changing coordinates until they match the selected map tile
			while(!($tile->CoordinateX == $character->Location->CoordinateX + $x && $tile->CoordinateY == $character->Location->CoordinateY + $y) && $y < 3)
			{
				// Black box of doom. Also, INLINE STYLES
				echo '<div class="maptile'.($clear ? ' clear-left' : '').'" style="background-color:black;color:white">'.($x == 0 && $y == 0 ? '<img style="position:absolute;bottom:0px;right:0px" src="/img/person.png"/>' : '').'</div>';
		
				$clear = false;
				$x++;
				if($x >= 3) // Woo, new row
				{
					$x = -2;
					$y++;
					$clear = true;
				}
				if($x >= 3 && $y >= 3) break; // Yeah, lets avoid dem infinite loop possibilities if database is bad
			}
			// Is this the tile ye seek? (may need to add CoordinateZ here sometime later!)
			if($tile->CoordinateX == $character->Location->CoordinateX + $x && $tile->CoordinateY == $character->Location->CoordinateY + $y)
			{
				// Echo the actual tile :D
				echo '<div class="maptile'.($clear ? ' clear-left' : '').'" style="background-color:'.$tile->Type->Colour.'">'.$tile->LocationName.($x == 0 && $y == 0 ? '<img style="position:absolute;bottom:0px;left:0px" src="/img/person.png"/>' : '').($tile->Character->count_all() > ($x == 0 && $y == 0 ? 1 : 0) ? '<img style="position:absolute;bottom:0px;right:0px" src="/img/otherperson.png"/>' : '');
				if($x <= 1 && $x >= -1 && $y <= 1 && $y >= -1 && !($x == 0 && $y == 0))
				{
					// We can move here
					$_link('/game/'.$character->CharacterID.'/move/'.$tile->LocationID, 'Move', 'button move_button', '#page_content nohash' );
				}
				echo '</div>';
				$x++;
				$clear = false;
				if($x >= 3)
				{
					$x = -2;
					$y++;
					$clear = true;
				}
			}
		
		}
		// Fill up rest of map
		while($x < 3 && $y < 3)
		{
			// OH GOD INLINE STYLES WHY
			echo '<div class="maptile'.($clear ? ' clear-left' : '').'" style="background-color:black;color:white">'.($x == 0 && $y == 0 ? '<img style="position:absolute;bottom:0px;right:0px" src="/img/person.png"/>' : '').'</div>';
			$clear = false;
	
			$x++;
			if($x >= 3)
			{
				$x = -2;
				$y++;
				$clear = true;
			}
			if($x == 3 && $y == 3) break;
		}
		?>
		<div class="clear-left"></div>
	</div>
	<div id="inventory">
	
		<div id="inventory_accordion">
		<?php
		
			$cats = array(); // cattergories
		
			foreach($inventory as $item)
			{
				if(!isset($cats[$item->Type->Category->CategoryName])) $cats[$item->Type->Category->CategoryName] = array();
				$cats[$item->Type->Category->CategoryName][] = $item;
			}
		
			ksort($cats); // Don't sort dogs
			$firstinv = true;
			$currentcategoryid = null;
			foreach($cats as $categoryitems)
			{
				foreach($categoryitems as $item)
				{
					if($item->Type->ItemCategoryID != $currentcategoryid)
					{
						$currentcategoryid = $item->Type->ItemCategoryID;
						if(!$firstinv) echo '</ul></div>'; else $firstinv = false;
						echo '<h3>'.$item->Type->Category->CategoryName.'</h3><div style="padding-top:0px;padding-bottom:0px"><ul>';
					}
					echo '<li class="item-'.$item->ItemInstanceID.'">'.$item->Type->ItemTypeName.' <a href="/game/'.$character->CharacterID.'/drop/'.$item->ItemInstanceID.'" rel="#dynamicjs nohash"><img src="/img/turd20.png" alt="Drop" title="Drop" /></a></li>';
				}
			}
		
		?>
		
		</div>
	</div>
	</div>
</div>