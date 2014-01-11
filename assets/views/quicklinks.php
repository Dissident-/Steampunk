			<?php
				if($logged_in)
				{
					$_link('/character/list', 'Characters', 'button');
					$_link('/auth/logout', 'Logout', 'button', 'body');
					if($has_role['Admin'])
					{
						echo '<div class="ui-corner-all" style="margin:2px;padding:2px;display:inline-block;border:1px solid black;background-color:lightyellow">Admin: ';
						$_link('/admin/tiletype', 'Tiles', 'button');
						$_link('/admin/itemtype', 'Items', 'button');
						$_link('/admin/itemcategory', 'Item Groups', 'button');
						echo '</div>';
					}
				}
				else
				{
					$_link('/auth/login', 'Login', 'button');
					$_link('/auth/register', 'Register', 'button');
				}