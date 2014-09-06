class Dungeon < Sinatra::Application
	module HTML
		module_function

		def Link(url, text, classes = "", rel = "", style = "")
			rel = "no_ajax" if rel == false 
			link = "<a href=\"#{url}\""
			link = link + " class=\"#{classes}\"" unless classes == ""
			link = link + " rel=\"#{rel}\"" unless rel == ""
			link = link + " style=\"#{style}\"" unless style == ""
			link = link +  " style=\"#{classes}\"" unless classes == ""
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
		
		def Form(url = nil, id = nil, return_target = "#page_content")
		
			return "</form>" if url == nil
			return "<form action=\"#{url}\" id=\"#{id}\" method=\"POST\"><input class=\"return_target\" name=\"return_target\" type=\"hidden\" value=\"#{return_target}\"/>" unless return_target == false
			return "<form action=\"#{url}\" id=\"#{id}\" method=\"POST\"><input class=\"no_ajax\" name=\"no_ajax\" type=\"hidden\" value=\"\"/>"
		end
	end
end