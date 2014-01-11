<div id="reauth_reload" class="ui-helper-hidden" title="Logging In...">
	<p>You have been automatically logged in, please wait while the page refreshes...</p>
</div>
<script type="text/javascript">
	localStorage['ReauthenticationToken'] = '<?php echo $token; ?>';
	$( "#reauth_reload" ).dialog({
		autoOpen: true,
		height: 100,
		width: 350,
		modal: true
	});
	//location.reload();
 </script>
