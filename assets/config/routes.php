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
	// Used by the game controller to know if someone specifically wants to respawn
	'respawn' => array('/game/<CharacterID>/respawn', array(
					'controller' => 'Game',
					'action' => 'respawn'
					)
				),
	// Used by the game controller by non index actions
	'game' => array('/game/<CharacterID>/<action>(/<arg1>)', array(
					'controller' => 'Game',
					'action' => 'index'
					)
				),
	// Used by the game controller to know if someone isn't doing anything
	'gameindex' => array('/game/<CharacterID>', array(
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
