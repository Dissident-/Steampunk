<script type="text/javascript">
	$('#game_warnings').hide();
	$('#game_minor_actions').hide();
	$('#game_warnings').html('');
	$('#game_minor_actions').html('');
	<?php if($action != '') { ?>
	$('#game_minor_actions').html("<?php echo htmlspecialchars($action); ?>");
	$('#game_minor_actions').show();
	<?php }
	if($warnings != '') { ?>
	$('#game_warnings').html("<?php echo htmlspecialchars($warnings); ?>");
	$('#game_warnings').show();
	<?php }
	if($subview != '') require_once $subview.'.php' ?>
</script>