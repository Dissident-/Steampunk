<p>You have been logged in!</p>
<p id="message_localstorage_auth">Until you logout or log in on another browser, this browser will be automatically logged in on each visit.</p>
 <script type="text/javascript">
	if(hasLocalStorage)
	{
		localStorage['ReauthenticationToken'] = '<?php echo $token; ?>';
		$('#message_localstorage_auth').show();
	}
 </script>