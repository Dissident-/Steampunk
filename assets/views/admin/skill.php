<div id="SkillContainer"></div>

<script type="text/javascript">
$('#SkillContainer').jtable({
            title: 'Skills',
			openChildAsAccordion: true,
            actions: {
                listAction: '/admin/skill?action=list',
                createAction: '/admin/skill?action=create',
                updateAction: '/admin/skill?action=update',
                deleteAction: '/admin/skill?action=delete'
            },
			paging: false,
			pageSize: 10,
			sorting: false,
			defaultSorting: 'SkillName ASC',
            fields: {
                SkillID: {
                    key: true,
                    list: false
                },
				Usage:
				{
					title: '\\o/',
					width: '1%',
					sorting: false,
					edit: false,
					create: false,
					display: function (table) {
						var $img = $('<img src="/ails/icon_90.png" title="Effects" />');
						$img.click(function () {
						
							$('#SkillContainer').jtable('openChildTable',
									$img.closest('tr'),
									{
										paging: false, //Enable paging
										pageSize: 10, //Set page size (default: 10)
										sorting: false, //Enable sorting
										defaultSorting: 'Title ASC', //Set default sorting
										title: 'Skill Usage',
										actions: {
											listAction: '/admin/skillusage?action=list&SkillID=' + table.record.SkillID,
											createAction: '/admin/skillusage?action=create&SkillID=' + table.record.SkillID,
											deleteAction: '/admin/skillusage?action=delete&SkillID=' + table.record.SkillID
										},
										fields: {
													Usage:
													{
														title: '\\o/',
														width: '1%',
														sorting: false,
														edit: false,
														create: false,
														display: function (table2) {
															var $img = $('<img src="/ails/I_Message02.png" title="Properties" />');
															$img.click(function () {
																
																actions = {
																	createAction: '/admin/skilleffect?action=create&SkillID=' + table.record.SkillID + '&SkillUsageID=' + table2.record.SkillUsageID,
																	listAction: '/admin/skilleffect?action=list&SkillID=' + table.record.SkillID + '&SkillUsageID=' + table2.record.SkillUsageID,
																	updateAction: '/admin/skilleffect?action=update&SkillID=' + table.record.SkillID + '&SkillUsageID=' + table2.record.SkillUsageID,
																	deleteAction: '/admin/skilleffect?action=delete&SkillID=' + table.record.SkillID + '&SkillUsageID=' + table2.record.SkillUsageID
																};
																if(table2.record.UsageName == 'armour')
																{
																		attrtype = {
																							title: 'Type',
																							list: true,
																							create: true,
																							edit: true,
																							options: '/admin/options?action=types&SkillUsageID=' + table2.record.SkillUsageID
																						};
																}
																else if(table2.record.UsageName == 'weaponbuff')
																{
																		attrtype = {
																							title: 'Affected Tag',
																							list: true,
																							create: true,
																							edit: true,
																							options: '/admin/options?action=types&SkillUsageID=' + table2.record.SkillUsageID
																						};																	
																}
																else if(table2.record.UsageName == 'activated')
																{
																		attrtype = {
																					title: 'Type',
																					list:  true,
																					create:  true,
																					edit:  true,
																					defaultValue: ''
																				};
																}
																else
																{

																	attrtype = {
																					title: 'Type',
																					list:  false,
																					create:  false,
																					edit:  false,
																					defaultValue: ''
																				};
																}
																$('#SkillContainer').jtable('openChildTable',
																		$img.closest('tr'),
																		{
																			paging: false, //Enable paging
																			pageSize: 10, //Set page size (default: 10)
																			sorting: false, //Enable sorting
																			defaultSorting: 'Title ASC', //Set default sorting
																			title: 'Skill Effects',
																			actions: actions,
																			fields: {
																						SkillEffectID:
																						{
																							key:true,
																							list: false
																						},
																						SkillUsageID:
																						{
																							list: false,
																							edit: false,
																							create: false,
																							defaultValue: table2.record.SkillUsageID
																						},
																						SkillID:
																						{
																							list: false,
																							edit: false,
																							create: false,
																							defaultValue: table.record.SkillID
																						},
																						AttributeName:
																						{
																							title: 'Property',
																							edit: true,
																							options: '/admin/options?action=attributes&SkillUsageID=' + table2.record.SkillUsageID,
																						},
																						AttributeType: attrtype,
																						AttributeValue:
																						{
																							title: 'Value'
																						}
																					}
																		}, function (data) { //opened handler
																	data.childTable.jtable('load');
																});
															});
															return $img;
														}
													},
													SkillUsageID:
													{
														key:true,
														list:true,
														edit:true,
														create:true,
														width:'99%',
														title: 'Item',
														options: '/admin/options?action=usages',
													}
												}
									}, function (data) { //opened handler
								data.childTable.jtable('load');
							});
						});
						return $img;
					}
				},
				SkillName:
				{
					title: 'Skill Name'
				},
				SkillBaseCost:
				{
					title: 'Skill Points'
				},
				Icon:
				{
					title: 'Icon',
					display: function (table) {
						return '<img src="' + table.record.Icon + '" />' + table.record.Icon;
					}
				}
			}
});
$('#SkillContainer').jtable('load');
</script>