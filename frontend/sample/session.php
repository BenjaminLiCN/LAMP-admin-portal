<?php
// ===============================================================================
// Realtime Data - Copyright 2002 Realtime Holdings Pty Ltd
// ===============================================================================
// session.php - Realtime data global file included by all modules.
// ===============================================================================

//	include_once("/usr/local/lib/norwood/online/include/db.php");
//	include_once("/usr/local/lib/norwood/online/include/menu.php");
//	require_once('/usr/local/lib/norwood/online/html/login/login.class.php');

// For single sign-on system, this function should return the user's settings
// $login is a userLogin class reference (see login.class.php), or null.
// This function is called from session.php and login/login.php.
function localSessionValidation($login) {
	return(null);
	// localSessionValidation is used to allow Norwood staff to access a restricted number of pages,
	// without being logged in, as long as they are on a local subnet they will operate as user 'despatch'.
	// $login is a userLogin class reference (see login.class.php), or null.
	// If $validate is true and there is any session discrepancy,
	// redirect to the login page with the proper user's session settings.
	// This function is called from session.php and login/login.php.
	$validate = in_array(substr($_SERVER['REMOTE_ADDR'], 0, 10), array('192.9.200.', '192.168.11', '192.168.10', '192.168.20', '192.168.30'));
	if ($validate) {
		$user_rec = $login->retrieve_user_settings('despatch');
		$user_rec['fullname'] = '';
		return($user_rec);
	}
	return(null);
}

class onlineSessionHandler {
	var $fp = null;

	function query($dbname, $sql, $db) {
		$ret = mysql_db_query($dbname, $sql, $db);
		if ($this->fp) {
			$mess = $_SERVER['REMOTE_ADDR']."\t".substr($sql, 0, 100)."\t".$_SERVER['PHP_SELF'];
			$mess .= strncasecmp($sql, 'select', 6)?("\t".mysql_affected_rows()." rows affected\n"):("\t".mysql_num_rows($ret)." row selected\n");
			fwrite($this->fp, $mess);
		}
		return($ret);
	}

	function open($savePath, $sessionName) {
//		$this->fp = fopen('/var/lib/php/session/session_log', 'a+');	// Comment out this line to turn off debugging
		return true;
	}
	 
	function close() {
		if ($this->fp)
			fclose($this->fp);
		return true;
	}

	function read($id) {
		$ret = false;
		if ($db = mysql_connect('queeniedb', 'tagpic', 'tagpic')) {
			$id = mysql_real_escape_string($id);

			$sql = "SELECT data FROM sessions WHERE id = '$id'";

			if ($result = $this->query('sessions', $sql, $db)) {
				if (mysql_num_rows($result)) {
					$record = mysql_fetch_assoc($result);
					$ret = $record['data'];
				}
			}

			mysql_close($db);
		}
		return($ret);
	}

	function write($id, $login) {
		$ret = false;
		if ($db = mysql_connect('queeniedb', 'tagpic', 'tagpic')) {
			$access = time();

//			$this->query('sessions', 'lock tables sessions write', $db);

			$id = mysql_real_escape_string($id);
			$access = mysql_real_escape_string($access);
			$login = mysql_real_escape_string($login);

			$sql = "REPLACE INTO sessions VALUES  ('$id', '$access', '$login')";

			$ret = $this->query('sessions', $sql, $db);

//			$this->query('sessions', 'unlock tables', $db);

			mysql_close($db);
		}
		return($ret);
	}


	function destroy($id) {
		$ret = false;
		if ($db = mysql_connect('queeniedb', 'tagpic', 'tagpic')) {
			$id = mysql_real_escape_string($id);

			$sql = "DELETE FROM sessions WHERE id = '$id'";

			$ret = $this->query('sessions', $sql, $db);
			mysql_close($db);
		}
		return($ret);
	}

	function clean($max) {
		$ret = false;
		if ($db = mysql_connect('queeniedb', 'tagpic', 'tagpic')) {
			$old = time() - $max;
			$old = mysql_real_escape_string($old);

			$sql = "DELETE FROM sessions WHERE access < '$old'";

			$ret = $this->query('sessions', $sql, $db);
			mysql_close($db);
		}
		return($ret);
	}												 
}

function online_session_init() {
	$handler = new onlineSessionHandler();
	if (session_set_save_handler(
		array($handler, 'open'),
		array($handler, 'close'),
		array($handler, 'read'),
		array($handler, 'write'),
		array($handler, 'destroy'),
		array($handler, 'clean')
	)) {
		session_set_cookie_params(60*60*12,'/','.norwood.com.au');	// Cookie expiry reduced from 24 to 12 hours to overcome staff being logged out during the day
		ini_set('session.cookie_domain', '.norwood.com.au' );
	}
	else
		die('Failed to establish DB session handlers<br>');
	
	if (php_sapi_name() != 'cli')
		session_start();
	if (file_exists($_SESSION['user']['loginPath'].'local.php'))
		include($_SESSION['user']['loginPath'].'local.php');
	else
		include('login/local.php');
	include_once("realtimeData.class.php");
	
// =================================================================================
// For backward compatibility with old HTML4 screens, declare these globals as well.
// =================================================================================
$GLOBALS['localPath'] = '/usr/local/lib/norwood/';		// /usr/local/lib/norwood/
$GLOBALS['htmlStyle'] = $GLOBALS['realtimeConfig']['STYLE']?$GLOBALS['realtimeConfig']['STYLE']:'nwCorporate';
$GLOBALS['styleRoot'] = $GLOBALS['realtimeConfig']['ROOT']."/styles/".$GLOBALS['realtimeConfig']['STYLE'];
$GLOBALS['htmlStylePath'] = $GLOBALS['realtimeConfig']['DOCUMENT_ROOT'].'/'.$GLOBALS['styleRoot'];


	// If this is a royalty owner session, override the style and global settings from db.php
	if (is_array($_SESSION['user']) && (count($_SESSION['user']['royalty_owner']) || ($_REQUEST['htmlStyle'] == 'royaltiesOnline'))) {

		// A royalty owner account must also have customer restrictions
		if (!count($_SESSION['user']['custno'])) {
			session_destroy();
			die('Improper royalty owner user session.<br>No customer restrictions defined.<br>Please contact the Royalties Administrator.<br>');
		}
		$_REQUEST['htmlStyle'] = 'royaltiesOnline';
//	}
//	if ($_REQUEST['htmlStyle'] == 'royaltiesOnline') {
		$GLOBALS['htmlStyle'] = 'royaltiesOnline';
		$GLOBALS['styleRoot'] = "/norwood/online/styles/".$GLOBALS['htmlStyle'];	// /norwood/online/styles/$GLOBALS['htmlStyle']
		$GLOBALS['htmlStylePath'] = "/var/www/html/".$GLOBALS['styleRoot'];

		$_SESSION['backgroundColour'] = '#b7c2a9';
		$_SESSION['buttonOnColour'] = '#4a7f03';
		$_SESSION['buttonOffColour'] = '#8bc43f';
		$_SESSION['borderColour'] = '#213903';

		$GLOBALS['realtimeConfig']['AppName'] = 'Royalties Online';
		$GLOBALS['realtimeConfig']['MAINMENU'] = 'ROYALTIESONLINE';
		$GLOBALS['topLevelMenu'] = $GLOBALS['realtimeConfig']['MAINMENU'];
		$GLOBALS['realtimeConfig']['onlineLogo'] = $GLOBALS['styleRoot']."/royalties_logo.png";

		define("ROYALTIES_CONFIG", 1);		// Royalties Online specific configuration
		if ($_SESSION['user']['royaltyMainMenu'] > '')
			$GLOBALS['realtimeConfig']['MAINMENU'] = $_SESSION['user']['royaltyMainMenu'];
	}
}

function online_session_start($skipSessionAuthentication=false) {
	online_session_init();
	
	if ($skipSessionAuthentication || (php_sapi_name() == 'cli'))
		return;

	$login = new realtimeData();

	// =============================================================================================
	// Verify that the user is logged in and that the account is still enabled.
	if ($_SESSION['user']) {
		$sql = "select * from ONLINE_USER where (username = '".$_SESSION['user']['username']."') and (password = '".$_SESSION['user']['password']."')";
		$r = $login->querydb($sql);
		if (mysql_num_rows($r) <= 0)
			$_SESSION['user'] = NULL;
		else if (($settings = $login->retrieve_user_settings($_SESSION['user']['username'], 'status')) && ($settings['status'] != 'enabled'))
			$_SESSION['user'] = NULL;
	}
	else if ($user_rec = localSessionValidation($login))
		$_SESSION['user'] = $user_rec;

	$loginRedirect = true;

	$sql = "select DATA, URL, if(EXPIRES > now(), 1, 0) as ACTIVE from ONLINE_USER_TEMP where UUID = '".$_REQUEST['TEMPSESSIONID']."'";
	if (($r = $login->querydb($sql)) && ($rec = mysql_fetch_assoc($r)) && ($rec['ACTIVE'] == 1)) {
		$_SESSION = unserialize($rec['DATA']);
		$referer = $thisurl = parse_url($rec['URL']);
		$_SESSION['TEMPSESSIONID'] = $_REQUEST['TEMPSESSIONID'];
	}

	$referer = parse_url($_SERVER['HTTP_REFERER']);
	$thisurl = parse_url($_SERVER['PHP_SELF']);

	$items = array();
	$values = array();
	// If the user has already accessed this URL before, skip the following test
	if ($_SESSION['TEMPSESSIONID'] > '') {
		$referer = array();
		$values = $_SESSION['user']['allowed_paths'];
	}
	else if (@in_array($thisurl['path'], $_SESSION['user']['allowed_paths']))
		$values[] = $thisurl['path'];
	else if (@in_array($referer['path'], $_SESSION['user']['allowed_paths']))
		$values[] = $referer['path'];
	else {
		// Retrieve the URLs of the entire menu tree and test whether the URL of this
		// page is included. If not then the user will be redirected to the login page.
		$m = $login->load_menu_array($_SESSION['user'], $GLOBALS['realtimeConfig']['MAINMENU']);
		$items = $login->build_module_menu_item($m, $values);
//print_r($values);
		foreach ($items as $k => $v) {
			$values[] = @parse_url($v, PHP_URL_PATH);
//			$values[$k] = $v['path'];
		}
	}

	if (in_array($thisurl['path'], $values) || in_array($referer['path'], $values)) {
		$loginRedirect = false;
		// =============================================================================================
		// Find the target window name for the selected page and set this in global $thisWindowName.
		// This variable will be passed to the FormOpen functions in the various modules.
		$sql = "select * from ONLINE_MENU where (url = '".$_SERVER['REQUEST_URI']."') or (url = '".$_SERVER['PHP_SELF']."') or (url like '".$_SERVER['PHP_SELF']."?%')";
		if (($r = $login->querydb($sql)) && ($rec = mysql_fetch_array($r))) {
			$GLOBALS['thisWindowName'] = (($_SESSION['user']['singleWindowMode'] == 'on') && ($rec['target'] != '_blank'))?'_self':$rec['target'];
			$GLOBALS['thisWindowTitle'] = $rec['description'];

			// =============================================================================================
			// If the selected page requires you to be logged in, but you aren't,
			// then you will be redirected to the login page.
			$loginRedirect = (($rec['login'] == 'yes') && ($_SESSION['user'] == NULL) && ($rec['url'] != $GLOBALS['realtimeConfig']['LOGIN']));
		}
/*
		else {
			list($arg1, $arg2) = split("/", $menu, 2);
			$sql = "select * from ONLINE_MENU where (menu = '".$arg1."') and (item = '".$arg2."')";
			if (($r = $login->querydb($sql)) && ($rec = mysql_fetch_array($r))) {
				$GLOBALS['thisWindowName'] = (($rec['target'] > '') && ($_SESSION['user']['singleWindowMode'] == 'on'))?'_self':$rec['target'];
				$GLOBALS['thisWindowTitle'] = $rec['description'];
			}
			else
				$loginRedirect = ($_SESSION['user'] == NULL);
		}
*/
	}
	else {
		// Check if the requested page does not require the user to be logged in
		$sql = "select * from ONLINE_MENU where (url = '".$_SERVER['PHP_SELF']."')";
		if (($r = $login->querydb($sql)) && ($rec = mysql_fetch_array($r)) && ($rec['login'] != 'yes'))
			$loginRedirect = false;
	}
	$GLOBALS['realtimeDataAppName'] = $GLOBALS['realtimeConfig']['AppName'];

	if ($loginRedirect) {
		unset($_SESSION['user']);
		$url = $GLOBALS['realtimeConfig']['LOGIN'];
//		if ($GLOBALS['htmlStyle'] > '')
//			$url .= "?htmlStyle=".$GLOBALS['htmlStyle'];
		die("
			<script>
				window.location.assign('');
				window.location.assign('".$url."');
			</script>
		");
	}
	if ($_SESSION['TEMPSESSIONID'] <= '')
		if (!@in_array($thisurl['path'], $_SESSION['user']['allowed_paths']))
			$_SESSION['user']['allowed_paths'][] = $thisurl['path'];
	
}

// =================================================================================
// For backward compatibility with old HTML4 screens, include these files as well.
include("/usr/local/lib/norwood/online/include/db.php");
include("/usr/local/lib/norwood/online/include/menu.php");
// =================================================================================
