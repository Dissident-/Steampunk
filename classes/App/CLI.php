<?php
namespace App;
class CLI {
    public $pixie;
    public function __construct($pixie) {
        $this->pixie = $pixie;
    }
    public function is($name, $type) {
        echo("$name is a $type");
    }
	
	public function tick()
	{
		$this->pixie->db->query('update')->table('character')->data(array('ActionPoints' => $this->pixie->db->expr('ActionPoints + 1')))->where('ActionPoints', '<', 100)->execute();
	}
}