<div id="api_shopware_tabs">
<ul>
<li><a href="#api_shopware_tabs_start">Start</a></li>
<li><a href="#api_shopware_tabs_settings">Settings</a></li>
<li><a href="#api_shopware_tabs_attributes">Attribute</a></li>
<li><a href="#api_shopware_tabs_import">Import</a></li>
</ul>
<div id="api_shopware_tabs_start">
<p>Proin elit arcu, rutrum commodo, vehicula tempus, commodo a, risus. Curabitur nec arcu. Donec sollicitudin mi sit amet mauris. Nam elementum quam ullamcorper ante. Etiam aliquet massa et lorem. Mauris dapibus lacus auctor risus. Aenean tempor ullamcorper leo. Vivamus sed magna quis ligula eleifend adipiscing. Duis orci. Aliquam sodales tortor vitae ipsum. Aliquam nulla. Duis aliquam molestie erat. Ut et mauris vel pede varius sollicitudin. Sed ut dolor nec orci tincidunt interdum. Phasellus ipsum. Nunc tristique tempus lectus.</p>
</div>
<div id="api_shopware_tabs_settings">
<p>Morbi tincidunt, dui sit amet facilisis feugiat, odio metus gravida ante, ut pharetra massa metus id nunc. Duis scelerisque molestie turpis. Sed fringilla, massa eget luctus malesuada, metus eros molestie lectus, ut tempus eros massa ut dolor. Aenean aliquet fringilla sem. Suspendisse sed ligula in ligula suscipit aliquam. Praesent in eros vestibulum mi adipiscing adipiscing. Morbi facilisis. Curabitur ornare consequat nunc. Aenean vel metus. Ut posuere viverra nulla. Aliquam erat volutpat. Pellentesque convallis. Maecenas feugiat, tellus pellentesque pretium posuere, felis lorem euismod felis, eu ornare leo nisi vel felis. Mauris consectetur tortor et purus.</p>
</div>
<div id="api_shopware_tabs_attributes">
<?php 

include "api/ot_api_attributes_matching.php";

?>
</div>
<div id="api_shopware_tabs_import">
	<form action="<?php echo $ot->get_link("data_source", $data_source->id, "shopware"); ?>" method="POST">
		<input type="hidden" name="action" value="import_orders">
		<input type="submit" value="Import Orders">
	</form>
</div>
</div>



<script>
	$(function() {
		$( "#api_shopware_tabs" ).tabs();
	});
</script>

