<?php
	include('../session.php');
	online_session_start();
	
	include('dataLookup.class.php');
	include('inventoryLookup.class.php');
	
	$classFile = $_SERVER['PHP_SELF'];
	$page = new inventoryLookup($classFile);

	if ($cmd = $_REQUEST['cmd']) {
		if (method_exists($page, $_REQUEST['cmd'])) {
			$page->$cmd();
		}
		die();
	}

?>
<!DOCTYPE html>
<html lang="en">
<?php

	include('header.html');

//	$page->drawmenu($GLOBALS['realtimeConfig']['MAINMENU']);
	$debug = $_REQUEST['debug'];
	
	session_write_close();

?>
<body>

<div id='lookupDetailWindow' class='easyui-window' title='Inventory item detail' data-options='modal:true,closed:true' style='width:800px;height:600px;padding:10px;'>
</div>

<div id='mainLayout' class="easyui-layout" data-options="fit:true">
	<div data-options="region:'north'
			, split:true
			, minHeight:40
			, maxHeight:40
	" id='realtimeMenuPanel' href="<?php print $classFile; ?>?cmd=drawMainMenu"></div>
	<div data-options="region:'west', split:true, maxWidth:250" title="Settings" style="padding:0px;">
		<div class="easyui-accordion" id='settings' data-options="border:false">
			<div id='basicMenu' title="Basic" data-options="noheader:true,selected:true,collapsible:false" style="padding:10px;" href='<?php print $classFile; ?>?cmd=menuBasic'></div>
			<div id='optionsMenu' title="Options" style="padding:10px;" href="<?php print $classFile; ?>?cmd=menuOptions"></div>
			<div id='advancedMenu' title="Advanced" style="padding:10px; border:0px;" href="<?php print $classFile; ?>?cmd=menuAdvanced"></div>
			<span id="realtimeLogo" title="<?php print $GLOBALS['realtimeConfig']['COPYRIGHT']; ?>" class="easyui-tooltip" />
		</div>
	</div>
	<div data-options="region:'center',title:'<?php print $page->title; ?>'" style="padding:0px;" href="<?php print $classFile; ?>?cmd=<?php print $page->resultDisplayHandler; ?>">
	</div>
</div>

</body>

<?php
	include('footer.html');
//	print_r($_SERVER);
?>
</html>