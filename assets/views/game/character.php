<div class="padding-10">
<h2 class="ui-corner-top ui-widget-header"><?php echo $character->CharName; ?></h2>
<div class="ui-corner-bottom ui-widget-content padding-10">

	<?php if($isowner) echo '<p class="ui-state-highlight ui-corner-all padding-10">This is your character.</p>' ?>

	<?php if($character->HitPoints <= 0) echo '<p class="ui-state-error ui-corner-all padding-10">'.$character->CharName.' is currently dead :(</p>' ?>

	<?php echo '<p>Level '.$character->Level.'</p>' ?>
	
	<?php
		
		foreach($skills as $skill)
		{
		
			echo '<div class="ui-widget-content ui-corner-all margin-10 padding-10" style="display:inline-block">'.$skill->SkillName.'</div>';
		}
		
	?>
	<div style="display:block"></div>
	
	<?php if($isowner) $_link('/game/'.$character->CharacterID.'/skills', 'Learn Skills', 'button clear-left'); ?>
	
</div>
</div>