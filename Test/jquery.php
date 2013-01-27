<div id="popup">
	<form>
		<input type="text"><br>
		<input type="text"><br>
		<input type="text"><br>
	</form>
</div>
<button id="open">Öffnen</button>

<script>
$("#popup").dialog({
    autoOpen: false,
    height: 300,
    width: 350,
    modal: true
});

$( "#open" )
.button()
.click(function() {
  $( "#popup" ).dialog( "open" );
});
</script>
