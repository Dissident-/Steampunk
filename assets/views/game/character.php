<div class="padding-10">
<h2 class="ui-corner-top ui-widget-header"><?php echo $character->CharName; ?></h2>
<div class="ui-corner-bottom ui-widget-content padding-10">

	<?php if($isowner) echo '<p class="ui-state-highlight ui-corner-all padding-10">This is your character.</p>' ?>

	<?php if($character->HitPoints <= 0) echo '<p class="ui-state-error ui-corner-all padding-10">'.$character->CharName.' is currently dead :(</p>' ?>

	<?php echo '<p>Level '.$character->Level.'</p>' ?>
	
</div>
</div>