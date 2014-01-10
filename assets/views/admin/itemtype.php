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
															
																$('#ItemTypeContainer').jtable('openChildTable',
																		$img.closest('tr'),
																		{
																			paging: false, //Enable paging
																			pageSize: 10, //Set page size (default: 10)
																			sorting: false, //Enable sorting
																			defaultSorting: 'Title ASC', //Set default sorting
																			title: 'Item Properties',
																			actions: {
																				listAction: '/admin/itemusageattribute?action=list&ItemTypeID=' + table.record.ItemTypeID + '&ItemUsageID=' + table2.record.ItemUsageID,
																				updateAction: '/admin/itemusageattribute?action=update&ItemTypeID=' + table.record.ItemTypeID + '&ItemUsageID=' + table2.record.ItemUsageID
																			},
																			fields: {
																						ItemUsageAttribute:
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
																						AttributeName:
																						{
																							title: 'Property',
																							edit: true,
																							options: '/admin/itemusageattribute?action=attributes&ItemTypeID=' + table.record.ItemTypeID + '&ItemUsageID=' + table2.record.ItemUsageID,
																						},
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
														options: '/admin/itemusage?action=usages&ItemTypeID=' + table.record.ItemTypeID,
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
					options: '/admin/itemtype?action=categories',
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