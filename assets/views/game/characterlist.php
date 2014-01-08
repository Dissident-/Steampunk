<div id="CharacterListContainer"></div>
<?php $_link('/character/create', 'Create Character', 'button clear-left margin-10');

	// Seeing as everything else works for people without Javascript on, maybe have a non-js fallback here? Or use it as a gate, so people without js have to know how to get in (let's face it, nobody wants to play without js)

 ?>
<script type="text/javascript">
$('#CharacterListContainer').jtable({
            title: 'Your Characters',
			openChildAsAccordion: true,
			paging: false,
			pageSize: 10,
			sorting: false,
			defaultSorting: 'CharName ASC',
            fields: {
                CharacterID: {
                    key: true,
                    list: false
                },
				CharName:{
					title: 'Name',
					display: function(table){
						return '<?php $_link('/game/\' + table.record.CharacterID + \'', '\' + table.record.CharName + \'', 'button', '#page_content') // Hideous because we need to escape out for some Javascript ?>';
					}
				},
				HitPoints:
				{
					title: 'HP',
					width:'2%'
				},
				ActionPoints:
				{
					title: 'AP',
					width:'2%'
				},
				Experience:{
					title: 'XP',
					width:'2%'
				},
				Location:{
					title: 'Location',
					width:'20%'
				}
			}
});

<?php


foreach($characters as $char)
{
	// No point in having an AJAX call here, so let's just dump it onto the page
	echo '$("#CharacterListContainer").jtable("addRecord", {
	clientOnly: true,
	animationsEnabled: false,
	record:';
	
	echo json_encode(array('CharacterID' => $char->CharacterID, 'CharName' => $char->CharName, 'HitPoints' => $char->HitPoints, 'ActionPoints' => $char->ActionPoints, 'Experience' => $char->Experience, 'Location' => ($char->LocationID == null ? 'The Void' : $char->Location->LocationName.' ('.$char->Location->CoordinateX.', '.$char->Location->CoordinateY.', '.$char->Location->Plane->PlaneName.')')));

	echo '});';
}

?>
</script>