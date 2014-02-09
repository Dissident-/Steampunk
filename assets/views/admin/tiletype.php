<div id="TileTypeContainer"></div>

<script type="text/javascript">
$('#TileTypeContainer').jtable({
            title: 'Tile Types',
			openChildAsAccordion: true,
            actions: {
                listAction: '/admin/tiletype?action=list',
                createAction: '/admin/tiletype?action=create',
                updateAction: '/admin/tiletype?action=update',
                deleteAction: '/admin/tiletype?action=delete'
            },
			paging: false,
			pageSize: 10,
			sorting: false,
			defaultSorting: 'TypeName ASC',
            fields: {
                TileTypeID: {
                    key: true,
                    list: false
                },
				SearchOdds:
				{
					title: '',
					width: '1%',
					sorting: false,
					edit: false,
					create: false,
					display: function (table) {
						var $img = $('<img src="/ails/I_Map.png" title="Search Odds" />');
						$img.click(function () {
						
							$('#TileTypeContainer').jtable('openChildTable',
									$img.closest('tr'),
									{
										paging: false, //Enable paging
										pageSize: 10, //Set page size (default: 10)
										sorting: false, //Enable sorting
										defaultSorting: 'Title ASC', //Set default sorting
										title: 'Search Odds',
										actions: {
											listAction: '/admin/searchodds?action=list&TileTypeID=' + table.record.TileTypeID,
											createAction: '/admin/searchodds?action=create&TileTypeID=' + table.record.TileTypeID,
											updateAction: '/admin/searchodds?action=update&TileTypeID=' + table.record.TileTypeID,
											deleteAction: '/admin/searchodds?action=delete&TileTypeID=' + table.record.TileTypeID
										},
										fields: {
													ItemTypeID:
													{
														key:true,
														list:true,
														edit:true,
														create:true,
														title: 'Item',
														options: '/admin/searchodds?action=items&TileTypeID=' + table.record.TileTypeID,
													},
													ChanceWeight:
													{
														title: 'Drop Chance',
														display: function (ctable) {
															return ctable.record.ChanceWeight / 100 + '%';
														}
													}
												}
									}, function (data) { //opened handler
								data.childTable.jtable('load');
							});
						});
						return $img;
					}
				},
				TypeName:{
					title: 'Type Name'
				},
				APCost:
				{
					title: 'AP Cost',
					list: false,
					defaultValue: 1
				},
				Traversible:
				{
					title: 'Traversible',
					list: false,
					options: {'Always' : 'Always', 'WithAttribute' : 'Requires Attribute', 'WithoutAttribute' : 'Cannot Have Attribute', 'Never' : 'Never'},
					defaultValue: 'Always'
				},
				Colour:
				{
					title: 'Colour',
					display: function (table) {
						return '<div style="display:inline-block;width:15px;height:15px;border:1px solid black;background-color:' + table.record.Colour + '"></div> ' + table.record.Colour;
					}
				},
				TileIcon:
				{
					title: 'Icon',
					display: function (table) {
						return '<img src="' + table.record.TileIcon + '" />' + table.record.TileIcon;
					}
				},
				DefaultDescription:
				{
					title: 'Default Description',
					list: false,
					type: 'textarea'
				}
			}
});
$('#TileTypeContainer').jtable('load');
</script>