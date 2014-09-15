module Library
	module Trigger
		class ExplosiveMurder
			def self.activated(user)
				return 'You are dead!' if user.location === nil or user.hp < 1
				return 'You need 1 AP in order to explode!' if user.ap < 1
				user.hp = 0
				user.ap = user.ap - 1
				user.location.occupants.each do |victim|
					unless user == victim then
						victim.damage(Random.rand(10..20), :unholy)
					end
				end
				user.location.broadcast user.link + ' has exploded violently, dealing unholy damage to everyone in the area'
				user.move nil
				return :success
			end
		end
	end
end