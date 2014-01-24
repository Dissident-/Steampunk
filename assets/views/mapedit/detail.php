
	<h3 class="ui-corner-top ui-widget-header">Details</h3>
	<div class="ui-corner-bottom ui-widget-content padding-10">
		<?php if(isset($selected)) { 
		
		$_form('/mapeditor/save', 'mapedittile', '#edit_detail');
		
		if($selected->loaded()) $_input('LocationID', $selected->LocationID, 'hidden');
		
		$_input('CoordinateX', $selected->CoordinateX, 'hidden');
		$_input('CoordinateY', $selected->CoordinateY, 'hidden');
		$_input('CoordinateZ', $selected->CoordinateZ, 'hidden');
		$_input('PlaneID', $selected->PlaneID, 'hidden');
		
		?>
		
		<p>Tile Type: <select name="TileTypeID">
			<?php
			
			foreach($tiletype as $type)
			{
				echo '<option'.($selected->loaded() && $type->TileTypeID == $selected->TileTypeID ? ' selected="selected"' : '').' value="'.$type->TileTypeID.'">'.$type->TypeName.'</option>';
			}
			
			?>
		</select></p>
		<p>Location Name: <input type="text" name="LocationName" maxlength="45" value="<?php if($selected->loaded()) echo $selected->LocationName; ?>"/></p>
		<p>Use default description? <input type="checkbox" name="UseDefaultDesc" value="1" <?php if(!$selected->loaded() || $selected->Description == null) echo 'checked="checked"'; ?> /></p>
		<p>Custom description:<br/>
		<textarea id="Description" name="Description" maxlength="255" style="width:90%;"><?php if($selected->loaded()) echo $selected->Description; ?></textarea></p>
		

		
		<script type="text/javascript">
		<?php if($selected->loaded())
			{
				$selected->Type->find();
				echo '$("#map_'.$selected->CoordinateX.'_'.$selected->CoordinateY.'").attr("style", "background-color:'.$selected->Type->Colour.'");
				$("#map_'.$selected->CoordinateX.'_'.$selected->CoordinateY.' .name").html("'.$selected->LocationName.'");';
			}			
			?>	
		</script>
		
		
		<p>
		<?php

		$_submit('Save');
		echo '</p>';
		$_form();
	
		} else { ?>
		
		<p>Select a tile to start editing.</p>
		
		<?php } ?>
	</div>

