<?php
return array(
	'charselect' => array('/character/select', array(
					'controller' => 'Character',
					'action' => 'list'
					)
				),
	'charprofile' => array(array('/character/<CharacterID>', array('CharacterID' => '\d+')), array(
					'controller' => 'Character',
					'action' => 'profile'
					)
				),
	'game' => array('/game/<CharacterID>(/<action>(/<arg1>))', array(
					'controller' => 'Game',
					'action' => 'index'
					)
				),
	'default' => array('(/<controller>(/<action>(/<id>)))', array(
					'controller' => 'Hello',
					'action' => 'index'
					)
				),
);
