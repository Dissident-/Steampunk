<?php
namespace App\View;
 
class Helper extends \PHPixie\View\Helper{
 
    protected $aliases = array(
    '_'  => 'output',
    '_link' => 'link',
	'_input' => 'input',
	'_inputline' => 'inputline',
	'_form' => 'form',
	'_submit' => 'submit',
	'_validate' => 'validate'
    );
	
    public function link($url, $text, $classes = '', $dest = '#page_content') {
		echo '<a class="'.$classes.'" href="'.$url.'" rel="'.$dest.'">';
		echo $this->output($text);
		echo '</a>';
    }
	
	public function input($name, $text) {
		echo '<input name="'.$name.'" id="input_'.$name.'" value="';
		echo $this->output($text);
		echo '" />';
    }
	
	public function submit($name = 'Submit', $class = "button") {
		echo '<input type="submit" value="'.$name.'" class="'.$class.'"/>';
    }
	
	public function inputline($name, $text = '', $label = null, $type = "text") {
		echo '<p style="width:90%;margin-left:1%"><label style="min-width:10%;display:inline-block" for="input_'.$name.'">';
		echo $this->output($label === null ? $name.': ' : $label);
		echo '</label><input style="width:70%" type="'.$type.'" name="'.$name.'" id="input_'.$name.'" value="';
		echo $this->output($text);
		echo '" /></p>';
    }
	
	public function validate($errors)
	{
		if(is_array($errors))
		{
			echo '<div class="margin-10 padding-10 ui-corner-all ui-state-error">';
			foreach($errors as $k => $v)
			{
				if(is_array($v))
				{
					foreach($v as $kk => $vv)
					{
						echo '<p>'.$vv.'</p>';
					}
				}
				else
				{
					echo '<p>'.$v.'</p>';
				}
			}
			echo '</div>';
		}
	}
	
	public function form($url = null, $id = null, $return_target = '#page_content')
	{
		if($url == null)
			echo '</form>';
		else
			echo '<form action="'.$url.'" id="'.$id.'" method="POST"><input class="return_target" name="return_target" type="hidden" value="'.$return_target.'"/>';
	}
}