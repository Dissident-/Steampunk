
<?php

foreach($characters as $char)
{
	
	echo '<a href=/game/'.$char->CharacterID.'><div class="div-30 character-select">
	<img src="'.($char->HitPoints > 0 ? '/gameicons/robe.svg' : '/gameicons/tombstone.svg').'" style="width:80%;padding:15% 0% 5% 20%"/>
	<div>
		<p>'.$char->CharName.'</p>
		<p><img src="/gameicons/half-heart.svg"/>'.$char->HitPoints.' HP</p>
		<p><img src="/gameicons/wingfoot.svg"/>'.$char->ActionPoints.' AP</p>
		<p><img src="/gameicons/jigsaw-piece.svg"/>'.$char->Experience.' XP</p>
		<p><img src="/gameicons/open-book.svg"/>Lv '.$char->Level.'</p>
		<p><img src="/gameicons/light-bulb.svg"/>'.$char->SkillPoints.' SP</p>
	</div></div></a>';
	
}

 $_link('/character/create', 'Create Character', 'button clear-left margin-10');
?>