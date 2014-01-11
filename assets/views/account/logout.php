<p>You have been logged out!</p>
<p id="message_localstorage_auth">This browser will no longer be automatically logged in on future visits.</p>
 <script type="text/javascript">
	if(hasLocalStorage)
	{
		localStorage['ReauthenticationToken'] = null;
		$('#message_localstorage_auth').show();
	}
 </script>