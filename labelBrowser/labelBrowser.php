<?php
    $classFile =  $_SERVER['PHP_SELF'];

    if ($cmd = $_REQUEST['cmd']) {
        if (method_exists($page, $_REQUEST['cmd'])) {
            $page->$cmd();
        }
        die();
    }
?>
<div id='mainLayout' class="easyui-layout" data-options="fit:true">
    <div data-options="region:'north',split:true, minHeight:40, maxHeight:40" style='
			height: 40px;
			padding:3px;
			padding-left:30px; padding-right:30px;
			background-color: #f0f0f0;
		' href="<?php print $classFile; ?>?cmd=drawMainMenu">
    </div>
    <div data-options="region:'west', split:true, maxWidth:250" title="Settings" style="padding:0px;">
        <div class="easyui-accordion" id='settings' data-options="border:false">
            <div id='basicMenu' title="Basic" data-options="noheader:true,selected:true,collapsible:false" style="padding:10px;" href='<?php print $classFile; ?>?cmd=menuBasic'></div>
            <div id='MailingListMenu' title="Selection options" style="padding:10px;" data-options="onLoad: function(){rowCheckUncheck('', {})}" href="<?php print $classFile; ?>?cmd=favouritesListOptions"></div>
            <div id='imageOptions' title="Image options" style="padding:10px;" href="<?php print $classFile; ?>?cmd=imageOptions"></div>
            <div id='advancedMenu' title="Advanced" style="padding:10px; border:0px;" href="<?php print $classFile; ?>?cmd=menuAdvanced"></div>
            <span id="realtimeLogo" title="<?php print $GLOBALS['realtimeConfig']['COPYRIGHT']; ?>" class="easyui-tooltip" />
        </div>
    </div>
    <div data-options="region:'center',title:'<?php print $page->title; ?>'" style="padding:0px;">
        <div class="easyui-layout" data-options="fit:true">
            <div id='resultPanel' data-options="region:'north',split:true,border:false, minHeight:88" style="height:88px" href="<?php print $classFile; ?>?cmd=<?php print $page->resultDisplayHandler; ?>"></div>
            <div id='imagePanel' data-options="region:'center',split:true"></div>
        </div>
    </div>
</div>
