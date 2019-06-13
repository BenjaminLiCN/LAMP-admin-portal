<?php
// Copyright (c) 2014 - Realtime Holdings PL - All right reserved
// ================================================================
// This file contains class realtimeData.
// This is the base class of the "Realtime Data" framework.
// Use of this framework without the written authorisation of
// the copyright holder is strictly prohibited.
// For contact details, refer to http://www.realtime.com.au
// ================================================================
// RD001 - Improved single/multiple window handling.
// RD002 - Report integration into menu - Special $USER menu
// RD002a - Workaround for Chrome. Will not render the menu properly
// RD003 - Asynchronous sessionUpdate handler
// RD004 - Download progress bar for sales analysis and data lookup screens
// RD005 - Improved login and logout redirection
// SA001 - Improved multiple selection when whichSelect = matchmultiple
// RD006 - New easyui based user administration and user preferences screens. realtimeData class replaces userLogin class. Added mmenu method

class realtimeData {
//	var $includeFooter = false;
//	var $includeFooterPath = false;
	var $debugData = '';
	var $error = null;
	var $lookupDetailWindow = 'lookupDetailWindow';	// Default name of the detail/edit window
	var $methodClassMap = array();

	function method_exists($method) {
		return(method_exists($this, $method) || array_key_exists($method, $this->methodClassMap));
	}

	function __construct($novalidate=false) {
		$this->opendb();
		if (!($timezone = $_SESSION['user']['timezone'])) {
			$sql = "select Value from ONLINE_USER_LOCAL where (Username = 'default') and (Parameter = 'timezone')";
			if (($r = $this->querydb($sql)) && ($rec = mysql_fetch_assoc($r)))
				$timezone = $rec['Value'];
		}
		date_default_timezone_set($timezone);
/*
		if ((!isset($_SESSION)) || $novalidate)
			return;

		// =============================================================================================
		// Verify that the user is logged in, otherwise set the global variable $user to NULL.
		// This global variable is set into the session variables by the login process.
		if ($_SESSION['user']) {
			$sql = "select * from ONLINE_USER where (username = '".$_SESSION['user']['username']."') and (password = '".$_SESSION['user']['password']."')";
			$r = $this->querydb($sql, $GLOBALS['realtimeConfig']['DATABASE']);
			if (mysql_num_rows($r) <= 0)
				$_SESSION['user'] = NULL;
			else  if (TIMS_MULTICO) {
				$sql = "select bit_or(DBMASK+0) as DBMASK from TIMS_DBMAP where concat(DB, CO)";
				if ($_SESSION['user']['company']) {
					$or = " in ('";
					foreach ($_SESSION['user']['company'] as $co) {
						$sql .= $or.$co;
						$or = "','";
					}
					$sql .= "')";
				}
				if (($r = $this->querydb($sql)) && ($rec = mysql_fetch_array($r)))
					$_SESSION['user']['DBMASK'] = $rec[0]+0;
			}
		}

		$loginRedirect = false;
		// =============================================================================================
		// Find the target window name for the selected page and set this in global $thisWindowName.
		// This variable will be passed to the FormOpen functions in the various modules.
		$sql = "select * from ONLINE_MENU where (url = '".$_SERVER['REQUEST_URI']."') or (url = '".$_SERVER['PHP_SELF']."')";
		if (($r = $this->querydb($sql, $GLOBALS['realtimeConfig']['DATABASE'])) && ($rec = mysql_fetch_array($r))) {
	//		$thisWindowName = $rec['target'];
			$GLOBALS['thisWindowName']= (($_SESSION['user']['singleWindowMode'] == 'on') && ($rec['target'] != '_blank'))?'_self':$rec['target'];
			$GLOBALS['thisWindowTitle'] = $rec['description'];

			// =============================================================================================
			// If the selected page requires you to be logged in, but you aren't,
			// then you will be redirected to the login page.
			$loginRedirect = (($rec['login'] == 'yes') && ($_SESSION['user'] == NULL) && ($rec['url'] != $GLOBALS['realtimeConfig']['LOGIN']));
		}
		else {
			$arg = explode(":", $_REQUEST['menu'], 2);
			if (count($arg) == 2)
				$sql = "select * from ONLINE_MENU where (menu = '".$arg[0]."') and (indexno = '".$arg[1]."')";
			else {
				$arg = explode("/", $_REQUEST['menu'], 2);
				$sql = "select * from ONLINE_MENU where (menu = '".$arg[0]."') and (item = '".$arg[1]."')";
			}
			if (($r = $this->querydb($sql, $GLOBALS['realtimeConfig']['DATABASE'])) && ($rec = mysql_fetch_array($r))) {
	//			$thisWindowName = $rec['target'];
				$GLOBALS['thisWindowName'] = (($rec['target'] > '') && ($_SESSION['user']['singleWindowMode'] == 'on'))?'_self':$rec['target'];
				$GLOBALS['thisWindowTitle'] = $rec['description'];
			}
			else
				$loginRedirect = ($_SESSION['user'] == NULL);
		}

		if ($loginRedirect)
			die("
				<script>
					window.location.assign('');
					window.location.assign('".$GLOBALS['realtimeConfig']['LOGIN']."');
				</script>
			");

//		if ($header)
//			include($header);
*/
	}

	function __destruct() {
//		if ($GLOBALS['debug'] && $this->debugData)
//			print $this->debugData.";<br/>\n";

	}

	//jeasyui menubutton menu
	function mmenu_tree($menu, $mainmenu, $user_rec=array(), $menuok=true, $m=false) {
		global $debug;

		$d = $debug;
		$debug = false;
		
		if (!$m)
			$m = $this->load_menu_array($user_rec, $mainmenu);
		$ret .= '<ul>
		';
		foreach($m as $n => $rec) {
			$target = (($_SESSION['user']['singleWindowMode'] == 'on') && ($rec["target"] != '_blank'))?'_self':$rec['target'];
			if (!$rec['deny']) {
				$onclick = null;
				if ($rec["static"] == 'on')
					$onclick = "rtMenuHandler(\"".$target."\",\"".$GLOBALS['realtimeConfig']['ROOT'].'/static/main.php?menu='.$rec["menu"]."/".$rec["item"]."\");";
				else {
					if ($rec['classname'] > '')
						$rec['url'] = $GLOBALS['realtimeConfig']['ROOT'].'/lookup/reportLookup.php?run='.$rec["classname"];
					$onclick = "rtMenuHandler(\"".$target."\",\"".$rec["url"]."\");";
				}
				if ($rec["text"] == '$USER')
					$text = str_replace('"', "&quot;", $user_rec['fullname']);
				else
					$text = str_replace('"', "&quot;", $rec["text"]);
				if ($rec['icon'] > '')
					$text = "<i class='".$rec['icon']."'></i>&nbsp;".$text;
				if ($rec["branch"]) {
					$ret .= "<li><span ${icon} title='${rec["description"]}'>".$text."</span>\n";
					$ret .= $this->mmenu_tree($rec["item"], $mainmenu, $user_rec, NULL, $rec["branch"]);
					$ret .= "</li>\n";
				}
				else if ($rec['text'] == '-')
					$ret .= "<li class='mm-divider mm-spacer'></li>";
				else
				{
					$ret .= "<li><a ${icon} title='${rec["description"]}'";
					if ($onclick)
							$ret .= " href='#' onclick='".$onclick."'";
					$ret .= ">$text</a></li>\n";
				}
			}
		}
		$ret .= '</ul>
		';
		$debug = $d;
		return($ret);
	}

	public function mmenu($menu=null, $username=null) {

		if (!$menu)
			$menu = $GLOBALS['realtimeConfig']['MAINMENU'];
		if ($username)
			$userrec = $this->retrieve_user_settings($username);
		else
			$userrec = $_SESSION['user'];

		print '
		<nav id="mainMenu">
		';
		print $this->mmenu_tree($menu, $menu, $userrec);
		print '
		</nav>
		<script type="text/javascript">
			jQuery(document).ready(function( $ ) {
				$("#mainMenu").mmenu({
					autoHeight: true,
					dropdown: true,
					divider: ""
				},{
				className: {
					divider: null
				}
				});
			});
			function rtMenuHandler(target, url){
				win = window.open(url,target);
				win.focus();
			}
		</script>
		';
	}

	// Debugging function will write contents of an array to a file
	// e.g. dump('/tmp/err', $_REQUEST);
	function dumpVar($fname, $v) {
		if ($fp = fopen($fname, 'a')) {
			fwrite($fp, print_r($v, true));
			fclose($fp);
		}
	}

	var $dbLinkResource;
	function opendb($sqlserver="") {
		global $realtimeConfig;

		if ($sqlserver <= "")
			$sqlserver = $GLOBALS['realtimeConfig']['DBSERVER'][0];

		if(!($this->dbLinkResource = mysql_connect($sqlserver, $GLOBALS['realtimeConfig']['DBUSER'], $GLOBALS['realtimeConfig']['DBPASS'])))
			die ("Connection failed in function opendb()<br/>");
		
		return( $this->dbLinkResource);
	}

	function closedb($db) {
		if ($db)
			mysql_close($db);
		else
			mysql_close($this->dbLinkResource);
	}

	function querydb($sql, $database=null) {
		global $debug,  $realtimeConfig;

//		Do not replace <br> with the proper <br\>
//		This will break JSON.parse in the dataLoookup EditSubmit handlers
		$this->debugData .= $sql.";<br>\n";
		if ($database == null)
			$database = $GLOBALS['realtimeConfig']['DATABASE'];
		if ($ret = @mysql_db_query($database, $sql))
			return($ret);
		$this->debugData .= '#'.mysql_error()."<br>\n";
//		if ($GLOBALS['debug'])
//			print '#'.mysql_error()."<br/>\n";
	}

	public function jsonEncodeHexTag($data) {
		if (defined('JSON_HEX_TAG'))
			print(json_encode($data, JSON_HEX_TAG));
		else
			print(str_replace(array('<', '>'), array('\u003C', '\u003E'), json_encode($data)));
	}

	function unserialize($value) {
		if (preg_match('/^a:[0-9]*:/', $value))
			return(unserialize($value));
		else {
			$ret = array();
			$sql = explode(";\r\n", $value);
			foreach ($sql as $q) {
				$q = str_replace(';$', '', $q);
				$q = trim($q);
				if ($q > '')
					$ret[count($ret)] = $q;
			}
		}
		return($ret);
	}
	
	// Convert an array into string for storage in an SQL 'text' field
	function serialize($value) {
		return(serialize($value));
//		$ret = '';
//		foreach ($value as $v)
//			$ret .= $v.";\r\n";
//		return($ret);
	}

function useradmin_save_user_settings_query($cmd, $username, $parameter, $value) {
	$ret = null;
	$sql = "select * from ONLINE_USER_PARAMETERS where Parameter = '$parameter'";
	if ($r = $this->querydb($sql))
		$parm = mysql_fetch_assoc($r);
//print "<br/>value=[$value]<br/>";
	if ($parm['Type'] == 'array') {
		$a = array();
		$sql = "select Value from ONLINE_USER_LOCAL where (Username = '".mysql_real_escape_string($username)."') and (Parameter = '$parameter')"; 
		if (($r = $this->querydb($sql)) && ($rec = mysql_fetch_assoc($r)))
			$a = $this->unserialize($rec['Value']);
		if ($cmd == 'ParameterDelete') {
			$b = array();
			foreach ($a as $v) {
				if ($v != $value)
					$b[count($b)] = $v;
			}
			$value = $this->serialize($ret=$b);
		}
		else {
			if (!in_array($value, $a))
				$a[count($a)] = $value;
			$value = $this->serialize($ret=$a);
		}
		if (count($a))
			$this->querydb("replace into ONLINE_USER_LOCAL (Username, Parameter, Value) values('".mysql_real_escape_string($username)."', '$parameter', '".mysql_real_escape_string($value)."')");
		else
			$this->querydb("delete from ONLINE_USER_LOCAL where (Username = '".mysql_real_escape_string($username)."') and (Parameter = '$parameter')");
	}
	else {
		if ($value > '') {
			$this->querydb("replace into ONLINE_USER_LOCAL (Username, Parameter, Value) values('".mysql_real_escape_string($username)."', '$parameter', '".mysql_real_escape_string($value)."')");
			$ret = $value;
		}
		else
			$this->querydb("delete from ONLINE_USER_LOCAL where (Username = '".mysql_real_escape_string($username)."') and (Parameter = '$parameter')");
	}
//	$this->querydb($sql);
	return($ret);
}


// Bootstrap menu headers and footers
function menu_script_header() {
	return('
    <div class="navbar navbar-fixed-top navbar-inverse" role="navigation">
      <div class="container">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
        </div>
        <div class="collapse navbar-collapse">
');
}

function menu_script_footer() {
	return('
        </div><!-- /.nav-collapse -->
      </div><!-- /.container -->
    </div>
');
}

function parseTextArrayString($l) {
	$b = array();
	$a = explode(';', $l);
	foreach ($a as $v) {
		$v = trim($v);
		if ($v > '')
			$b[count($b)] = $v;
	}
	return($b);
}

function set_archive_perms(&$m, $user) {
	for ($n=0; $n<count($m); $n++) {
		if (count($m[$n]['dballow']) && (!in_array($user["selectedarchive"], $m[$n]['dballow'])))
			$m[$n]['deny'] = true;
		if (in_array($user["selectedarchive"], $m[$n]['dbdeny']))
			$m[$n]['deny'] = true;
		if ($m[$n]['branch'])
			set_archive_perms($m[$n]['branch'], $user);
	}
}

function deny_menu_item(&$m, $menu, $item, $deny) {
	for ($n=0; $n<count($m); $n++) {
		if (($m[$n]['menu'] == $menu) && ($m[$n]['item'] == $item))
			$m[$n]['deny'] = true;
		if ($deny)
			$m[$n]['deny'] = $deny;
		if ($m[$n]['branch'])
			$this->deny_menu_item($m[$n]['branch'], $menu, $item, $m[$n]['deny']);
	}
}

function allow_menu_item(&$m, $menu, $item, $deny) {
	for ($n=0; $n<count($m); $n++) {
		if (($m[$n]['menu'] == $menu) && ($m[$n]['item'] == $item))
			return($m[$n]['deny'] = false);
		else if ($m[$n]['branch'])
			$m[$n]['deny'] = $this->allow_menu_item($m[$n]['branch'], $menu, $item, $m[$n]['deny']);
		if (!$m[$n]['deny'])
			$deny = false;
	}
	return($deny);
}

function load_menu_array($user, $menu) {
	global $realtimeConfig;

	$sql = "select * from ONLINE_USER
		where ((username = '".$user['username']."') and (password = '".$user['password']."'))
		or ((username = '".$user['username']."') and (rectype = 'group'))
	";
	$r = $this->querydb($sql);
	$loggedin = @mysql_num_rows($r);

	// Load all menu items and branches according to the login state
	$m = $this->load_menu_branch($loggedin, $menu);
	
//print "<!--\n";
	if ($loggedin) {
		// Mark all menu items and parent branches which are 'denied' for this user
		// by setting the 'deny' element to true;
		if (is_array($user['menudeny']))
			foreach ($user['menudeny'] as $menudeny) {
//print_r($menudeny);
				list($menu, $item) = explode(':', $menudeny);
				if ($menu && $item)
					$this->deny_menu_item($m, $menu, $item, false);
			}
		// Clear all those menu items and parent branches which are 'allowed' for this user
		// by resetting the 'deny' element to false;
		if (is_array($user['menuallow']))
			foreach ($user['menuallow'] as $menuallow) {
				list($menu, $item) = explode(':', $menuallow);
				if ($menu && $item)
					$this->allow_menu_item($m, $menu, $item, false);
			}
		// Recursively descend the menu tree and set/reset the deny flag
		// for each menu item according to the currently selected archive
		if ($user["selectedarchive"] > '')
			$this->set_archive_perms($m, $user);
	}
//print "\n-->\n";
	return($m);
}

function load_menu_branch($loggedin, $menu) {
	$m = array();
	$sql = "select * from ONLINE_MENU where (menu = '$menu') order by indexno";
	$sql = "select m.*, r.classname 
		from ONLINE_MENU as m 
		left join ONLINE_REPORT as r on (r.classfile = m.url)
		where (m.menu = '$menu') 
		order by m.indexno
	";
	$r = $this->querydb($sql);
	$n = 0;
	while ($rec = mysql_fetch_assoc($r)) {
		if (($loggedin && ($rec['login'] != 'no')) || ((!$loggedin) && ($rec['login'] != 'yes'))) {
			$m[$n] = $rec;
			$m[$n]['deny'] = false;
			$m[$n]['dballow'] = $this->parseTextArrayString($m[$n]['dballow']);
			$m[$n]['dbdeny'] = $this->parseTextArrayString($m[$n]['dbdeny']);
			if ($m[$n]['branch'])
				$m[$n]['branch'] = $this->load_menu_branch($loggedin, $rec['item']);
			$n++;
		}
	}
	return($m);
}
// Milonic menu
function __extract_menu_tree($menu, $mainmenu, $user_rec=array(), $menuok=true, $m=false, $style="top=5;left=6;") {
	global $debug, $user, $realtimeConfig;

	if ($debug)
		print "<br/><br/>";
	$d = $debug;
//	$debug = false;

	$ret = 'with(milonic=new menuname("'.$menu.'")){
		style=menuStyle;
	';
	if ($menu == $mainmenu) {
		$m = $this->load_menu_array($user_rec, $mainmenu);
//		$ret .= "
//			style=mainMenuStyle;
//			alwaysvisible=1;
//			orientation='horizontal';
//		";
		$ret .= "
			style=mainMenuStyle;
			itemwidth=136;
			align='center';
			alwaysvisible=1;
			orientation='vertical';
		";
		$ret .= $style;
	}
//	else $ret .= '
//		style=menuStyle;
//	';

	foreach($m as $rec) {
		if (!$rec['deny']) {
			$ret .= 'aI("';
			$ret .= 'text='.str_replace('"', "&quot;", $rec["text"]).';';
			if ($rec["url"] > "") {
				if ($rec["static"] == 'on')
					$ret .= 'url='.$GLOBALS['realtimeConfig']['ROOT'].'/static/main.php?menu='.$rec["menu"]."/".$rec["item"].';';
				else
					$ret .= 'url='.$rec["url"].';';
			}
			if ($rec["target"] > "")
				$ret .= 'target='.((($_SESSION['user']['singleWindowMode'] == 'on') && ($rec["target"] != '_blank'))?'_self':$rec["target"]).';';
			if ($rec["branch"])
				$ret .= 'showmenu='.$rec["item"].';';
			if ($rec["description"])
				$ret .= 'status='.$rec["description"].';';
			$ret .= '");'."\n";
		}
	}
	$ret .= "}\n";

	// Now rescan the menu and find any branches
	foreach($m as $rec)
		if ($rec["branch"] && (!$rec['deny'])) $ret .= $this->extract_menu_tree($rec["item"], $mainmenu, $user_rec, NULL, $rec["branch"]);
//		if ($rec["branch"] && (!$rec['deny'])) $ret .= extract_menu_tree($rec["item"], $mainmenu, $user_rec, $ok, $rec["branch"]);

	$debug = $d;
	return($ret);
}

	public function build_module_menu($menu, $values) {
		global $user, $realtimeConfig, $debug;

		$d = $debug;
		$m = $this->load_menu_array($_SESSION['user'], $GLOBALS['realtimeConfig']['MAINMENU']);
		if ($menu == $GLOBALS['realtimeConfig']['MAINMENU'])
			$values = $this->build_module_menu_item($m, $values);
		else {
			foreach ($m as $n) {
				if ($n['item'] == $menu) {
					$values = $this->build_module_menu_item($n['branch'], $values);
				}
			}
		}
		$debug = $d;
		return($values);
	}

	public function build_module_menu_item($m, $values) {
		foreach($m as $rec) {
			if ($rec['branch'])
				$values = $this->build_module_menu_item($rec['branch'], $values);
			else if ((!$rec['deny'])&& ($rec['url'] > '')) {
				if ($rec["static"] == 'on')
					$values[$rec['menu'].':'.$rec['item']] = $GLOBALS['realtimeConfig']['ROOT'].'/static/main.php?menu='.$rec["menu"]."/".$rec["item"];
				else
					$values[$rec['menu'].':'.$rec['item']] = $rec['url'];
			}
		}
		return($values);
	}

function _build_module_menu($menu, &$items, &$values) {
	global $user, $realtimeConfig, $debug;

	$d = $debug;
//	$debug = false;

//	$m = load_menu_array($user, $menu);
//	build_module_menu_item($m, $items, $values);
	$m = $this->load_menu_array($_SESSION['user'], $GLOBALS['realtimeConfig']['MAINMENU']);
	if ($menu == $GLOBALS['realtimeConfig']['MAINMENU'])
		$this->build_module_menu_item($m, $items, $values);
	else {
		foreach ($m as $n) {
			if ($n['item'] == $menu) {
				$this->build_module_menu_item($n['branch'], $items, $values);
			}
		}
	}
	$debug = $d;
}

function _build_module_menu_item($m, &$items, &$values) {
	foreach($m as $rec) {
		if ($rec['branch'])
			$this->build_module_menu_item($rec['branch'], $items, $values);
		else if ((!$rec['deny'])&& ($rec['url'] > '')) {
			$items[count($items)] = $rec['description'];
//			$values[count($values)] = $rec['url'];
			if ($rec["static"] == 'on')
				$values[count($values)] = $GLOBALS['realtimeConfig']['ROOT'].'/static/main.php?menu='.$rec["menu"]."/".$rec["item"];
			else
				$values[count($values)] = $rec['url'];
		}
	}
}

function reportClassNameToURL() {
	$data = array();
	$data['url'] = $_REQUEST['classname'];
	$sql = "select classfile from ONLINE_REPORT where classname = '".$_REQUEST['classname']."'";
	if (($r = $this->querydb($sql)) && ($rec = mysql_fetch_assoc($r)))
		$data['url'] = $rec['classfile'];
	$data['debug'] = $this->debugData;
	die(json_encode($data));
}

function drawMainMenu() {
	$this->drawmenu($GLOBALS['realtimeConfig']['MAINMENU'], $_REQUEST['username']);
	print "
		<div title='Debug' id='debugWindow' class='easyui-window' data-options='closed:true'  style='width:600px;height:400px'></div>
		<div id='onlineTrxResult' class='easyui-window' data-options='modal:true,closed:true,collapsible:false,minimizable:false,maximizable:false' style='width:500px;height:200px;padding:0px;'></div>
		<div id='".$this->lookupDetailWindow."' class='easyui-window' title=' ' data-options=\"closed:true,collapsible:false,minimizable:false,maximizable:true
			, modal:true
			, onMove:function(left,top){if (top < 0) $(this).window('move', {top:0, left:left});}
		\" style='width:720px;height:480px;padding:10px;'>
		</div>
		<script>
			function reportFormShow(classname) {
				var win = $('#".$this->lookupDetailWindow."');
				if (win) {
					win.window('clear');
					win.window({href: null});
					win.window('open');
					// If the detail window is nearly as big as the main layout panel
					// then it will be maximised. This works best for phones/tablets
					var h = $('#mainLayout').height();
					var w = $('#mainLayout').width();
					var detail = $('#".$this->lookupDetailWindow."').window('options');
					if ((detail.height > (h*0.9)) || (detail.width > (w*0.9)))
						$('#".$this->lookupDetailWindow."').window('maximize');
					$.ajax({
						dataType: 'json',
						data: {
							cmd: 'reportClassNameToURL',
							classname: classname
						},
						success: function(data){
							win.window('refresh', data.url+'?cmd=reportFormShow&classfile='+data.url);
						}
					});
				}
			}
		</script>
	";
	print "<!--\n";
	print $this->debugData;
	print "\n-->\n";
}

function drawmenu($menu="", $username=null) {
	$userrec = array();
	$group = "";
	if ($username)
		$userrec = $this->retrieve_user_settings($username);
	else
		$userrec = $_SESSION['user'];
//	print $this->menu_script_header();
	if (is_array($menu))
		foreach ($menu as $m => $ypos)
			print $this->extract_menu_tree($m, $m, $userrec, true, false, $ypos);
	else
		print $this->extract_menu_tree($menu, $menu, $userrec);
	print '
		<script>
			function rtMenuHandler(target, url){
				win = window.open(url,target);
				win.focus();
			}
		</script>
	';
//	print $this->menu_script_footer();
}

//jeasyui menubutton menu
function extract_menu_tree($menu, $mainmenu, $user_rec=array(), $menuok=true, $m=false, $style="top=5;left=6;") {
	global $debug, $user, $realtimeConfig;

//	if ($debug)
//		print "<br><br>";
	$d = $debug;
	$debug = false;
	
	$prefix = '';
	if ($user_rec['username'] != $_SESSION['user']['username'])
		$prefix = preg_replace('/[^A-Z]/', '', strtoupper($user_rec['username']));

	if ($menu == $mainmenu) {
		$m = $this->load_menu_array($user_rec, $mainmenu);
		foreach($m as $n => $rec) {
			$target = (($_SESSION['user']['singleWindowMode'] == 'on') && ($rec["target"] != '_blank'))?'_self':$rec['target'];
			if (!$rec['deny']) {
				$onclick = null;
				if ($prefix > '')
					;
				else if ($rec['classname'])
					$onclick = 'reportFormShow("'.$rec['classname'].'")';
				else if ($rec["static"] == 'on')
					$onclick = "rtMenuHandler(\"".$target."\",\"".$GLOBALS['realtimeConfig']['ROOT'].'/static/main.php?menu='.$rec["menu"]."/".$rec["item"]."\");";
				else
					$onclick = "rtMenuHandler(\"".$target."\",\"".$rec["url"]."\");";
				$text = str_replace('"', "&quot;", $rec["text"]);
				$icon = ($rec["icon"] > '')?",iconCls:\"${rec["icon"]}\"":'';
				if ($rec["branch"]) {
					if ($rec['text'] == '$USER') {
						$text = str_replace('"', "&quot;", $user_rec['fullname']);
						$title = str_replace('"', "&quot;", $user_rec['fullname'].' - Logged in since '.$user_rec['lastlogin']);
						$ret .= "<a href='#' id='".$prefix."mainMenuUserSettings' title='${title}' style='/*float:right*/' class='easyui-menubutton' data-options='menu:\"#".$prefix."${rec['menu']}${rec['item']}\"${icon}'>$text</a>\n";
					}
					else
						$ret .= "<a href='#' class='easyui-menubutton' data-options='menu:\"#".$prefix."${rec['menu']}${rec['item']}\"${icon}'>$text</a>\n";
				}
				else if ($onclick)
					$ret .= "<a href='#' class='easyui-linkbutton' onclick='".$onclick."' data-options='plain:true${icon}'>$text</a>\n";
			}
		}
	}
	foreach($m as $n => $rec) {
		$target = (($_SESSION['user']['singleWindowMode'] == 'on') && ($rec["target"] != '_blank'))?'_self':$rec['target'];
		if (!$rec['deny']) {
			$onclick = null;
			if ($prefix > '')
				;
			else if ($rec['classname'])
				$onclick = 'reportFormShow("'.$rec['classname'].'")';
			else if ($rec["static"] == 'on')
				$onclick = "rtMenuHandler(\"".$target."\",\"".$GLOBALS['realtimeConfig']['ROOT'].'/static/main.php?menu='.$rec["menu"]."/".$rec["item"]."\");";
			else
				$onclick = "rtMenuHandler(\"".$target."\",\"".$rec["url"]."\");";
			$text = str_replace('"', "&quot;", $rec["text"]);
			$icon = ($rec["icon"] > '')?"data-options='iconCls:\"${rec["icon"]}\"'":'';
			if ($rec['text'] == '-')
				$ret .= '<div class="menu-sep"></div>';
			else if ($rec["branch"]) {
				if ($menu != $mainmenu)
					$ret .= "<div ${icon} title='${rec["description"]}'><span>$text</span><div>\n";
				else
					$ret .= "<div ${icon} title='${rec["description"]}' id='".$prefix."${rec['menu']}${rec['item']}'>\n";
				$ret .= $this->extract_menu_tree($rec["item"], $mainmenu, $user_rec, NULL, $rec["branch"]);
				if ($menu != $mainmenu)
					$ret .= "</div>\n";
				$ret .= "</div>\n";
			}
			else if ($menu != $mainmenu) {
				$ret .= "<div ${icon} title='${rec["description"]}'";
				if ($onclick)
						$ret .= " href='#' onclick='".$onclick."'";
				$ret .= ">$text</div>\n";
			}
		}
	}
	$debug = $d;
	return($ret);
}

// Bootstrap navbar menu
function _extract_menu_tree($menu, $mainmenu, $user_rec=array(), $menuok=true, $m=false, $style="top=5;left=6;") {
	global $debug, $user, $realtimeConfig;

//	if ($debug)
//		print "<br><br>";
	$d = $debug;
	$debug = false;

	if ($menu == $mainmenu) {
		$m = $this->load_menu_array($user_rec, $mainmenu);
		$ret .= '<ul class="nav navbar-nav">';
	}
//	else
 //             $ret .= '<ul class="dropdown-menu">';

	foreach($m as $n => $rec) {
		if (!$rec['deny']) {
			$text = str_replace('"', "&quot;", $rec["text"]);
			if ($rec["branch"]) {
				if ($menu == $mainmenu) {
					$ret .= '<li class="dropdown">
						      <a href="#" class="dropdown-toggle" data-toggle="dropdown">'.$text.'<b class="caret"></b></a>
					';
					$ret .= '<ul class="dropdown-menu">
					';
				}
				else {
					if ($n)
						$ret .= '<li class="divider"></li>';
					$ret .= '<li class="dropdown-header">'.$text.'</li>';
				}
				$ret .= $this->extract_menu_tree($rec["item"], $mainmenu, $user_rec, NULL, $rec["branch"]);
				if ($menu == $mainmenu)
					$ret .= '</ul></li>
					';
			}
			else {
				$ret .= '<li title="'.$rec["description"].'">
				';
				if ($rec["url"] > "") {
					if ($rec["static"] == 'on')
						$ret .= '<a href="'.$GLOBALS['realtimeConfig']['ROOT'].'/static/main.php?menu='.$rec["menu"]."/".$rec["item"].'">
						';
					else
						$ret .= '<a href="'.$rec["url"].'">
						';
				}
				$ret .= $text;
				if ($rec["url"] > "")
					$ret .= '</a>
					';
			$ret .= '</li>
			';
			}
		}
	}
	if ($menu == $mainmenu)
		$ret .= '</ul>
';


	$debug = $d;
	return($ret);
}

function periodSelect($select, $name, $action='') {
	print "<tr>
		<td>Period:</td>
		</tr>
		<tr>
		<td>
	";
	$assoc = array(
			"TYR" => "This year",
			"LYR" => "Last year",
			"PYR" => "Previous year",
			"L12" => "Last 12 months",
			"MTH" => "Month range",
		);
	print $this->comboBoxAssoc($assoc, $select, $name, $action);
	print "</td>
		</tr>
	";

}

function htmlSQLComboBox($sql, $select, $name, $options=null, $classFile='easyui-combobox') {
	$assoc = array();
	if ($r = $this->querydb($sql))
		while ($rec = mysql_fetch_assoc($r)) {
			$assoc[] = $rec;
		}
	return($this->htmlComboBox($assoc, $select, $name, $options, $classFile));
}

function _encodeOptions($level, $options) {
	$comma = '';
	if ($level == 1)
		$data .= '[[';
	else if ($level == 2)
		$data .= '{';
//	if (is_array($options)) {
		foreach ($options as $value => $item) {
			if (is_array($item)) {
				if ($level == 1)
					$data .= $comma.$this->encodeOptions($level+1, $item);
				else
					$data .= $comma."\n$value:".$this->encodeOptions($level+1, $item);
			}
			else if ((substr_compare('function(', $item, 0, 9) == 0) || (substr_compare('[[', $item, 0, 2) == 0))
				$data .= $comma."\n$value:$item";
			else if (is_string($item))
				$data .= $comma."\n$value:'".str_replace('"', '&#34;', $item)."'";
			else if (is_bool($item))
				$data .= $comma."\n$value:".($item?'true':'false')."";
			else
				$data .= $comma."\n$value:$item";
			$comma = ',';
		}
//	}
	if ($level == 1)
		$data .= ']]';
	else if ($level == 2)
		$data .= '}';
	return($data);
}

// Convert an array into a list of easyui data-options
// and return them appended then to the passed $str
function arrayToDataOptions($options, $str='') {
	$comma = ($str > '')?',':'';
	if (is_array($options)) {
		foreach ($options as $value => $item) {
			if (is_int($value))
				$value = '';
			else
				$value .= ':';
			if (is_array($item)) {	// Allow for nested objects/arrays
				if ($item[0] > '')
					$str .= $comma."\n${value}[".$this->arrayToDataOptions($item, '')."]";	// indexed array
				else
					$str .= $comma."\n${value}{".$this->arrayToDataOptions($item, '')."}";	// associative array
			}
			else if (substr_compare('function(', $item, 0, 9) == 0)
				$str .= $comma."\n${value}$item";
			else if (substr_compare('~~', $item, 0, 2) == 0)		// Any item prefixed with two tilde is to be treated as a literal object/name
				$str .= $comma."\n${value}".substr($item, 2)."";
			else if (substr_compare('[[', $item, 0, 2) == 0)
				$str .= $comma."\n${value}$item";
			else if (substr_compare('['.chr(123), $item, 0, 2) == 0)		// '[{' Open square bracket and open curly bracket
				$str .= $comma."\n${value}$item";
			else if (is_string($item))
				$str .= $comma."\n${value}'".str_replace('"', '&#34;', $item)."'";
			else if (is_bool($item))
				$str .= $comma."\n${value}".($item?'true':'false')."";
			else
				$str .= $comma."\n${value}$item";
			$comma = ',';
		}
	}
	return($str);
}

	function menuIncudeOptions($options, $updateFunction=null, $title='Include:', $switchStates=null) {
		print $this->htmlTableOpen("style='padding:4px;'");

		if ($title) {
			print $this->htmlTableRowOpen();
			print $this->htmlTableCell(2, $title, "style='padding-top: 5px;'");
			print $this->htmlTableRowClose();
		}
		if (!$updateFunction)
			$updateFunction = "\$('#mainLayout').layout('panel', 'center').panel('refresh','".$this->realtimeDataClassFile."?cmd=".$this->resultDisplayHandler."')";
			
		$onchange = "updateSessionData('".$this->realtimeDataClassFile."', {\n";
		$comma = '';
		foreach ($options as $k => $name) {
			$onchange .= $comma.' '.$k.": $('#$k').is(':checked')\n";
			$comma = ',';
		}
		$onchange .= "}\n, function(){ $updateFunction; });\n";
		foreach ($options as $k => $name) {
			$hint = $name[1];
			if ($switchStates) {
				$options = $this->arrayToDataOptions(array('onChange' => "function(checked){updateSessionData('".$this->realtimeDataClassFile."', {".$k.": checked}, function(){".$updateFunction.";})}"));
				$options = $this->arrayToDataOptions($switchStates, $options);
				$cell = "<input title='".$hint."' class='easyui-switchbutton' ".($this->$k == 'on'?'checked':'')." data-options=\"".$options."\">".'&nbsp;&nbsp;'.$name[0];
			}
			else {
//				$attr = 'onchange="'.$onchange.'"';
				$attr = "onchange=\"updateSessionData('".$this->realtimeDataClassFile."', {".$k.": $('#$k').is(':checked')}, function(){ $updateFunction; });\"";
				$cell = $this->htmlCheckBox($k, $this->$k, $attr).' '.$name[0];
			}
			print $this->htmlTableRowOpen();
			print $this->htmlTableCell(2, $cell, "title='$hint' style='padding-top: 5px;' ");
			print $this->htmlTableRowClose();
		}

		print $this->htmlTableClose();
		
	}

	function monthRangeSelect($fromID, $toID, $updateFunction=null) {
		$innerHTML = $this->htmlTableOpen();
		$innerHTML .= $this->htmlTableRowOpen();
		$innerHTML .= $this->htmlTableCell(1, 'From:', "align='left' width='40%'");
		$sql = "select PERIOD, LONGNAME, concat('Financial year ',FY) as FY from OPT_CALENDAR order by PERIOD";
		if (!$updateFunction)
			$updateFunction = "$('#searchResults0').datagrid('reload')";
		$options = array(
			'valueField'	=> 'PERIOD',
			'textField'	=> 'LONGNAME',
			'editable'	=> false,
			'groupField'	=> 'FY',
			'onSelect'	=> "function(rec) {
				updateSessionData('".$this->realtimeDataClassFile."', {
					monthFromSelect: $('#monthFromSelect').combobox('getValue'),
					monthToSelect: $('#monthToSelect').combobox('getValue')
				}, function(){ $updateFunction; });
			}",
		);
		$cell = $this->htmlSQLComboBox($sql, $this->$fromID, $fromID, $options);
		$innerHTML .= $this->htmlTableCell(1, $cell);
		$innerHTML .= $this->htmlTableRowClose();
		$innerHTML .= $this->htmlTableRowOpen();
		$innerHTML .= $this->htmlTableCell(1, 'To:', "align='left' width='40%'");
		$cell = $this->htmlSQLComboBox($sql, $this->$toID, $toID, $options);
		$innerHTML .= $this->htmlTableCell(1, $cell);
		$innerHTML .= $this->htmlTableRowClose();
		$innerHTML .= $this->htmlTableClose();
		return($innerHTML);
	}

	function dateRangeSelect($fromID, $toID, $updateFunction=null) {
		$innerHTML = $this->htmlTableOpen();
		$innerHTML .= $this->htmlTableRowOpen();
		$innerHTML .= $this->htmlTableCell(1, 'From:', "align='left' width='40%'");
		if (!$updateFunction)
			$updateFunction = "$('#searchResults0').datagrid('reload')";
		$options = array(
//			'editable'	=> false,
			'formatter'	=> "function(date){
					var mon = new Array('Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec');
					var y = date.getFullYear();
					var m = date.getMonth();
					var d = date.getDate();
					return d+' '+mon[m]+' '+y;
				}
			",
			'parser'	=> "function(s) {
					var t = Date.parse(s);
					if (isNaN(t))
						return new Date();
					else 
						return new Date(t);
				}
			",
			'onSelect'	=> "function(date) {
				updateSessionData('".$this->realtimeDataClassFile."', {
					dateFromSelect: $('#dateFromSelect').datebox('getValue'),
					dateToSelect: $('#dateToSelect').datebox('getValue')
				}, function(){ $updateFunction; });
			}",
		);
		$innerHTML .= $this->htmlTableCell(1, $this->htmlInputBox($this->$fromID, $fromID, $options, 'easyui-datebox'));
		$innerHTML .= $this->htmlTableRowClose();
		$innerHTML .= $this->htmlTableRowOpen();
		$innerHTML .= $this->htmlTableCell(1, 'To:', "align='left' width='40%'");
		$innerHTML .= $this->htmlTableCell(1, $this->htmlInputBox($this->$toID,  $toID, $options, 'easyui-datebox'));
		$innerHTML .= $this->htmlTableRowClose();
		$innerHTML .= $this->htmlTableClose();
		return($innerHTML);
	}

	function menuPeriod($data = null, $updateFunction=null, $title="Period:") {
		print $this->htmlTableOpen("style='padding:4px;'");

		print $this->htmlTableRowOpen();
		print $this->htmlTableCell(2, $title);
		print $this->htmlTableRowClose();
		
		print $this->htmlTableRowOpen();
		
		if (!$updateFunction)
			$updateFunction = "$('#searchResults0').datagrid('reload')";
//			$updateFunction = "\$('#mainLayout').layout('panel', 'center').panel('refresh','".$this->realtimeDataClassFile."?cmd=".$this->resultDisplayHandler."')";
		if (!$data)
			$data = array(
				array('id' => 'TYR', 'text' => 'This year'),
//				array('id' => 'TYC', 'text' => 'This year (closed months)'),
				array('id' => 'LYR', 'text' => 'Last year'),
				array('id' => 'PYR', 'text' => 'Previous year'),
				array('id' => 'L12', 'text' => 'Last 12 months'),
				array('id' => 'YTD', 'text' => 'Calendar YTD'),
				array('id' => 'CUR', 'text' => 'Current month'),
				array('id' => 'LMN', 'text' => 'Last month'),
				array('id' => 'MNT', 'text' => 'Month range'),
				array('id' => 'DAY', 'text' => 'Date range'),
			);

		$options = array(
			'width'	=> 221,
			'valueField'	=> 'id',
			'textField'	=> 'text',
			'editable'	=> false,
			'panelHeight'	=> 'auto',

			'onSelect'	=> "function(rec) {
				$('#monthRange').panel('close');
				$('#dateRange').panel('close');
				if (rec.id == 'MNT')
					$('#monthRange').panel('open');
				else if (rec.id == 'DAY')
					$('#dateRange').panel('open');
				updateSessionData('".$this->realtimeDataClassFile."', {
					periodSelect: $('#periodSelect').combobox('getValue')
				}, function(){ $updateFunction; });
			}",
		);

		$cell = $this->htmlComboBox($data, $this->settings['periodSelect'], 'periodSelect', $options);
		print $this->htmlTableCell(2, $cell);
		print $this->htmlTableRowClose();
	
		print $this->htmlTableClose();

		$closed = $this->periodSelect == 'MNT'?'false':'true';
		print "<div id='monthRange' class='easyui-panel' data-options='border:false,noheader:true,closed:$closed'>".$this->monthRangeSelect('monthFromSelect', 'monthToSelect', $updateFunction).'</div>';
		$closed = $this->periodSelect == 'DAY'?'false':'true';
		print "<div id='dateRange' class='easyui-panel' data-options='border:false,noheader:true,closed:$closed'>".$this->dateRangeSelect('dateFromSelect', 'dateToSelect', $updateFunction).'</div>';
		
	}

function htmlComboBox($assoc, $select, $name, $options=null, $classFile='easyui-combobox', $initialText='') {
	$data = $comma = '';
	if (is_array($assoc)) {
		if ($assoc[0]['group'] > '')
			$options['groupField'] = 'group';
		$data = 'data:[';
		foreach ($assoc as $rec) {
			$sep = $comma."{\n\t";
			foreach ($rec as $k => $v) {
				$data .= $sep."$k:'".str_replace("'", "\\'", $v)."'\n";
				$sep = "\t,";
				$comma = ',';
				if (($k == $options['valueField']) && $options['multiple'] && in_array($v, $select))
					$data .= $sep."selected:true\n";
				else if (($k == $options['valueField']) && ($v == $select))
					$data .= $sep."selected:true\n";
			}
			$data .= "}\n";
		}
		$data .= ']';
	}

	$data = $this->arrayToDataOptions($options, $data);
	$ret = "<input class='$classFile' id='$name' name='$name' value=\"".str_replace('"', "&quote;'", $initialText)."\" data-options=\"$data\">\n";
	return($ret);
}

	function sessionUpdate() {
		$searchForComboAppend = false;
		$searchForComboRemove = false;
		foreach ($_REQUEST as $k => $v) {
			if ($k == 'searchForComboAppend')
				$searchForComboAppend = ($v == 'true');
			else if ($k == 'searchForComboRemove')
				$searchForComboRemove = ($v == 'true');
//			else if ($k == 'debug')
//				print_r($_SESSION);
			else if (($k != 'cmd') && ($k != 'PHPSESSID')) {
				if ($v == 'false')
					$v = false;
				if ($v == 'true')
					$v = true;
				if ($searchForComboAppend && is_array($v) && is_array($_SESSION[$this->realtimeDataClassFile][$k])) {
					foreach($v as $val)
						if (array_search($val, $_SESSION[$this->realtimeDataClassFile][$k]) === false)
							array_push($_SESSION[$this->realtimeDataClassFile][$k], $val);
				}
				else if ($searchForComboRemove && is_array($_SESSION[$this->realtimeDataClassFile][$k])) {
					while (($p = array_search($v, $_SESSION[$this->realtimeDataClassFile][$k])) !== false)
						array_splice($_SESSION[$this->realtimeDataClassFile][$k], $p, 1);
				}
				else if ($_SESSION[$this->realtimeDataClassFile][$k] !== $v)
					$_SESSION[$this->realtimeDataClassFile][$k] = $v;
			}
		}
		die(json_encode($_SESSION[$this->realtimeDataClassFile]));
	}

	function menuCombo($title, $name, $data, $updateFunction, $classFile='easyui-combobox', $options=array()) {
		print $this->htmlTableOpen("style='padding:4px;'");

		print $this->htmlTableRowOpen();
		print $this->htmlTableCell(2, $title);
		print $this->htmlTableRowClose();
		
		print $this->htmlTableRowOpen();
		
		$options['width']	= 221;
		$options['valueField']	= 'id';
		$options['textField']	= 'text';
		if (!$options['editable'])
			$options['editable'] = false;
		$options['panelHeight']	= '200';
		// multiple as easyui-combogrid not working yet, so use easyui-combobox instead
		if ($options['multiple']) {
			if (!isset($options['onSelect']))
				$options['onSelect'] = "function(rec) {
					var v = $(this).combobox('getValues');
					updateSessionData('".$this->realtimeDataClassFile."', {
						$name: (v.length == 0)?null:v
					}, function(){ $updateFunction; });
				}";
			if (!isset($options['onUnselect']))
				$options['onUnselect'] = $options['onSelect'];
//			$options['onSelectAll'] = $options['onSelect'];
//			$options['onUnselectAll'] = $options['onSelect'];
			$options['fitColumns'] = true;
			$options['idField'] = 'id';
//			$options['multiple'] = true;
			if ($classFile != 'easyui-combobox') {
				$options['columns'] = "[[{field:'ck',checkbox:true},{field:'text',title:'Select/unselect all',width:120}]]";
				$options['panelWidth'] = 300;
			}
//			$options['panelHeight'] = 200;
			$cell = $this->htmlComboBox($data, $this->$name, $name, $options, $classFile);
		}
		else {
			if (!isset($options['onSelect']))
				$options['onSelect']	= "function(rec) {
					updateSessionData('".$this->realtimeDataClassFile."', {
						$name: $('#$name').combobox('getValue')
					}, function(){ $updateFunction; });
				}";
			$cell = $this->htmlComboBox($data, $this->$name, $name, $options, $classFile);
		}
		print $this->htmlTableCell(2, $cell);
		print $this->htmlTableRowClose();
	
		print $this->htmlTableClose();
	}

function htmlInputBox($value, $name, $options=null, $classFile='easyui-validatebox', $attr='') {
	$options['value'] = $value;
	$data = '';
	$data = $this->arrayToDataOptions($options, $data);
	$ret = "<input class='$classFile' id='$name' name='$name' data-options=\"$data\" $attr>\n";
	return($ret);
}

function htmlTableOpen($attr='') {
	return("<table $attr>\n");
}

function htmlTableClose() {
	return("</table>\n");
}

function htmlTableRowOpen($attr='') {
	return("<tr $attr>\n");
}

function htmlTableRowClose() {
	return("</tr>\n");
}

function htmlTableCell($colspan=1, $field, $attr='') {
	if ($field == "")
		$field = "&nbsp;";
	if ($colspan > 1)
		$td = "<td colspan='$colspan'";
	else
		$td = "<td";
	if ($attr > '')
		$td .= ' '.$attr;
	return($td.">".$field."</td>\n");
}

function htmlCheckBox($name, $value, $attr=null, $disabled=false) {
	if ($disabled > '')
		$onchange .= ' DISABLED';
	if ($value == 'on')
		return("<INPUT TYPE='checkbox' ID='$name' NAME='$name' CHECKED $attr>");
	else
		return("<INPUT TYPE='checkbox' ID='$name' NAME='$name' $attr>");
}

	function moveDashboardPanel() {
		$panel = $_REQUEST['panel'];
		$userid = $_REQUEST['userid'];
		$dashboardLayout = unserialize($_SESSION['user']['dashboardLayout']);
		$newLayout = array();
		asort($panel);
		$column = $left = 0;
		foreach ($panel as $n => $p) {
			if ($p['left'] > $left) {
				$left = $p['left'];
				$column++;
			}
			$newLayout[$n-1] = $dashboardLayout[$n-1];
			$newLayout[$n-1]['column'] = $column;
		}
		$_SESSION['user']['dashboardLayout'] = serialize($newLayout);
		if ($_SESSION['user']['dashboardCanAlter'] == 'on')
			$this->useradmin_save_user_settings_query('Save', $userid, 'dashboardLayout', serialize($newLayout));
	}

	function resizeDashboardPanel() {
		$panel = $_REQUEST['panel'];
		$height = $_REQUEST['height'];
		$userid = $_REQUEST['userid'];
		$dashboardLayout = unserialize($_SESSION['user']['dashboardLayout']);
		$dashboardLayout[$panel-1]['height'] = $height+30;
		$_SESSION['user']['dashboardLayout'] = serialize($dashboardLayout);
		if ($_SESSION['user']['dashboardCanAlter'] == 'on')
			$this->useradmin_save_user_settings_query('Save', $userid, 'dashboardLayout', serialize($dashboardLayout));
	}

	function removeDashboardPanel() {
		$panel = $_REQUEST['panel'];
		$userid = $_REQUEST['userid'];
		$dashboardLayout = unserialize($_SESSION['user']['dashboardLayout']);
//print_r($dashboardLayout);
//		unset($dashboardLayout[$panel-1]);
		$newLayout = array();
		foreach($dashboardLayout as $n => $p)
			if ($n != ($panel - 1))
				$newLayout[] = $p;

//print_r($dashboardLayout);
		$_SESSION['user']['dashboardLayout'] = serialize($newLayout);
//$GLOBALS['debug'] = true;
		$this->useradmin_save_user_settings_query('Save', $userid, 'dashboardLayout', serialize($newLayout));
	}
	
	function onlineTrxMessage() {
		$sql = "select * from ONLINE_TRX_HISTORY where ID = '".$_REQUEST['onlineTrxID']."'";
		if (($r = $this->querydb($sql)) && ($rec = mysql_fetch_assoc($r))) {
			$color = array(
				'unknown' => '#d0d0ff',	// Mauve
				'success' => '#d0ffd0',// Green
				'notice' => '#d0d0d0',	// Grey
				'error' => '#ffd0d0',	// Red
				'failure' => '#ffffd0',	// Yellow
				'warning' => '#d0d0ff',	// Blue
			);
			$result = array(
				'unknown' => 'Unknown/incomplete result.',
				'notice' => 'OK. Please note the message below.',
				'success' => 'Success. Close this window to continue.',
				'error' => 'Error: The transaction resulted in an error.',
				'failure' => 'Failure: The transaction failed.<br>See the message below.',
				'warning' => 'Warning: The transaction completed with the following message.',
			);
			$content = $this->htmlTableOpen("style='width: 100%; height: 100%; color:black; background-color:".$color[$rec['RESULT']].";'");
		
			$content .= $this->htmlTableRowOpen("", '');
			$content .= $this->htmlTableCell(1, '', 'width:25%');
			$content .= $this->htmlTableCell(1, '', 'width:75%');
			$content .= $this->htmlTableRowClose();

			$content .= $this->htmlTableRowOpen("");
			$content .= $this->htmlTableCell(1, 'Result :', 'align=right valign=top');
			$content .= $this->htmlTableCell(1, $result[$rec['RESULT']], "style='font-weight: bold;' valign=top");
			$content .= $this->htmlTableRowClose();

			$content .= $this->htmlTableRowOpen("");
			$content .= $this->htmlTableCell(1, 'Transaction :', 'align=right valign=top');
			$content .= $this->htmlTableCell(1, $rec['REFTYPE'], "style='font-weight: bold;' valign=top");
			$content .= $this->htmlTableRowClose();

			$content .= $this->htmlTableRowOpen("");
			$content .= $this->htmlTableCell(1, 'Reference :', 'align=right valign=top');
			$content .= $this->htmlTableCell(1, $rec['REFCODE'], "style='font-weight: bold;' valign=top");
			$content .= $this->htmlTableRowClose();

			$content .= $this->htmlTableRowOpen("");
			$content .= $this->htmlTableCell(1, 'Message :', 'align=right valign=top');
			$content .= $this->htmlTableCell(1, $rec['MESSAGE'], "style='font-weight: bold;' valign=top");
			$content .= $this->htmlTableRowClose();
		
			$content .= $this->htmlTableRowOpen("", '');
			$content .= $this->htmlTableCell(2, '');
			$content .= $this->htmlTableRowClose();

			$content .= $this->htmlTableClose();
			print $content;
		}
	}

	function advancedSettingsMenuButton($label, $hint, $icon, $action, $spacing=10, $id='btn', $options=array()) {
		if ($spacing > 0)
			print "<div style='height:".$spacing."px;'/>";
		$options['iconCls'] = $icon;
		print "<a id='$id' title='".$hint."' href='#' class='easyui-linkbutton' style='width:221px;text-align: left;'
			data-options=\"".$this->arrayToDataOptions($options)."\" onclick='".$action."'>&nbsp;&nbsp;".$label."</a>";
	}

	function advancedSettingsMenu($allowDashboard=true) {
		$hint = 'The current settings will be permanent and will apply when you next log in';
		$this->advancedSettingsMenuButton('Make these my default', $hint, 'icon-save', 'javascript:advancedSettingsButton("savePermanentSettings", 0, "Make these settings permanent?");');
		
		$hint = 'Restore all settings to the default. Current settings will be lost';
		$this->advancedSettingsMenuButton('Restore default settings', $hint, 'icon-undo', 'javascript:advancedSettingsButton("restoreDefaultSettings", 1, "Restore default settings?");');

		if ($allowDashboard && ($_SESSION['user']['dashboardCanAlter'] == 'on')) {
			$hint = 'The current results will be added to the dashboard in a new window';
			$this->advancedSettingsMenuButton('Add this to the dashboard', $hint, 'icon-add', 'javascript:advancedSettingsButton("addToDashboardLayout", 0, "Add this view to the dashboard?");');
		}
	}

	function savePermanentSettings() {
		$permamentSettings = unserialize($_SESSION['user']['permamentSettings']);
		$permamentSettings[$this->realtimeDataClassFile] = $_SESSION[$this->realtimeDataClassFile];
		$this->useradmin_save_user_settings_query('Save', $_SESSION['user']['username'], 'permamentSettings', serialize($permamentSettings));
	}

	function restoreDefaultSettings() {
		$permamentSettings = unserialize($_SESSION['user']['permamentSettings']);
		unset($permamentSettings[$this->realtimeDataClassFile]);
		$_SESSION['user']['permamentSettings'] = serialize($permamentSettings);
		unset($_SESSION[$this->realtimeDataClassFile]);
		$this->useradmin_save_user_settings_query('Save', $_SESSION['user']['username'], 'permamentSettings', serialize($permamentSettings));
	}
	
	function addToDashboardLayout() {
		$dashboardLayout = unserialize($_SESSION['user']['dashboardLayout']);
		$layout = array();
		$layout['url'] = $this->realtimeDataClassFile;
		$layout['cmd'] = $this->resultDisplayHandler;
		$layout['height'] = 320;
		$layout['refreshInterval'] = 0;
		$layout['name'] = $this->title;
		$layout['data'] = $_SESSION[$this->realtimeDataClassFile];
		$dashboardLayout[] = $layout;
		$_SESSION['user']['dashboardLayout'] = serialize($dashboardLayout);
		$this->useradmin_save_user_settings_query('Save', $_SESSION['user']['username'], 'dashboardLayout', serialize($dashboardLayout));
	}

	function getPeriodDesc($d) {
		if (!$this->periodCache[$d]) {
			$sql = "select LONGNAME from OPT_CALENDAR where PERIOD = '$d'";
			if (($r = $this->querydb($sql)) && ($rec = mysql_fetch_row($r)))
				$this->periodCache[$d] = $rec[0];
		}
		return($this->periodCache[$d]);
	}

	function downloadResults() {
		$filename = $_REQUEST['filename'];
		$attachment = $_REQUEST['attachment']?$_REQUEST['attachment']:basename($filename);
		if (dirname($filename) == $this->downloadDirectory) {
			if ($fp = fopen($filename, 'r')) {
				header("Cache-control: private"); // fix for IE
				if (stristr($filename, '.xlsx'))
					header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
				else if (stristr($filename, '.txt'))
					header("Content-Type: text/plain");
				else if (stristr($filename, '.pdf'))
					header("Content-Type: application/pdf");
				else if (stristr($filename, '.html')) {
					fpassthru($fp);
					die();
				}
				else
					header("Content-Type: application/vnd.ms-excel");
				header('Content-Disposition: attachment; filename="'.$attachment.'"');
				fpassthru($fp);
			}
			if ($this->downloadDelete)
				unlink($filename);
			else
				@chmod($filename, 0666);
		}
	}

	// Either update the progress or return the current progress.
	function downloadProgressUpdate($progress=null) {
		$data = array('downloadProgressValue' => 1, 'downloadProgressText' => 'Starting');
		$sql = "select progress from ONLINE_USER where username = '".$_SESSION['user']['username']."'";
		if (($r = $this->querydb($sql)) && ($rec = mysql_fetch_assoc($r)) && ($d = json_decode($rec['progress'])))
			$data = (array)$d;
		if ($progress) {
			foreach($progress as $k => $v)
				$data[$k] = $v;
			$this->querydb("update ONLINE_USER set progress = '".mysql_real_escape_string(json_encode($data))."' where username = '".$_SESSION['user']['username']."'");
		}
		else
			die(json_encode($data));
	}

	function downloadProgress() {
		print '
			<table style="width: 100%; height: 100%; background-color:#ffd0d0;">
				<tr>
					<td align="center">
						<p id="downloadProgressText">&nbsp;</p>
					</td>
				</tr>
				<tr>
					<td align="left" valign="middle" style="padding-left:50px; padding-right:50px">
						<div id="downloadProgressValue" class="easyui-progressbar" style="width:100%; background-color:#ffffff;"></div>
					</td>
				</tr>
			</table>
		';
		$this->downloadProgressUpdate(array('downloadProgressText' => 'Starting...', 'downloadProgressValue' => 1));
	}

	function output_password_change($user, $error) {
		print htmlOutput_TableOpen("DetailData");
		print htmlOutput_TableRowOpen("salesHeaderRow");
		print htmlOutput_TableCell(3, "salesMenuTitle", "To change your password, enter your existing and and new password", "align=center");
		print htmlOutput_TableRowClose();

		print htmlOutput_TableRowOpen("");
		print htmlOutput_TableCell(1, "", "", "width=35%");
		print htmlOutput_TableCell(1, "", "", "width=55%");
		print htmlOutput_TableCell(1,"","");
		print htmlOutput_TableRowClose();

		if ($error > '') {
			print htmlOutput_TableRowOpen("");
			print htmlOutput_TableCell(1, "", "");
			print htmlOutput_TableCell(1, "inputField", $error);
			print htmlOutput_TableRowClose();
		}

		$instructions = "Enter your existing password and your new password twice.<br>";
		$instructions .= "Use the <b>[Tab]</b> key to move between fields and buttons.<br>";
		$instructions .= "Click the <b>[Confirm]</b> button to proceed.<br>";
		$instructions .= "Otherwise click <b>[Clear]</b> button to start again</b>.<br>";
		print htmlOutput_TableRowOpen("");
		print htmlOutput_TableCell(1, "", "");
		print htmlOutput_TableCell(1, "inputFieldValue", "<br>".$instructions."<br>");
		print htmlOutput_TableRowClose();

		print htmlOutput_TableRowOpen("");
		print htmlOutput_TableCell(1, "inputFieldTitle", "User:");
		print htmlOutput_TableCell(1, "inputField", $user['username']);
		print htmlOutput_TableRowClose();
		print htmlOutput_TableRowOpen("");
		print htmlOutput_TableCell(1, "inputFieldTitle", "Fullname:");
		print htmlOutput_TableCell(1, "inputField", $user['fullname']);
		print htmlOutput_TableRowClose();
		print htmlOutput_TableRowOpen("");
		print htmlOutput_TableCell(1, "inputFieldTitle", "Enter your existing password:");
		print htmlOutput_TableCell(1, "inputFieldValue", htmlOutput_InputFieldWidth("password1", "inputFieldValue", "", 100, "password"));
		print htmlOutput_TableRowClose();
		print htmlOutput_TableRowOpen("");
		print htmlOutput_TableCell(1, "inputFieldTitle", "Enter new password:");
		print htmlOutput_TableCell(1, "inputFieldValue", htmlOutput_InputFieldWidth("password2", "inputFieldValue", "", 100, "password"));
		print htmlOutput_TableRowClose();
		print htmlOutput_TableRowOpen("");
		print htmlOutput_TableCell(1, "inputFieldTitle", "Confirm new password:");
		print htmlOutput_TableCell(1, "inputFieldValue", htmlOutput_InputFieldWidth("password3", "inputFieldValue", "", 100, "password"));
		print htmlOutput_TableRowClose();
		print htmlOutput_TableRowOpen("");
		print htmlOutput_TableCell(1, "", "");
		$button = htmlOutput_ButtonField("", "inputFormButton", "Confirm");
		$button .= "&nbsp;".htmlOutput_ButtonField("", "inputFormButton", "Clear", "javascript:page('');");
		print htmlOutput_TableCell(1, "inputFieldValue", $button);
		print htmlOutput_TableRowClose();

		print htmlOutput_TableClose();
	}

	function output_login($error) {
		print htmlOutput_TableOpen("DetailData");
		print htmlOutput_TableRowOpen("salesHeaderRow");
		print htmlOutput_TableCell(3, "salesMenuTitle", "To login, enter your username and password", "align=center");
		print htmlOutput_TableRowClose();

		print htmlOutput_TableRowOpen("");
		print htmlOutput_TableCell(1, "", "", "width=35%");
		print htmlOutput_TableCell(1, "", "", "width=55%");
		print htmlOutput_TableCell(1,"","");
		print htmlOutput_TableRowClose();

		if ($error > '') {
			print htmlOutput_TableRowOpen("");
			print htmlOutput_TableCell(1, "", "");
			print htmlOutput_TableCell(1, "inputField", $error);
			print htmlOutput_TableRowClose();
		}

		if ((CMG_GLOBAL) || (ERP_ARCIVE))
			$instructions = "Enter your SSO number as your username, and enter your password.<br>";
		else
			$instructions = "Enter your username (firstname.lastname), and enter your password.<br>";
		$instructions .= "Use the <b>[Tab]</b> keys to move between fields and buttons.<br>";
		$instructions .= "Click the <b>[Login]</b> button to proceed.<br>";
		$instructions .= "Otherwise click <b>[Clear]</b> button to start again</b>.<br>";
		print htmlOutput_TableRowOpen("");
		print htmlOutput_TableCell(1, "", "");
		print htmlOutput_TableCell(1, "inputFieldValue", "<br>".$instructions."<br>");
		print htmlOutput_TableRowClose();

		print htmlOutput_TableRowOpen("");
		print htmlOutput_TableCell(1, "inputFieldTitle", "Username:");
		print htmlOutput_TableCell(1, "inputFieldValue", htmlOutput_InputFieldWidth("username", "inputFieldValue", "", 100));
		print htmlOutput_TableRowClose();
		print htmlOutput_TableRowOpen("");
		print htmlOutput_TableCell(1, "inputFieldTitle", "Password:");
		print htmlOutput_TableCell(1, "inputFieldValue", htmlOutput_InputFieldWidth("password", "inputFieldValue", "", 100, "password"));
		print htmlOutput_TableRowClose();
		print htmlOutput_TableRowOpen("");
		print htmlOutput_TableCell(1, "", "");

//$button = "<INPUT TYPE='HIDDEN' NAME='cmd' VALUE='Login'><INPUT TYPE=submit NAME='Login' VALUE='Login' class='inputFormButton'>";
//		$button = htmlOutput_ButtonField("", "inputFormButton", "Login", "javascript:page('Login');");
		$button = htmlOutput_ButtonField("", "inputFormButton", "Login");
		$button .= "&nbsp;".htmlOutput_ButtonField("", "inputFormButton", "Clear", "javascript:page('');");
		print htmlOutput_TableCell(1, "inputFieldValue", $button);
		print htmlOutput_TableRowClose();

		$instructions  = "If you have forgotton your password, you can be issued.<br>";
		$instructions .= "with a new one by clicking the <b>[Lost password]</b> button below.<br>";
		print htmlOutput_TableRowOpen("");
		print htmlOutput_TableCell(1, "", "");
		print htmlOutput_TableCell(1, "inputFieldValue", "<br>".$instructions."<br>");
		print htmlOutput_TableRowClose();

		print htmlOutput_TableRowOpen("");
		print htmlOutput_TableCell(1, "", "");
//		$button = htmlOutput_ButtonField("", "inputFormButton", "Lost password", "javascript:page('Forgot');");
		$button = htmlOutput_ButtonField("", "inputFormButton", "Lost password", "javascript:lostPaswordClick();");
		print htmlOutput_TableCell(1, "inputFieldValue", $button);
		print htmlOutput_TableRowClose();
		print htmlOutput_TableClose();
	}

	function output_retrieve_password($error) {
		print htmlOutput_TableOpen("DetailData");
		print htmlOutput_TableRowOpen("salesHeaderRow");
		print htmlOutput_TableCell(3, "salesMenuTitle", "To retrieve your password, enter your username", "align=center");
		print htmlOutput_TableRowClose();

		print htmlOutput_TableRowOpen("");
		print htmlOutput_TableCell(1, "", "", "width=35%");
		print htmlOutput_TableCell(1, "", "", "width=55%");
		print htmlOutput_TableCell(1,"","");
		print htmlOutput_TableRowClose();

		if ($error > '') {
			print htmlOutput_TableRowOpen("");
			print htmlOutput_TableCell(1, "", "");
			print htmlOutput_TableCell(1, "inputField", $error);
			print htmlOutput_TableRowClose();
		}

		if ((CMG_GLOBAL) || (ERP_ARCIVE))
			$instructions = "Enter your SSO number as your username below and click <b>[Continue]</b>.<br>";
		else
			$instructions = "Enter your username (firstname.lastname) below and click <b>[Continue]</b>.<br>";
		$instructions .= "A <i>new</i> password will be generated and sent to your e-mail address.<br>";
		$instructions .= "Otherwise click the <b>[Cancel]</b> button and your password will not be changed.<br>";
		print htmlOutput_TableRowOpen("");
		print htmlOutput_TableCell(1, "", "");
		print htmlOutput_TableCell(1, "inputFieldValue", "<br>".$instructions."<br>");
		print htmlOutput_TableRowClose();

		print htmlOutput_TableRowOpen("");
		print htmlOutput_TableCell(1, "inputFieldTitle", "Username:");
		print htmlOutput_TableCell(1, "inputFieldValue", htmlOutput_InputFieldWidth("username", "inputFieldValue", "", 100));
		print htmlOutput_TableRowClose();
		print htmlOutput_TableRowOpen("");
		print htmlOutput_TableCell(1, "", "");
//		$button = htmlOutput_ButtonField("", "inputFormButton", "Continue", "javascript:page('Retrieve');");
		$button = htmlOutput_ButtonField("", "inputFormButton", "Continue");
		$button .= "&nbsp;".htmlOutput_ButtonField("", "inputFormButton", "Cancel", "javascript:page('');");
		print htmlOutput_TableCell(1, "inputFieldValue", $button);
		print htmlOutput_TableRowClose();

		print htmlOutput_TableClose();
	}

	function output_password_confirm($email, $msg) {
		print htmlOutput_TableOpen("DetailData");
		print htmlOutput_TableRowOpen("salesHeaderRow");
		print htmlOutput_TableCell(3, "salesMenuTitle", "Your password has been changed", "align=center");
		print htmlOutput_TableRowClose();

		print htmlOutput_TableRowOpen("");
		print htmlOutput_TableCell(1, "", "", "width=35%");
		print htmlOutput_TableCell(1, "", "", "width=55%");
		print htmlOutput_TableCell(1,"","");
		print htmlOutput_TableRowClose();

		print htmlOutput_TableRowOpen("");
		print htmlOutput_TableCell(1, "", "");
		print htmlOutput_TableCell(1, "inputField", "An e-mail has been sent to your mailbox: $email");
		print htmlOutput_TableRowClose();

		print htmlOutput_TableRowOpen("");
		print htmlOutput_TableCell(1, "", "");
		print htmlOutput_TableCell(1, "inputFieldValue", "<br>".$msg."<br>");
		print htmlOutput_TableRowClose();

		print htmlOutput_TableRowOpen("");
		print htmlOutput_TableCell(1, "", "");
//		$button = htmlOutput_ButtonField("", "inputFormButton", "Continue", "javascript:page('');");
		$button = htmlOutput_ButtonField("", "inputFormButton", "Continue");
		print htmlOutput_TableCell(1, "inputFieldValue", $button);
		print htmlOutput_TableRowClose();

		print htmlOutput_TableClose();
	}

	var $userCache = array();
	function cacheUserQuery($sql) {
		$rec = array();
		if (!isset($this->userCache[$sql])) {
			if ($r = $this->querydb($sql, $GLOBALS['realtimeConfig']['DATABASE']))
				while ($rec[] = mysql_fetch_assoc($r))
					;
			$this->userCache[$sql] = $rec;
		}
		return($this->userCache[$sql]);
	}
	
	function retrieve_user_record($username, $userrec=TRUE, $setting=null) {
		$user = null;
		if (strpos($username, '@')) {
			$sql = "select count(username) as N, group_concat(username) as NAMES from ONLINE_USER
				where (email = '$username')
				group by email
			";
			if (($r = $this->cacheUserQuery($sql)) && ($rec = $r[0]) && ($rec['N'] > 1)) {
				$this->error = 'The e-mail address you have entered entered is used by more than one user.';
				$this->error .= '<p>Please enter a unique user name instead;';
				return(null);
			}
			else if ($rec['N'] == 1)
				$username = $rec['NAMES'];
		}
		$this->error = 'No such account. Bad username entered';
		if ($userrec) {
			$query = "select * from ONLINE_USER where (username = '$username')";		
			if ($r = $this->cacheUserQuery($query))
				$user = $r[0];
		}
		$query = "select u.Parameter, u.Value, p.Type
				from ONLINE_USER_LOCAL as u
				left join ONLINE_USER_PARAMETERS as p on (p.Parameter = u.Parameter)
				where (u.Username = '$username')
		";
		if ($setting)
			$sql .= " and (Parameter = '$setting')";
		if ($r = $this->cacheUserQuery($query)) {
			foreach ($r as $rec) {
				if (($rec['Type'] == 'array'))
					$user[$rec['Parameter']] = $this->unserialize($rec['Value']);
				else
					$user[$rec['Parameter']] = $rec['Value'];
			}
		}
		return($user);
	}

	function array_merge_recursive_distinct(array &$array1, array &$array2) {
//		return(array_merge($array1, $array2));

		$merged = $array1;

		foreach ($array2 as $key => &$value) {
			if (is_array($value) && isset ($merged[$key]) && is_array ($merged [$key]) )
				$merged[$key] = $this->array_merge_recursive_distinct($merged [$key], $value);
			else if (is_int($key) && (!in_array($value, $merged)))
				$merged[count($merged)] = $value;
			else if (!in_array($value, $merged))
				$merged[$key] = $value;
		}
		return $merged;
	}

	// Note: array_merge() function calls below used to be array_unique(array_merge())
	// To retreive a particular setting, $setting may be named. This is used to retrieve
	// the 'status' setting during session validation.
	function retrieve_user_settings($username, $setting=null) {
		global $realtimeConfig;

		$user = null;
		// Then load the users settings
		if ($rec = $this->retrieve_user_record($username, TRUE, $setting)) {
			$user = array();
			// Load the 'default' users settings first
			if ($username != 'default')
				$user = $this->retrieve_user_record('default', FALSE, $setting);
//print "retrieve_user_settings1:";print_r($rec);
			foreach ($rec as $Parameter => $Value) {
				if (is_array($Value) && is_array($user[$Parameter]))
					$user[$Parameter] = array_merge($Value, $user[$Parameter]);
				else if ($Value > '')
					$user[$Parameter] = $Value;
			}
			// Load the settings for all groups that this user is a member of
			if (is_array($user['groupname'])) {
				foreach ($user['groupname'] as $group) {
					$rec = $this->retrieve_user_record($group, FALSE, $setting);
//print "retrieve_user_settings2:";print_r($rec);
					if (is_array($rec))
						foreach ($rec as $Parameter => $Value) {
							if (is_array($Value) && is_array($user[$Parameter]))
								$user[$Parameter] = array_merge($Value, $user[$Parameter]);
							else
								$user[$Parameter] = $Value;
						}
				}
			}
//print "retrieve_user_settings3:";print_r($user);
			// Reload the users settings so these will override any group settings
			$rec = $this->retrieve_user_record($username, TRUE, $setting);
			foreach ($rec as $Parameter => $Value) {
				if (is_array($Value) && is_array($user[$Parameter]))
					$user[$Parameter] = $this->array_merge_recursive_distinct($Value, $user[$Parameter]);
				else if ($Value > '')
					$user[$Parameter] = $Value;
			}
		}
//print "retrieve_user_settings4:";print_r($user);
		return($user);
	}

	function generate_new_password($user, $mail, $pw=NULL) {
		global $realtimeConfig;

		$error = null;
		if ($pw == NULL)
			$pw = $this->generate_random_word();
		$query = "update ONLINE_USER set password = encrypt('$pw') where (username = '".$user['username']."')";
		if ($r = $this->querydb($query, $realtimeConfig['DATABASE'])) {
			if ($realtimeConfig['SMTPHost'] > '')
				$mail->isSMTP();
			else
				$mail->isSendmail();
			$mail->Host = $realtimeConfig['SMTPHost'];
			$mail->From = $realtimeConfig['MailFrom'];
			$mail->Sender = $realtimeConfig['MailFrom'];
			$mail->FromName = $realtimeConfig['AppName'];
			$mail->AddAddress($user["email"], $user["fullname"]);
			$mail->Subject = $realtimeConfig['AppName'].' account for user '.$user['username'];

			$mail->IsHTML(false);
			$mail->Body = "

Your '".$realtimeConfig['AppName']."' password has been changed. Use the details below to log in.
You may change your password at anytime using the administration menu.
The '".$realtimeConfig['AppName']."' internet address is:

        ".$realtimeConfig['AppURL']."

Your '".$realtimeConfig['AppName']." login details are:

        Username: ".$user["username"]."
        Password: ".$pw."

Please ensure that this information is kept strictly confidential and in a safe place.

Note that this is an automated message. Do not reply to this message.
";
			if (!$mail->send()) $error = $mail->ErrorInfo." - Possible bad e-mail address. Unable to send data. Please contact the system administrator.";
		}
		else  $error = "Failed to generate a new password. Please contact the system administrator.";
		return($error);
	}

	function register_user($username, $password) {
		$this->opendb(); // Open the database , just in case this is being called from Tagpic etc. which have not already done so.

		$user = null;
		if (strpos($username, '@')) {
			$sql = "select count(username) as N, group_concat(username) as NAMES from ONLINE_USER
				where (email = '$username')
				and password = encrypt('$password', substring(password,1,2))
				group by email
			";
			if (($r = $this->querydb($sql, $realtimeConfig['DATABASE'])) && ($rec = mysql_fetch_assoc($r)) && ($rec['N'] > 1)) {
				$this->error = 'The e-mail address you have entered entered is used by more than one user.';
				$this->error .= '<p>Please log in using one of the following registered user names instead;';
				$this->error .= '<p>'.$rec['NAMES'];
				return(null);
			}
			else if ($rec['N'] == 1)
				$username = $rec['NAMES'];
		}
		$this->error = 'No such account. Bad username entered';

		if (function_exists('localPasswordValidation') && ($userrec = localPasswordValidation($this, $username, $password)) && ($userrec['status'] == 'enabled')) {
			$user = $userrec;
			$this->querydb("update ONLINE_USER set lastlogin = now() where username = '".$userrec['username']."'");
			$this->error = null;
		}
		else if (($userrec = $this->retrieve_user_settings($username)) && ($username == $userrec['username'])) {
			if ($userrec['status'] == 'enabled') {
				$seed = substr($userrec['password'], 0, 2);
				$sql = "select encrypt('$password', '$seed') as password";
				if (($r = $this->querydb($sql, $realtimeConfig['DATABASE'])) && ($rec = mysql_fetch_assoc($r)) && ($rec['password'] == $userrec['password'])) {
					if ($userrec['password'] == $rec['password']) {
						$user = $userrec;
						$this->querydb("update ONLINE_USER set lastlogin = now() where username = '$username'");
						$this->error = null;
					}
					else
						$this->error = 'Failed to retrieve users settings';
				}
				else
					$this->error = 'The username and password you have entered is incorrect';
			}
			else if ($userrec['status'] == 'maintenance')
				$this->error = 'The system is temporarily unavailable due to system maintenance.<p>Please try to login again later.';
			else if ($userrec['status'] == 'terminated')
				$this->error = 'This account is terminated. All account access is denied.<p>Contact the system administrator.';
			else if ($userrec['status'] == 'disabled')
				$this->error = 'The account has been disabled.<p>Contact the system administrator.';
			else
				$this->error = 'This account is not enabled and has an undefined status.<p>Contact the system administrator.';
		}
		else
			$this->error = 'The username you have entered is unknown';
		return($user);
	}

	// ----------------------------------------------------------------------
	//	Generate a new random password using the names
	//	in the demonstartion products table (DEMO_PRODUCT).
	// ----------------------------------------------------------------------
	function generate_random_word() {
		$db = $this->opendb();
		srand(time());
		do {
//			--------------------------------------------------------
//			Find a random record in DEMO_PRODUCT and return NAME
//			--------------------------------------------------------
			$n = rand(1,24000);
			if (NORWOOD_CONFIG == 1)
				$query  = "select lcase(NAME) as NAME from ZN_LABEL having NAME >= 'A%' limit ".$n.",1";
			else
				$query  = "select lcase(NAME) as NAME from DEMO_PRODUCT having NAME >= 'A%' limit ".$n.",1";
			$r = $this->querydb($query);
			if ($r) {
				$data = mysql_fetch_object($r);
				if ($data) {
//					--------------------------------------------------------
//					Convert to lowercase and confirm it starts with alpha
//					--------------------------------------------------------
					$s = strtolower($data->NAME);
					if (preg_match("/^[a-z]/", $s)) {
//						--------------------------------------------------------
//						Get the first word and remove non-alphanumeric letters
//						--------------------------------------------------------
						$n = strpos($s, " ");
						if ($n !== null)
							$s = substr($s, 0, $n);
						$s = preg_replace("/[^[:alnum:]]/", "", $s);
						if ($s != "")
							return($s);
					}
				}
			}
			else break;
		} while(true);
		$this->closedb($db);
		return(null);
	}
}

// This class is used to record the results of transactions as they are made in Online.
class onlineTrxHistory extends realtimeData {
	var $datetime;
	var $username;
	var $refType;
	var $refCode;
	var $action;
	var $message;
	var $techreport;
	var $result;
	
	// The constructor accepts the following arguments;
	//	$refType will describe the type of transaction record, e.g. 'Sales restriction group'
	//	$refType will identify the record key, e.g. '0970'
	//	$action will describe the action being taken, e.g. 'Add customer 002751'
	function __construct($refType, $refCode, $action) {
		$this->refType = $refType;
		$this->refCode = $refCode;
		$this->action = $action;
		$this->result = 'unknown';
		parent::__construct(true);
	}
/*	
	function __destruct() {
		$sql = "insert into ONLINE_TRX_HISTORY (TRX_DATE, USERNAME, REFTYPE, REFCODE, ACTION, RESULT, MESSAGE, TECHREPORT) values(
			now()
			, '".mysql_escape_string($GLOBALS['user']['username'])."'
			, '".mysql_escape_string($this->refType)."'
			, '".mysql_escape_string($this->refCode)."'
			, '".mysql_escape_string($this->action)."'
			, '".mysql_escape_string($this->result)."'
			, '".mysql_escape_string($this->message)."'
			, '".mysql_escape_string($this->techreport)."'
		)";
		$this->querydb($sql);
	}
*/	
	function write($result, $message, $techreport='', $show=false) {
		$this->result = $result;
		$this->message = $message;
		$this->techreport = $techreport;
		if ($show || ($result != 'success'))
			$this->show();
		$sql = "insert into ONLINE_TRX_HISTORY (TRX_DATE, USERNAME, REFTYPE, REFCODE, ACTION, RESULT, MESSAGE, TECHREPORT) values(
			now()
			, '".mysql_escape_string($_SESSION['user']['username'])."'
			, '".mysql_escape_string($this->refType)."'
			, '".mysql_escape_string($this->refCode)."'
			, '".mysql_escape_string($this->action)."'
			, '".mysql_escape_string($this->result)."'
			, '".mysql_escape_string($this->message)."'
			, '".mysql_escape_string($this->techreport)."'
		)";
		$this->querydb($sql);
		return(mysql_insert_id());
	}
	
	// The following methods exist only for backward compatibility
	// with the original/former onlineTrxHistory at Norwood.
	function error($message, $techreport='', $show=true) {
		$this->write('error', $message, $techreport, $show);
		if ($show)
			$this->show();
	}
	
	function warning($message, $techreport='', $show=true) {
		$this->write('warning', $message, $techreport, $show);
		if ($show)
			$this->show();
	}
		
	function success($message='', $techreport='', $show=false) {
		$this->write('success', $message, $techreport, $show);
		if ($show)
			$this->show();
	}
	
	function show() {
		$this->onlineTrxMessage();
	}
}

?>