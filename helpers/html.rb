class Dungeon < Sinatra::Application
	module HTML
		module_function

		def Link(url, text, classes = "", ajaxtarget = "", style = "", attrs = {})
			ajaxtarget = "no_ajax" if ajaxtarget == false 
			ajaxtarget = "#page_content" if ajaxtarget == ""
			link = "<a href=\"#{url}\""
			link = link + " class=\"#{classes}\"" unless classes == "" or classes === nil
			link = link + " data-ajax=\"#{ajaxtarget}\"" unless ajaxtarget == "" or ajaxtarget === nil
			link = link + " style=\"#{style}\"" unless style == "" or style === nil
			link = link + " " + attrs.map{|k,v| "#{k}='#{v}'"}.join(' ')
			link = link + ">#{text}</a>"
			return link
		end
		
		def Input(name, text, type = "text")
			return "<input name=\"#{name}\" id=\"input_#{name}\" value=\"#{text}\" type=\"#{type}\"/>"
		end
		
		def InputLine(name, text, type = "text", label = nil)
			label = name if label == nil
			return "<p style=\"width:90%;margin-left:1%\"><label style=\"min-width:10%;display:inline-block\" for=\"input_#{name}\">#{label}</label><input style=\"width:70%\"  name=\"#{name}\" id=\"input_#{name}\" value=\"#{text}\" type=\"#{type}\"/></p>"
		end
		
		def Validate(errors)
			data = ""
			if errors.kind_of?(Array) then
				data = "<div class=\"margin-10 padding-10 ui-corner-all ui-state-error\">"
				errors.each { |v| 

					if v.kind_of?(Array) then
					
						v.each { |vv| 

						data = data + "<p>#{vv}</p>"

						}
					else
						data = data + "<p>#{v}</p>"
					end

				}
				data = data + "</div>"
			end
			return data
		end
		
		def Submit(name = 'Submit', htmlclass = "button")
			return "<input type=\"submit\" value=\"#{name}\" class=\"#{htmlclass}\"/>"
		end
		
		def Form(url = nil, id = nil, return_target = nil, attrs = {})
			return_target = "#page_content" if return_target === nil
			return "</form>" if url == nil
			return "<form action=\"#{url}\" id=\"#{id}\" data-returntarget=\"#{return_target}\" method=\"POST\" #{attrs.map{|k,v| "#{k}=\"#{v}\""}.join(' ')} >" unless return_target === false
			return "<form action=\"#{url}\" id=\"#{id}\" method=\"POST\" #{attrs.map{|k,v| "#{k}=\"#{v}\""}.join(' ')} >"
		end
	end
end