<div id="ItemTypeContainer"></div>

<script type="text/javascript">
$('#ItemTypeContainer').jtable({
            title: 'Item Types',
			openChildAsAccordion: true,
            actions: {
                listAction: '/admin/itemtype?action=list',
                createAction: '/admin/itemtype?action=create',
                updateAction: '/admin/itemtype?action=update',
                deleteAction: '/admin/itemtype?action=delete'
            },
			paging: false,
			pageSize: 10,
			sorting: false,
			defaultSorting: 'ItemTypeName ASC',
            fields: {
                ItemTypeID: {
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
						var $img = $('<img src="/ails/icon_90.png" title="Usage" />');
						$img.click(function () {
						
							$('#ItemTypeContainer').jtable('openChildTable',
									$img.closest('tr'),
									{
										paging: false, //Enable paging
										pageSize: 10, //Set page size (default: 10)
										sorting: false, //Enable sorting
										defaultSorting: 'Title ASC', //Set default sorting
										title: 'Item Usage',
										actions: {
											listAction: '/admin/itemusage?action=list&ItemTypeID=' + table.record.ItemTypeID,
											createAction: '/admin/itemusage?action=create&ItemTypeID=' + table.record.ItemTypeID,
											deleteAction: '/admin/itemusage?action=delete&ItemTypeID=' + table.record.ItemTypeID
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
																	createAction: '/admin/itemusageattribute?action=create&ItemTypeID=' + table.record.ItemTypeID + '&ItemUsageID=' + table2.record.ItemUsageID,
																	listAction: '/admin/itemusageattribute?action=list&ItemTypeID=' + table.record.ItemTypeID + '&ItemUsageID=' + table2.record.ItemUsageID,
																	updateAction: '/admin/itemusageattribute?action=update&ItemTypeID=' + table.record.ItemTypeID + '&ItemUsageID=' + table2.record.ItemUsageID,
																	deleteAction: '/admin/itemusageattribute?action=delete&ItemTypeID=' + table.record.ItemTypeID + '&ItemUsageID=' + table2.record.ItemUsageID
																};
																attrname = {
																				title: 'Property',
																				edit: true,
																				options: '/admin/options?action=attributes&ItemTypeID=' + table.record.ItemTypeID + '&ItemUsageID=' + table2.record.ItemUsageID,
																			};
																if(table2.record.UsageName == 'armour')
																{
																		attrtype = {
																							title: 'Type',
																							list: true,
																							create: true,
																							edit: true,
																							options: '/admin/options?action=types&ItemTypeID=' + table.record.ItemTypeID + '&ItemUsageID=' + table2.record.ItemUsageID
																						};
																}
																else if(table2.record.UsageName == 'weaponbuff')
																{
																		attrtype = {
																							title: 'Affected Tag',
																							list: true,
																							create: true,
																							edit: true,
																							options: '/admin/options?action=types&ItemTypeID=' + table.record.ItemTypeID + '&ItemUsageID=' + table2.record.ItemUsageID
																						};																	
																}
																else if(table2.record.UsageName == 'customattribute')
																{
																		attrname = {
																				title: 'Property',
																				edit: true,
																			};
																		attrtype = {
																					title: 'Type',
																					list:  false,
																					create:  false,
																					edit:  false,
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
																$('#ItemTypeContainer').jtable('openChildTable',
																		$img.closest('tr'),
																		{
																			paging: false, //Enable paging
																			pageSize: 10, //Set page size (default: 10)
																			sorting: false, //Enable sorting
																			defaultSorting: 'Title ASC', //Set default sorting
																			title: 'Item Properties',
																			actions: actions,
																			fields: {
																						ItemUsageAttributeID:
																						{
																							key:true,
																							list: false
																						},
																						ItemUsageID:
																						{
																							list: false,
																							edit: false,
																							create: false,
																							defaultValue: table2.record.ItemUsageID
																						},
																						ItemTypeID:
																						{
																							list: false,
																							edit: false,
																							create: false,
																							defaultValue: table.record.ItemTypeID
																						},
																						AttributeName: attrname,
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
													ItemUsageID:
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
				ItemTypeName:
				{
					title: 'Item Name'
				},
				ItemCategoryID:{
					title: 'Category Name',
					options: '/admin/options?action=categories',
				},
				BaseWeight:
				{
					title: 'Weight'
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
$('#ItemTypeContainer').jtable('load');
</script>