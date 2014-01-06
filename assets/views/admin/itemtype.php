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