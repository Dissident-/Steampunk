module Interface
	class Element
		def self.Quicklinks(user)
			data = ""
			if user then
				data = data + HTML.Link( '/character/list', 'Characters', 'button' )
				data = data + HTML.Link( '/auth/logout', 'Logout', 'button', 'no_ajax' )
				data = data + '<div class="ui-corner-all" style="margin:2px;padding:2px;display:inline-block;border:1px solid black;background-color:lightyellow">Admin: '
				data = data + HTML.Link( '/mapeditor', 'Map', 'button' )
				data = data + HTML.Link( '/admin/tiletype', 'Tiles', 'button' )
				data = data + HTML.Link( '/admin/itemtype', 'Items', 'button' )
				data = data + HTML.Link( '/admin/itemcategory', 'Item Groups', 'button' )
				data = data + HTML.Link( '/admin/skill', 'Skills', 'button' )
				data = data + '</div>'
			else
				data = data + HTML.Link( '/auth/login', 'Login', 'button' )
				data = data + HTML.Link( '/auth/register', 'Register', 'button' )
			end
			return data
		end
	

	end
end