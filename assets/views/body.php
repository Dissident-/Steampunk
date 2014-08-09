	<div class="margin-10 padding-10" style="bottom:0px">
	
		<h1 class="ui-corner-top ui-widget-header">
			Project Steampunk
		</h1>
		<div class="ui-widget-content padding-10" id="quicklinks">
			<?php
				require 'quicklinks.php';
			 ?>
		</div>
		
		
		<?php
		if(!$logged_in)
		{
				require 'account/reauth.php';
		}
		?>
		
		<div class="ui-corner-bottom ui-widget-content padding-10" style="bottom:0px;" id="page_content">
			<?php 
				if(isset($subview) && $subview != '') require($subview.'.php');
			?>
		</div>
	</div>
	
	
	<div id="popup_container" title="Notice" class="ui-helper-hidden"></div>
	<div id="dev_tools_container" class="ui-helper-hidden" title="Developer Tools">
	</div>
	<div id="switcher"></div>
	<div id="ajax_error">
	</div>
	<div id="loading_spinner" class="ui-helper-hidden">
		<div class="loading_spinner ui-corner-all ui-widget-overlay">
			<p class="text-centered" style="margin-top:50px"><span class="ui-state-highlight ui-corner-all"><img src="/img/loading.gif" alt="Spinner"/> Please Wait, Loading...</span></p>
		</div>
	</div>
	<div id="dynamicjs" class="ui-helper-hidden">
	</div>