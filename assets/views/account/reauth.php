 <script type="text/javascript">
	if(hasLocalStorage && localStorage['ReauthenticationToken'] != null && localStorage['ReauthenticationToken'] != 'null')
	{
			$.ajax({
				type:'POST',
				url: '/auth/reauthenticate?ajax=1&target=#dynamicjs',
				data: { ReauthenticationToken: localStorage['ReauthenticationToken']},
				success: function(data, status, xhr){
						$('#dynamicjs').html(data);
				},
				timeout: '300000'
			});
	}
 </script>