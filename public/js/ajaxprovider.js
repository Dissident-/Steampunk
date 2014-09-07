$.fn.serializeObject = function()
{
    var o = {};
    var a = this.serializeArray();
    $.each(a, function() {
        if (o[this.name] !== undefined) {
            if (!o[this.name].push) {
                o[this.name] = [o[this.name]];
            }
            o[this.name].push(this.value || '');
        } else {
            o[this.name] = this.value || '';
        }
    });
    return o;
};


// A lot of legacy stuff needs cleaning up in here

var CI_INDEX = '';

var temporarilyIgnoreHashChange = false;

var baseURL = window.location.href;

//baseURL = baseURL.replace(':' + location.port, '');

if(window.location.pathname != '/')
	baseURL = baseURL.replace(window.location.pathname, '');
if(baseURL.indexOf('#') >= 0)
{
	baseURL = baseURL.slice(0, baseURL.indexOf('#'));
}
if(baseURL.indexOf('?') >= 0)
{
	baseURL = baseURL.slice(0, baseURL.indexOf('?'));
}

var APPNAME = $('title').html();

// http://diveintohtml5.info/storage.html
function supports_html5_storage() {
  try {
    return 'localStorage' in window && window['localStorage'] !== null;
  } catch (e) {
    return false;
  }
}

var hasLocalStorage = supports_html5_storage();

$( document ).ajaxComplete(function() {
	prettify();
});
function prettify()
{
	$('.button').button();
	$('.button').removeClass('button');
}

//http://stackoverflow.com/questions/280634/endswith-in-javascript
if (typeof String.prototype.endsWith !== 'function') {
    String.prototype.endsWith = function(suffix) {
        return this.indexOf(suffix, this.length - suffix.length) !== -1;
    };
}

// http://stackoverflow.com/a/12502832/798680
jQuery.extend
(
    {
        getValues: function(url) 
        {
            var result = null;
            $.ajax(
                {
                    url: url,
                    type: 'get',
                    dataType: 'html',
                    async: false,
                    /*cache: false,*/
                    success: function(data) 
                    {
                        result = data;
                    }
                }
            );
           return result;
        }
    }
);




if(History.enabled)
	{
		// HTML5 browser, yay!
		History.Adapter.bind(window,'statechange',function() { // Note: We are using statechange instead of popstate
			var State = History.getState();
			targetURL = State.url;
			$.ajax({
				type:'GET',
				url: targetURL,
				data: targetElement == '#page_content' ? {} :{ target: targetElement},
				beforeSend: function(){
					savePanels();
					addMarker(targetElement, targetURL);
					//$(targetElement).hide('fade');
					LoadingSpinner(targetElement, true);
				},
				success: function(data, status, xhr){
					if(checkMarker(targetElement, targetURL))
					{
						$(targetElement).html(data);
						if($('.title').length > 0)
						{
							$('head title').html($('.title').html());
						}
						else
						{
							$('head title').html(APPNAME);
						}
						setAjaxForms();
						setAjaxLinks();
						prettify();
						restorePanels();
					}
				},
				error: function(data, status, xhr){
				
					if(checkMarker(targetElement, targetURL))
					{
						$(targetElement).html(data);
						setAjaxForms();
						LoadingSpinner(targetElement, false);
					}
					
				},
				complete: function(XHR, status){
					if(status == 'timeout')
					{
						alert('The remote web page timed out!');
					}
					$(targetElement).hide();
					LoadingSpinner(targetElement, false);
					$(targetElement).show(); //$(targetElement).show('fade');
					if(targetElement == '#page_content') $("html, body").animate({ scrollTop: 0 }, "slow");
				},
				timeout: '300000'
			});
			
		});
	
	}
	else
	{
	
	
	
	
		$(window).bind('hashchange', function()
		{
			savePanels();
			if(temporarilyIgnoreHashChange)
			{
				temporarilyIgnoreHashChange = false
				return;
			}
			if(location.hash != '' && location.hash != '#')
			{
				if(location.hash.indexOf('->') > -1)
				{
					hashData = location.hash.slice(1).split('->');
					targetElement = hashData[0];
					targetURL = hashData[1];
				}
				else
				{
					targetElement = '#page_content';
					targetURL = location.hash.slice(1);
				}
				
				if(targetURL.slice(0, 1) != '/' && !baseURL.endsWith('/'))
				{
					targetURL = '/' + targetURL;
				}
				else if(targetURL.slice(0, 1) == '/' && baseURL.endsWith('/'))
				{
					targetURL = targetURL.substring(1);
				}
				targetURL = baseURL + targetURL;
				
				$.ajax({
					type:'GET',
					url: targetURL,
					data: targetElement == '#page_content' ? {} : { target: targetElement},
					beforeSend: function(){
						addMarker(targetElement, targetURL);
						//$(targetElement).hide('fade');
						LoadingSpinner(targetElement, true);
					},
					success: function(data, status, xhr){
						//if(checkMarker(targetElement, targetURL))
						//{
							$(targetElement).html(data);
							if($('.title').length > 0)
							{
								$('head title').html($('.title').html());
							}
							else
							{
								$('head title').html(APPNAME);
							}
							setAjaxForms();
							setAjaxLinks();
							prettify();
							restorePanels();
							/*if(window.location.pathname.slice(1) == location.hash.slice(1))
							{
								location.hash = '';
							}*/

						//}
					},
					error: function(data, status, xhr){
					
						if(checkMarker(targetElement, targetURL))
						{
							$(targetElement).html(data);
							setAjaxForms();
							LoadingSpinner(targetElement, false);
							/*if(window.location.pathname.slice(1) == location.hash.slice(1))
							{
								location.hash = '';
							}*/

						}
						
					},
					complete: function(XHR, status){
						if(status == 'timeout')
						{
							alert('The remote web page timed out!');
						}
						//$(targetElement).hide();
						LoadingSpinner(targetElement, false);
						//$(targetElement).show(); //$(targetElement).show('fade');
						if(targetElement == '#page_content') $("html, body").animate({ scrollTop: 0 }, "slow");
					},
					timeout: '300000'
				});
			}
		});
		$(window).trigger('hashchange'); 
		
		
	
	
	
	
	
	
	}



/*

var _gaq = _gaq || [];
_gaq.push(['_setAccount', 'UA-WHATEVER']);
if(location.hash == '' || location.hash == '#') // Only track the pageview if they're actually supposed to be here
{
	_gaq.push(['_trackPageview']);
}

(function() {
	var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
	ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
	var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
})();

(function ($) {
	  // Log all jQuery AJAX requests to Google Analytics
	  $(document).ajaxSend(function(event, xhr, settings){
		if (typeof _gaq !== "undefined" && _gaq !== null) {
		  _gaq.push(['_trackPageview', settings.url]);
		}
	  });
})(jQuery);*/

function cleanUpDialogs(selector)
{
	var i = 0;
	$(selector).each(function(){
		if(i > 0)
		{
			$(this).remove();
		}
		i++;
	});
}

var targetElement = '#page_content', siteBaseURL;
var firstLoad = true;
var siteBaseURL = window.location;
var lastAction = 0;
var lastActionData = '';
var sidebarHiddenByUser = false;

$('html').on('click', 'a', function(e){
	savePanels();
	if(deWorde.running && $(this).attr('data-worde')) // Use socket if connected and supported, otherwise AJAX
	{
		ev = $(this).attr('data-preworde'); // Allow for any extra js required if using socket
		if(ev) eval(ev);
		deWorde.socket.send($(this).attr('data-worde'));
		ev = $(this).attr('data-postworde');
		if(ev) eval(ev);
		return false;
	}
	return ajaxURL(this); // If link isn't marked for AJAX then this will return true
});

$('html').on('submit', 'form', function(e){
	savePanels();
	
	// Attempt to websocket form if connected and supported
	if(deWorde.running && $(this).attr('data-wordeform'))
	{
		ev = $(this).attr('data-preworde');
		if(ev) eval(ev);
		
		worde = $(this).attr('data-worde')
		if(worde)
		{
			deWorde.socket.send(worde);
		}
		else
		{
			deWorde.socket.send(JSON.stringify($(this).serializeObject()));
		}
		
		ev = $(this).attr('data-postworde');
		if(ev) eval(ev);
		
		return false;
	}
	
	if(!$(this).attr('id') || $(this).attr('id').length == 0)
	{
		return true;
	}
	targetElement = "#" + $(this).attr('id');
	return ajaxForm(targetElement);
});

$(document).ready(function(){

	prettify();
	setAjaxForms();
	setAjaxLinks();
	restorePanels();

	if($('.title').length > 0)
	{
		$('head title').html($('.title').html());
	}
	else
	{
		$('head title').html(APPNAME);
	}

	/*
	$.ajaxSetup ({
		cache: false
	}); 
	*/
	$(document).ajaxError(function(event, jqXHR, ajaxSettings, thrownError) {
		$('#ajax_error').attr('title', thrownError);
		$('#ajax_error').html(jqXHR.responseText);
		$('#ajax_error').dialog({ width: 800, height: 600,  show: {effect: "fade",duration: 500},hide: {effect: "fade",duration: 500} });
		$('#ajax_error').dialog('open');
		$( "#ajax_error" ).effect( 'highlight', {}, 1000 );
		targetElement = '#terminator_error_adv';
	});


	/*$(document).on('click', 'a', function(){
		return ajaxURL(this);
	});*/
	
	
	
	
});

function ajaxURL(data)
{
	if(data.href.length < 2)
	{
		return true;
	}
	ajax = $(data).attr('data-ajax');
	if(ajax == 'no_ajax')
	{
		return true;
	}
	if(data.href.slice(data.href.length - 1) == '#')
	{
		return false;
	}
	targetElement = '#page_content';
	useHashChange = true;
	
	
	if(ajax != null && ajax.length > 1)
	{
		if(ajax.indexOf(' ') != -1)
		{
			targetBits = ajax.split(' ');
			targetElement = targetBits[0];
			if(targetBits[1] == 'nohash') useHashChange = false;
		}
		else
		{
			targetElement = ajax;
		}
	}
	targetURL = data.href;
	
	if(useHashChange)
	{

	
	targetURL = data.href;
	
	
	if(History.enabled && targetElement == '#page_content') // Yay for HTML5 browsers
	{
		History.pushState(null, APPNAME, data.href);
		return false;
	}
	
	
	
	if(targetElement == '#page_content')
	{

		location.hash = '#' + data.href.slice(data.href.indexOf(window.location.hostname) + window.location.hostname.length + 1);
		return false;
	}
	

	
	}
	
	$.ajax({
		type:'GET',
		url: targetURL,
		data: targetElement == '#page_content' ? {} :{ target: targetElement},
		beforeSend: function(){
			addMarker(targetElement, targetURL);
			//$(targetElement).hide('fade');
			LoadingSpinner(targetElement, true);
		},
		success: function(data, status, xhr){
			//if(checkMarker(targetElement, targetURL))
			//{
				$(targetElement).html(data);
				if($('.title').length > 0)
				{
					$('head title').html($('.title').html());
				}
				else
				{
					$('head title').html(APPNAME);
				}
				setAjaxForms();
				setAjaxLinks();
				prettify();
				restorePanels();


			//}
		},
		error: function(data, status, xhr){
		
			if(checkMarker(targetElement, targetURL))
			{
				$(targetElement).html(data);
				setAjaxForms();
				LoadingSpinner(targetElement, false);

			}
			
		},
		complete: function(XHR, status){
			if(status == 'timeout')
			{
				alert('The remote web page timed out!');
			}
			//$(targetElement).hide();
			LoadingSpinner(targetElement, false);
			//$(targetElement).show(); //$(targetElement).show('fade');
			if(targetElement == '#page_content') $("html, body").animate({ scrollTop: 0 }, "slow");
		},
		timeout: '300000'
	});
		
	return false;
}

var display_prompt = true;
var targetSubmitURL = '';

function ajaxForm(data)
{
	if($(data).attr('data-returntarget').length > 0)
	{
		targetElement = $(data).attr('data-returntarget');
	}
	else
	{
		return true;
	}
	
	targetSubmitURL = $(data).attr('action');
	$.ajax({
		type:'POST',
		url: targetSubmitURL + (targetElement == '#page_content' ? '' : '?target=' + targetElement),
		data: $(data).serialize(),
		beforeSend: function() {
			addMarker(targetElement, targetSubmitURL);
			LoadingSpinner(targetElement, true);
		},
		success: function(data, status, xhr){
			if(!checkMarker(targetElement, targetSubmitURL))
			{
				return;
			}
			$(targetElement).html(data);
			if($('.title').length > 0)
			{
				$('head title').html($('.title').html());
			}
			else
			{
				$('head title').html('');
			}
			setAjaxForms();
			setAjaxLinks();
			prettify();
			restorePanels();
			$("html, body").animate({ scrollTop: 0 }, "slow");
		},
		complete: function(XHR, status){
			if(status == 'timeout')
			{
				alert('The remote web page timed out!');
			}
			//$(targetElement).hide();
			LoadingSpinner(targetElement, false);
			//$(targetElement).show('fade'); //$(targetElement).show('fade');
		},
		error: function(data, status, xhr){
		
			if(checkMarker(targetElement, targetURL))
			{
				$(targetElement).html(data);
				setAjaxForms();
				setAjaxLinks();
				prettify();

			}
			
		}
	});
	return false;
}

$(document).ready(function(){

	$('.show_sidebar').hide();

	$('#ajax_error').ajaxError(function(event, request, settings){
		$('#ajax_error').html(request.responseText.replace('<style','<div style="display:none"').replace('</style>','</div>'));
		$('#ajax_error').dialog({ autoOpen: true, modal: true, title: 'Error', 
				buttons:{
							"Aww Shucks": function()
							{
								$( this ).dialog( "close" );
								LoadingSpinner(targetElement, false);
							}
						}
		});
	});
	
	$('.show_sidebar').hover(function(){
		$('.show_sidebar').animate({marginRight : '+=300px'}, 'fast');
	}, function(){
		$('.show_sidebar').animate({marginRight : '0px'}, 'fast');
	});
	
	$('.show_sidebar').click(function(){
		$('.show_sidebar').hide('slow');
		sidebarHiddenByUser = false;
		$('#main_container').removeClass('main_container_nosidebar')
		$('#sidebar_container').removeClass('sidebar_container_nosidebar')
		$('#sidebar').show('slide', {direction: 'right'}, 'slow');
	});
	
	
	$(document).ajaxComplete(function(event, request, settings){
	
		$('.validation_errors').each(function(i){
			if($.trim($(this).html()))
			{
				$(this).hide();
				$(this).show('slow');
			}
			else
			{
				$(this).hide();
			}
		});
	
		if($(targetElement + ' .sidebar').length != 0)
		{
		
		sidebarhtml = $(targetElement + ' .sidebar').html();
			
			
			if(sidebarhtml.startsWith('persist='))
			{
				if(sidebarhtml.substring(8) != $('#sidebar .sidebartype').attr('value'))
				{
					$('#sidebar').hide('slide', {direction: 'right'}, 'fast', function(){
						$('#main_container').addClass('main_container_nosidebar');
						$('#sidebar_container').addClass('sidebar_container_nosidebar');
						$('#sidebar').html('');
						$('.show_sidebar').hide();
					});
				}
				
				$(targetElement + ' .sidebar').remove();
				
			}
			else if(sidebarhtml.startsWith('persist'))
			{
			}
			else if(sidebarhtml != $('#sidebar').html())
			{

				ProcessSidebar();

			}
			else
			{
				// They're the same, so do nothing
			}
		}
		else
		{
			$('#sidebar').hide('slide', {direction: 'right'}, 'fast',function(){
				//$('#sidebar').fadeTo('fast', 0, function(){
				$('#sidebar').html('');
				//});
				$('#main_container').addClass('main_container_nosidebar')
				$('#sidebar_container').addClass('sidebar_container_nosidebar')
				$('.show_sidebar').hide();
			});
		}
	
	});
	
	setAjaxForms();
	
	setAjaxLinks();
});


function ProcessSidebar()
{
	$('#sidebar').hide('slide', {direction: 'right'}, 'fast',function(){
		$('#sidebar').html($(targetElement + ' .sidebar').html());
		$(targetElement + ' .sidebar').remove();
		if($('#sidebar').html().length == 0)
		{
			$('.show_sidebar').hide();
			$('#main_container').addClass('main_container_nosidebar');
			$('#sidebar_container').addClass('sidebar_container_nosidebar');
		}
		else if (sidebarHiddenByUser == false)
		{
			$('.show_sidebar').hide();
			$('#main_container').removeClass('main_container_nosidebar');
			$('#sidebar_container').removeClass('sidebar_container_nosidebar');
			$('#sidebar').show('slide', {direction: 'right'}, 'fast');
		}
		else
		{
			$('.show_sidebar').show();
		}
		$('.hide_sidebar').click(function(){
			$('#sidebar').hide('slide', {direction: 'right'}, 'slow',function()
			{
				sidebarHiddenByUser = true;
				$('#main_container').addClass('main_container_nosidebar')
				$('#sidebar_container').addClass('sidebar_container_nosidebar')
				$('.show_sidebar').show();
			});
		});
		
	});
}

function LoadingSpinner(element, onOrOff) // We aren't using the loading spinner here, due to the slowdown. Possibly switch to some snazzy css3 transitions instead
{
	return;
	if(onOrOff)
	{
		//data = $(element).html();
		$(element).addClass('loading_spinner_parent_relative');
		$(element).append($('#loading_spinner').html());
		$(element + ' .loading_spinner').hide();
		if(element != '#page_content')
		{
			$(element + ' .loading_spinner').position({of: $(element)});
			$(element + ' .loading_spinner').width($(element).width());
			$(element + ' .loading_spinner').height($(element).height());
		}
		$(element + ' .loading_spinner').show('fade', {}, 1000);
	}
	else
	{
		$(element + '.loading_spinner').remove();
		$(element).removeClass('old_content');
		$(element).removeClass('loading_spinner_parent_relative');
	}
}

function addMarker(element, data) // Intended to deal with users trying to do multiple AJAX actions into the same div
{
	if($(element + ' .ajax_marker').length)
	{
		$(element + ' .ajax_marker').html(data);
	}
	else
	{
		$(element).append('<span class="ui-helper-hidden ajax_marker">' + data + '</span>');
	}
}

function checkMarker(element, data)
{
	if($(element + ' .ajax_marker').length && $(element + ' .ajax_marker').html() == data)
	{
		return true;
	}
	else
	{
		return false;
	}
}

function setAjaxForms()
{	
	// No longer used, did provide some backwards compatibility for awful browsers which we never hope to see again
}

function setAjaxLinks()
{
	// No longer used, did provide some backwards compatibility for awful browsers which we never hope to see again

}

function savePanels()
{
	if(!hasLocalStorage) return;
	if($( "#right_panel" ).length) localStorage['right_panel'] = $( "#right_panel" ).tabs( "option", "active" );
	if($( "#inventory_accordion" ).length) localStorage['inventory_accordion'] = $( "#inventory_accordion" ).accordion( "option", "active" );
}

function restorePanels()
{
	$( "#right_panel" ).tabs();
	if(!hasLocalStorage)
	{
		$( "#inventory_accordion" ).accordion({heightStyle: "content"});
		return;
	}
	if(localStorage['right_panel'] != null && localStorage['right_panel'] != 'null') $( "#right_panel" ).tabs( "option", "active" ,parseInt(localStorage['right_panel']) );
	if(localStorage['inventory_accordion'] != null && localStorage['inventory_accordion'] != 'null') $( "#inventory_accordion" ).accordion({collapsible: true, heightStyle: "content", active: parseInt(localStorage['inventory_accordion'])}); else $( "#inventory_accordion" ).accordion({collapsible: true, heightStyle: "content"});
}