<div><div class="ui-corner-left ui-widget-content padding-10" style="float:left"><?php echo $character->CharName; ?></div><div style="float:left" class="ui-state-error padding-10"><?php echo $character->HitPoints ?>HP</div><div style="float:left" class="ui-state-highlight padding-10"><?php echo $character->ActionPoints ?>AP</div><div style="float:left" class="ui-corner-right ui-widget-content padding-10"><?php echo $character->Experience ?>XP</div>
<?php
	// Yellow and red messages that slide in right of character details
	echo '<div id="game_minor_actions" style="float:left;margin-left:10px" class="padding-10 ui-state-highlight ui-corner-all'.( $action == '' ? ' ui-helper-hidden' : '' ).'">';
	if($action != '') echo $action;
	echo '</div><div id="game_warnings" style="float:left;margin-left:10px" class="padding-10 ui-state-error ui-corner-all'.( $warnings == '' ? ' ui-helper-hidden' : '' ).'">';
	if($warnings != '') echo $warnings;
	echo '</div>';
	
?><div class="clear-left"></div>
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
			<p>You are dead.</p>
			<?php $_link('/game/'.$character->CharacterID.'/respawn', 'Respawn', 'button', '#page_content nohash' ); ?>
		</div>
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
	 <script type="text/javascript">
		$( "#inventory_accordion" ).accordion({heightStyle: "content"});
		$(function() {
			$( "#right_panel" ).tabs({ active: <?php if( $right == 'inventory') echo '1'; else echo '0';?>});
		});
	</script>
	</div>
</div>