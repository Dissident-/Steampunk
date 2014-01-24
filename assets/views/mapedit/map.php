		<?php
		
		$x = -2; // Top left of minimap
		$y = -2;
		$clear = true;
		
		//$offsetX = 0;
		//$offsetY = 0;
		
		// Iterate over map tiles
		foreach($map as $tile)
		{
			// Loop changing coordinates until they match the selected map tile
			while(!($tile->CoordinateX == $offsetX + $x && $tile->CoordinateY == $offsetY + $y) && $y < 3)
			{
				// Black box of doom. Also, INLINE STYLES
				echo '<div id="map_'.($x + $offsetX).'_'.($y + $offsetY).'" class="maptile'.($clear ? ' clear-left' : '').'" style="background-color:black;color:white"><span class="name"></span>';
				$_link('/mapeditor/edit?X='.($x + $offsetX).'&Y='.($y + $offsetY), 'Edit', 'button move_button', '#edit_detail nohash', 'bottom:40%');
				if($x == -2 || $x == 2 || $y == -2 || $y == 2) $_link('/mapeditor/move?X='.($x + $offsetX).'&Y='.($y + $offsetY), 'Move', 'button move_button', '#map nohash');
				echo '</div>';
		
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
			if($tile->CoordinateX == $offsetX + $x && $tile->CoordinateY == $offsetY + $y)
			{
				// Echo the actual tile :D
				echo '<div id="map_'.($x + $offsetX).'_'.($y + $offsetY).'" class="maptile'.($clear ? ' clear-left' : '').'" style="background-color:'.$tile->Type->Colour.'"><span class="name">'.$tile->LocationName.'</span>';
				$_link('/mapeditor/edit?L='.$tile->LocationID, 'Edit', 'button move_button', '#edit_detail nohash', 'bottom:40%');
				if($x == -2 || $x == 2 || $y == -2 || $y == 2) $_link('/mapeditor/move?L='.$tile->LocationID, 'Move', 'button move_button', '#map nohash');
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
		
		// Fill up map
		while($x < 3 && $y < 3)
		{
			// OH GOD INLINE STYLES WHY
			echo '<div id="map_'.($x + $offsetX).'_'.($y + $offsetY).'" class="maptile'.($clear ? ' clear-left' : '').'" style="background-color:black"><span class="name"></span>';
			
			$_link('/mapeditor/edit?X='.($x + $offsetX).'&Y='.($y + $offsetY), 'Edit', 'button move_button', '#edit_detail', 'bottom:40%');
			if($x == -2 || $x == 2 || $y == -2 || $y == 2) $_link('/mapeditor/move?X='.($x + $offsetX).'&Y='.($y + $offsetY), 'Move', 'button move_button', '#map nohash');
			
			echo '</div>';
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


