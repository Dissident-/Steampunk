<div id="ItemCategoryContainer"></div>

<script type="text/javascript">
$('#ItemCategoryContainer').jtable({
            title: 'Item Categories',
			openChildAsAccordion: true,
            actions: {
                listAction: '/admin/itemcategory?action=list',
                createAction: '/admin/itemcategory?action=create',
                updateAction: '/admin/itemcategory?action=update',
                deleteAction: '/admin/itemcategory?action=delete'
            },
			paging: false,
			pageSize: 10,
			sorting: false,
			defaultSorting: 'CategoryName ASC',
            fields: {
                ItemCategoryID: {
                    key: true,
                    list: false
                },
				CategoryName:{
					title: 'Category Name'
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
$('#ItemCategoryContainer').jtable('load');
</script>