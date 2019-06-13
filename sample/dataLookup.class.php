<?php
// Copyright (c) 2014 - Realtime Holdings PL - All right reserved
// ================================================================
// This file contains class dataLookup which extends realtimeData.
// These two classes form part of the "Realtime Data" framework.
// Use of this framework without the written authorisation of
// the copyright holder is strictly prohibited.
// For contact details, refer to http://www.realtime.com.au
// ================================================================
// RD002 - Report integration into menu
// DL001 - Multiple selection combo boxes
// RD003 - Asynchronous sessionUpdate handler
// DL002 - selectDistinct option added
// RD004 - Download progress bar for sales analysis and data lookup screens
// DL003 - Disable fieldset and buttons until edit screen is loaded
// DL004 - Add record copy - Allow filterlist to define a method instead of an SQL - Add filtertype: combotree
// DL005 - Improved combobox column filter, and added column celleditor and cellstyler options, and dataGridClass override
// DL006 - Report writer improvements and integration, so report may use the dataLookup::downloadQuery method.
// DL007 - Dynamically loaded recordEitButtons

include_once('realtimeData.class.php');

// Convert an array to an object
class dataLookupColumn {
	function __construct($var) {
		$this->title = null;		// Column header and description in selection combo box
		$this->table = null;		// SQL table name. Required for all columns which canEdit
		$this->field = null;		// SQL field name. May conrain SQL function to compose the column result
		$this->width = null;		// Relative column width and absolute field width when editing (in px)
		$this->align = 'left';		// Column alignment
		$this->leftjoin = null;		// Left join SQL table for this column. May be a string or an array
		$this->filtertype = 'text';	// Column datagrid filter. May be 'textarea', 'numberbox', datebox' or 'combobox',
		$this->filterlist= null;	// SQL query to generate combobox. Must return 'value' and 'text' fields
		$this->filteropts= array('mode' => 'local');	// An array of options for the lookup field, such as "array('required' => true)"
		$this->filterpost= false;	// Use having clause for column filter instead of where clause. Used for "group" columns
		$this->select = null;		// Column field displayed when result is from left joined table 
		$this->canDisplay= true;	// Column is available in to the outputGrid and generateOutputData methods
		$this->canEdit= true;		// Column is available in to the recordEdit method
		$this->editopts= array();	// An array of field options when editing, such as "array('required' => true)"
		$this->group = null;		// For grouping fields in a property grid. Refer mossgreen/lookup/inventory.php
		$this->default= '';		// default value when creating a new record
		$this->celleditor = false;	// An array of options to allow direct cell editing. Refer tic/lookup/campaign_receipt.class.php
		$this->cellstyler = null;	// Cell styler function. Refer tic/lookup/campaign_schedule.php
		$this->cellformatter = null;	// Cell formatter function. Refer tic/lookup/campaign_schedule.php
		foreach ($var as $k => $v)
			$this->$k = $v;
	}
}

class dataLookup extends realtimeData {
	var $title;
	var $column;
	var $settings = array(
		'realtimeDataClassFile' => '',
		'resultsperpage' => 50,
		'columnSelection' => null,
		'SortByColumn' => '',
		'SortByDirection' => '',
		'filterRules'	=> array(),
		'lookupDetailData' => null,
		'currencySymbol' => '$',
		'checkboxSelection' => null,	// if not null, display check boxes

		'downloadDirectory' => '/var/tmp',	// Directory used to create downloads
		'downloadDelete' => true,		// Delete temporary files after download?
		'downloadNotify' => 0,			// Warn/notify users that the download has been registered (set to '1=yes' or '0=no')
		
		'groupViewColumn'	=> null,	// Additional column used for "groupview" grouping. See "kaleya/worksorder.php" as an example
		
		'dataGridIdentifier'	=> 'searchResults',	// Maybe overrideen in a multi-tabbed screen with multiple grids. Refer cmg/lookup/eandolookup.php
		'dataGridClass'	=> 'datagrid',	// Maybe overrideen in a multi-tabbed screen with multiple grids. Refer tic/lookup/campaign_progress.php
		'selectDistinct'	=> false,	// Use a "distinct" qualifier in the SQL query

		'periodSelect' => 'TYR',
		'monthFromSelect' => '',
		'monthToSelect' => '',
		'dateFromSelect' => '',
		'dateToSelect' => '',
	);

	var $queryHavingClause;
	var $realtimeDataClassFile;
	var $pagerButtons = array();	// Additional pager buttons
	var $additionalFields = array();

	function __construct($classFile='', $customSettings=null) {
		parent::__construct();
		if (is_array($customSettings)) {
			foreach($customSettings as $k => $v)
				$this->settings[$k] = $v;
		}

		$this->pagerButtons[] = "<a href='#' onclick='javascript:resetSearchFilters();' class='easyui-linkbutton' title='Clear and reset all search data and filters' data-options=\"iconCls:'".iconCancel."',plain:true\"></a>";
		$this->settings['realtimeDataClassFile'] = $classFile;

		if (($_REQUEST['cmd'] == 'outputData') && $_REQUEST['rows']) {
//			$_SESSION[$classFile]['chartResultsPerPage'] = $_REQUEST['rows'];
			$_SESSION[$classFile]['resultsperpage'] = $_REQUEST['rows'];
		}
		else if ($_SESSION['user']['resultsperpage'])
			$this->settings['resultsperpage'] = $_SESSION['user']['resultsperpage'];

		// Load the users default settings if the current sessions settings are empty
		$permamentSettings = unserialize($_SESSION['user']['permamentSettings']);
		// Override any settings set within the current session
		if ($n = $_REQUEST['dashboard']) {
			$n--;
			$dashboardLayout = unserialize($_SESSION['user']['dashboardLayout']);
			foreach ($this->settings as $k => $v) {
				if (isset($dashboardLayout[$n]['data'][$k]))
					$this->settings[$k] = $dashboardLayout[$n]['data'][$k];
			}
			foreach ($this->settings as $k => $v)
				$this->$k = $v;
			$this->realtimeDataClassFile = $dashboardLayout[$n]['url'];
		}
		else {
			$this->applySettings( $classFile, $this->settings);
			$this->realtimeDataClassFile = $classFile;
		}
		if ((!is_array($this->columnSelection)) && is_array($this->column)) {
			$this->columnSelection = array();
			foreach($this->column as $k => $v)
				if (!$v->hidden and $v->canDisplay)
					$this->columnSelection[] = $k;
		}

		// If this is the initial page draw, then clear all the search filters and matching data
		if (!$_REQUEST['cmd'])
			$this->filterRules = $_SESSION[$classFile]['filterRules'] = array();
		else if ($_REQUEST['filterRules'] > '') {
			if (ini_get('magic_quotes_gpc'))
				$_SESSION[$this->realtimeDataClassFile]['filterRules'] = json_decode(stripslashes($_REQUEST['filterRules']));
			else
				$_SESSION[$this->realtimeDataClassFile]['filterRules'] = json_decode($_REQUEST['filterRules']);
		}

		$sql = "select * from ONLINE_REPORT where classfile = '".$this->realtimeDataClassFile."'";
		if ($r = $this->querydb($sql)) {
			$rec = mysql_fetch_assoc($r);
			$this->title = $rec['title'];
		}

	}

	function __destruct() {
		parent::__destruct();
	}

	function applySettings($classFile, $settings=array()) {
		$permamentSettings = unserialize($_SESSION['user']['permamentSettings']);
		foreach ($settings as $k => $v) {
			if (isset($_SESSION[$classFile][$k]))
				$settings[$k] = $_SESSION[$classFile][$k];
			else if (isset($permamentSettings[$classFile][$k]))
				$settings[$k] = $permamentSettings[$classFile][$k];
		}
		// Assign all settings to the class properties and session variables
		foreach ($settings as $k => $v) {
			$this->$k = $v;
			$_SESSION[$classFile][$k] = $v;
			$this->settings[$k] = $v;
		}
	}

	function resetSearchFilters() {
		unset($_SESSION[$this->realtimeDataClassFile]['geoLocation']);
	}

	function fetchSessionData() {
		die(json_encode($_SESSION[$this->realtimeDataClassFile]));
	}

	function loadFilterCombo($returnResult=false) {
		$q = $_REQUEST['q'];
		$data = array();
		$c = null;
		foreach($this->column as $col => $c)
			if ($_REQUEST['id'] == $col) {
				$sql = $c->filterlist;
				if ($q)
					$sql = str_replace('%%', '%', str_replace('%s', $q, $sql));
				break;
			}
		if ($c && method_exists($this, $sql))
			$data = $this->$sql($_REQUEST['nullok'], $c);
		else if ($r = $this->querydb($sql)) {
			// $_REQUEST['nullok'] used to indicate if the filter should include a null value
			// This is redundant with the new framework. Instead, it is now used to indicate
			// if this method is being called from the column filter combobox.
			// See NW inventoryLookup.php "inventory category" column
			// and Tic camapign_receipt.class.php "Product" column
//			if ($_REQUEST['nullok'])
//				$data = array(array('value' => '', 'text' => ''));
			while ($rec = mysql_fetch_assoc($r)) {
				// Pad the value with a space. This is to overcome the default action of the combobox filter
				// automatically selecting the item when using a remote filter. this padding is removed
				// by trim() in the havingFilter() and whereFilter() methods.
				if ($_REQUEST['nullok'] && ($c->filteropts['mode'] == 'remote'))
					$rec['value'] = ' '.$rec['value'];
				if ($c->format == 'MIXEDCASE')
					$rec['text'] = ucwords(strtolower($rec['text']));
				$data[] = $rec;
				if (count($data) > 100)
					break;
			}
		}
		$data[0]['debug'] = $this->debugData;
		if ($returnResult)
			return($data);
		die(json_encode($data));
	}

	function outputGrid($gridopts=null, $onLoadSuccess="", $onAfterEdit="") {

		$dashboard = $_REQUEST['dashboard'];
		if ($dashboard <= '')
			$dashboard = 0;

		$id = $this->dataGridIdentifier.$dashboard;
		$pagerButtonId = $id.'PagerButtons';
			if ($this->geoDataOption)
				$this->pagerButtons[] .= "<a href='#' onclick='javascript:geoDataShow(\"".$this->realtimeDataClassFile."\", \"$id\");' class='easyui-linkbutton' title='View these results as Geo data on a map' data-options=\"iconCls:'fa fa-globe',plain:true\"></a>";
//		if (!$dashboard) {
//			if ($this->pagerButtons > '')
//				$pagerButtons .= $this->pagerButtons;
//		}
		if (!isset($gridopts['pagination'])) {
			print " <div id='$pagerButtonId'>
					<table style='border-spacing:0'>
						<tr>
			";
			foreach ($this->pagerButtons as $button)
				print "\n\t\t\t\t\t\t\t\t<td>$button</td>\n";
			print "
						</tr>
					</table>
				</div>
			";
		}
		$filter = '';
		
		$updateFunction = "\$('#".$id."').".$this->dataGridClass."('load')";
		$enableCellEditing = false;
		foreach ($this->column as $col => $c) {
			if (is_array($this->columnSelection) && in_array($col, $this->columnSelection)) {
			if (is_array($c->celleditor))
				$enableCellEditing = true;
			switch ($c->filtertype) {
			case 'numberbox':
				$opt = '';
				if ($c->filteropts > '')
					$opt = $this->arrayToDataOptions($c->filteropts, $opt);
				$filter .= $comma."{
					field:'".$col."',
					type:'".$c->filtertype."',
					options:{
						".$opt."
					},
					op:['equal','notequal','less','greater']
				}";
				break;
			case 'timespinner':
				$opt = '';
				$opt ="
					onChange:function(value){
						var rule = $id.".$this->dataGridClass."('getFilterRule', '".$col."');
						if (value == ''){
							$id.".$this->dataGridClass."('removeFilterRule', '".$col."');
						} else {
							rule.value = value;
							$id.".$this->dataGridClass."('addFilterRule', rule);
						}
						$id.".$this->dataGridClass."('doFilter');
					},
					onSpinUp:function(){ $id.".$this->dataGridClass."('doFilter');},
					onSpinDown:function(){ $id.".$this->dataGridClass."('doFilter');}
				";
				if ($c->filteropts > '')
					$opt = $this->arrayToDataOptions($c->filteropts, $opt);
				$filter .= $comma."{
					field:'".$col."',
					type:'".$c->filtertype."',
					options:{
						".$opt."
					},
					op:['equal','notequal','less','greater']
				}";
				break;
			case 'datetimebox':
			case 'datebox':
				$opt = '';
				$opt ="onChange:function(value){
						$id.".$this->dataGridClass."('doFilter');
					},
					_icons_: [{
						iconCls:'fa fa-times rd-clear rd-clear-wide'
						, handler: function(e) {
							\$(e.data.target).textbox('clear');
							$id.".$this->dataGridClass."('removeFilterRule', '".$col."');
							$updateFunction;
						}
					}]
				";
				if ($c->filteropts > '')
					$opt = $this->arrayToDataOptions($c->filteropts, $opt);
				$filter .= $comma."{
					field:'".$col."',
					type:'".$c->filtertype."',
					options:{
						".$opt."
					},
					op:['equal','notequal','less','greater']
				}";
				break;
			case 'combotree':
			case 'combobox':
				$opt = "
					panelHeight:'300',
					onSelect:function(rec){
						if (rec.value == ''){
							$id.".$this->dataGridClass."('removeFilterRule', '".$col."');
						} else {
							$id.".$this->dataGridClass."('addFilterRule', {
								field: '".$col."',
								op: 'equal',
								value: rec.value
							});
						}
						$id.".$this->dataGridClass."('doFilter');
					},
					icons: [{
						iconCls:'fa fa-times rd-clear rd-clear-wide'
						, handler: function(e) {
							\$(e.data.target).textbox('clear');
							$id.".$this->dataGridClass."('removeFilterRule', '".$col."');
							$updateFunction;
							\$(e.data.target).".$c->filtertype."('reload');
						}
					}]
				";
				if ($c->filterlist > '')
					$opt .= ", url:'".$this->realtimeDataClassFile."?cmd=loadFilterCombo&nullok=1&id=".$col."'";
				if ($c->filteropts['mode'] == 'remote')
					$opt .= ", editable: true";
				else
					$opt .= ", editable: false";
				if ($c->filteropts > '')
					$opt = $this->arrayToDataOptions($c->filteropts, $opt);
				$filter .= $comma."{
					field:'".$col."',
					type:'".$c->filtertype."',
					options:{
						".$opt."
					}
				}";
				break;
			case 'textarea':
			case 'text':
				$opt = '';
				if ($c->filteropts > '')
					$opt = $this->arrayToDataOptions($c->filteropts, $opt);
				$filter .= $comma."{
					field:'".$col."',
					type:'text',
					op:['equal','contains','beginwith','endwith']
				}";
				break;
			}
			if ($filter > '')
				$comma = ',';
		}
		}

		print "
			<script>
				function openDetailWindow(win, url) {
					win.window('open');
					// If the detail window is nearly as big as the main layout panel
					// then it will be maximised. This works best for phones/tablets
					var h = $('#mainLayout').height();
					var w = $('#mainLayout').width();
					var detail = win.window('options');
					if ((detail.height > (h*0.9)) || (detail.width > (w*0.9)))
						win.window('maximize');
					win.window('refresh', url);
				}

				var cellUpdateComplete = true;		// Flag to inhibit cell editing until the prior edit/update has completed
				var cellEditingInProgress = false;
				var cellEditingDisabled = true;		// Flag to stop the 'cellEditingEnable' method from being called twice
				function geoDataShow(href, target) {
					var win = $('#geoDataWindow');
					if (win) {
						geoWindowInit(href, target);
						win.window('open');
						// If the detail window is nearly as big as the main layout panel
						// then it will be maximised. This works best for phones/tablets
						var h = $('#mainLayout').height();
						var w = $('#mainLayout').width();
						var detail = $('#geoDataWindow').window('options');
						if ((detail.height > (h*0.9)) || (detail.width > (w*0.9)))
							$('#geoDataWindow').window('maximize');
					}
				}

				function resetSearchFilters() {
					$.ajax({
						async: false,
						url: '".$this->realtimeDataClassFile."',
						dataType: 'json',
						method: 'get',
						data: {
							cmd: 'resetSearchFilters'
						}
					});
					$('#".$id."').".$this->dataGridClass."('removeFilterRule');
					updateSessionData('".$this->realtimeDataClassFile."', {
						filterRules: []
					}, function(){ $('#".$id."').".$this->dataGridClass."('reload'); });
				}
	

				$(function(){
					// Enable the datagrid-filter extensions
					var $id = $('#$id').".$this->dataGridClass."({
						filterBtnIconCls:'icon-filter'
					});
				";
				if ($filter > '')
					print "
					$id.".$this->dataGridClass."('enableFilter', [${filter}]);
					
					// Reset any search data
					$('#$id').".$this->dataGridClass."('removeFilterRule');
				";
				if (!isset($gridopts['pagination']))
					print "
					// Add the pagination buttons
					$('#$id').".$this->dataGridClass."('getPager').pagination({
						buttons: $('#$pagerButtonId')
					});
				";
				if (is_array($_SESSION[$this->realtimeDataClassFile]['filterRules']))
					foreach ($_SESSION[$this->realtimeDataClassFile]['filterRules'] as $f)
						print "
							$('#$id').".$this->dataGridClass."('getFilterComponent', '".$f->field."').val('".$f->value."');
							$('#$id').".$this->dataGridClass."('addFilterRule', {
								field: '".$f->field."',
								op: '".$f->op."',
								value: '".$f->value."'
							});
						";
				else
					print "
						updateSessionData('".$this->realtimeDataClassFile."', {
							filterRules: []
						});
					";
//					print "$('#$id').".$this->dataGridClass."('doFilter');
//					";
				print "
				});
			</script>
		";

		$pageSize = $this->resultsperpage;
		$sql = "select Lookup from ONLINE_USER_PARAMETERS where PARAMETER = 'resultsperpage'";
		$pageList = '';
		if (($r = $this->querydb($sql)) && ($rec = mysql_fetch_row($r))) {
			$sql = $rec[0];
			if ($r = $this->querydb($sql)) {
				$pageList = '[';
				$comma = '';
				while ($rec = mysql_fetch_row($r)) {
					if ($rec[0] > '') {
						$pageList .= $comma.$rec[0];
						$comma = ',';
					}
				}
				$pageList .= ']';
			}
		}

		$options = "url: '".$this->realtimeDataClassFile."?cmd=outputData&targetGridID=$id'
			, singleSelect: true
			, checkOnSelect: ".($this->checkboxSelection?'false':'true')."
			, selectOnCheck: ".($this->checkboxSelection?'false':'true')."
			, border:false
			, remoteFilter: true
			, fitColumns: true
//			, view: scrollview
			, fit: true
			, pagination: true
			, pagePosition: 'top'
			, pageSize: '$pageSize'
			, pageList: $pageList
			, sortName: '".$this->SortByColumn."'
			, sortOrder: '".$this->SortByDirection."'
			, onHeaderContextMenu: function(e, field){
				e.preventDefault();
				if (!contextMenu){
					createColumnMenu();
				}
				contextMenu.menu('show', {
					left:e.pageX,
					top:e.pageY
				});
			}
		";
		if ($this->dataGridClass == 'treegrid')
			$onLoadSuccess .= "$('#debugWindow').window({content: data[0].debug});\n";
		else
			$onLoadSuccess .= "$('#debugWindow').window({content: data.debug});\n";
		if ($enableCellEditing) {
			$onLoadSuccess .= "
				if (cellEditingDisabled) {
					$(this).".$this->dataGridClass."('enableCellEditing');
					cellEditingDisabled = false;
				}
				cellUpdateComplete = true;
			";
			$options .= "
				, onAfterEdit: function(index,row,changes) {
					$('#".$id."').".$this->dataGridClass."('loading');
					cellEditingInProgress = false;
					$.ajax({
						dataType: 'json',
						data: {
							cmd: 'onAfterEditHandler',
							row: row,
							changes: changes,
							index: index
						},
						success: function(data) {
							$('#".$id."').".$this->dataGridClass."('loaded');
							$('#".$id."').".$this->dataGridClass."('updateRow', {index: index, row: data.row});
							".$onAfterEdit."
						}
					});
				}
				, onCellEdit: function(index, field, v) {
					cellEditingInProgress = true;
					cellUpdateComplete = false;
				}
				, onBeforeCellEdit: function(index, field, v) {
					return cellUpdateComplete;
				}
			";
		}
		if ($this->dataGridClass == 'treegrid')
			$options .= "
				, onLoadSuccess: function(row,data) {
					${onLoadSuccess}
				}
			";
		else
			$options .= "
				, onLoadSuccess: function(data) {
					${onLoadSuccess}
				}
			";

		if ($this->groupViewColumn > '') {
			$options .= "
				, view: groupview
				, groupField: '".$this->groupViewColumn."'
			";
		}
		if (method_exists($this, 'outputDetailWindow'))
			$options .= "
				, onClickRow: function (rowIndex, rowData) {
					updateSessionData('".$this->realtimeDataClassFile."', {lookupDetailData: rowData}, function() {
						var win = $('#".$this->lookupDetailWindow."');
						if (cellEditingInProgress)
							;
						else if (cellUpdateComplete && win) {
							var url = '".$this->realtimeDataClassFile."?cmd=outputDetailWindow';
							openDetailWindow(win, url);
//							win.window('open');
//							// If the detail window is nearly as big as the main layout panel
//							// then it will be maximised. This works best for phones/tablets
//							var h = $('#mainLayout').height();
//							var w = $('#mainLayout').width();
//							var detail = win.window('options');
//							if ((detail.height > (h*0.9)) || (detail.width > (w*0.9)))
//								win.window('maximize');
//							win.window('refresh', url);
						}
					});
				}
			";
		if ($gridopts)
			$options = $this->arrayToDataOptions($gridopts, $options);
		if ($dashboard)
			;
		else if ($this->checkboxSelection) {
			$options .= "
				, onCheck: function(rowIndex, rowData) {".$this->checkboxSelection."('onCheck',{rowData: rowData, rowIndex: rowIndex});}
				, onUncheck: function(rowIndex, rowData) {".$this->checkboxSelection."('onUncheck', {rowData: rowData, rowIndex: rowIndex});}
				, onCheckAll: function(rows) {".$this->checkboxSelection."('onCheckAll', {rowData: rows});}
				, onUncheckAll: function(rows) {".$this->checkboxSelection."('onUncheckAll', {rowData: rows});}
			";
		}
		print "<table id='$id'  data-options=\"$options\">
		<thead>
			<tr>
		";
		if ($this->checkboxSelection)
			print "<th data-options=\"field:'ck',checkbox:true\"></th>\n";
		foreach($this->column as $col => $c) {
			if ($c->filtertype == 'none')
				$options = "field:'".$col."',width:".$c->width.",sortable:false,align:'".$c->align."'";
			else
				$options = "field:'".$col."',width:".$c->width.",sortable:true,align:'".$c->align."'";
			if ($c->celleditor)
				$options .= ',editor:{'.$this->arrayToDataOptions($c->celleditor).'}';
			if ($c->cellstyler)
				$options .= ',styler: '.$this->arrayToDataOptions(array($c->cellstyler));
			if ($c->cellformatter)
				$options .= ',formatter: '.$this->arrayToDataOptions(array($c->cellformatter));
			if ($c->halign)
				$options .= ",halign:'".$c->halign."'";
			if (is_array($this->columnSelection) && in_array($col, $this->columnSelection))
				print"<th data-options=\"$options\">".$c->title."</th>\n";
		}
		print "			</tr>
				</thead>
			</table>
		";
	}
	
	function onAfterEditHandler() {
	}
	
	function onCellEditHandler() {
	}

	function Match($field, $search) {
		foreach($this->column as $c) {
			// tolerance values can be defined as either, e.g.;
			// 20 or +20 or -20, indicating plus/minus, plus or minus 20 absolute
			// 5% or +5% or -5%, indicating plus/minus, plus or minus 5 percent
			if (($c->field == $field) && ($c->tolerance > '')) {
				$len = strlen($c->tolerance);
				if ($percent = ($c->tolerance[$len-1] == '%')) {
					if ($plusonly = ($c->tolerance[0] == '+')) {
						$val = substr($c->tolerance, 1, $len-2)/100;
						$sql .="($field-($field*$val) <= '$search')";
					}
					else if ($minusonly = ($c->tolerance[0] == '-')) {
						$val = substr($c->tolerance, 1, $len-2)/100;
						$sql .="($field+($field*$val) >= '$search')";
					}
					else {
						$val = substr($c->tolerance, 0, $len-1)/100;
						$sql .="($field-($field*$val) <= '$search') and ($field+($field*$val) >= '$search')";
					}
				}
				else {
					if ($plusonly = ($c->tolerance[0] == '+')) {
						$val = substr($c->tolerance, 1, $len-1)+0;
						$sql .="($field-($field*$val) <= '$search')";
					}
					else if ($minusonly = ($c->tolerance[0] == '-')) {
						$val = substr($c->tolerance, 1, $len-1)+0;
						$sql .="($field+$val >= '$search')";
					}
					else {
						$val = $c->tolerance+0;
						$sql .="($field-$val <= '$search') and ($field+$val >= '$search')";
					}
				}
				return($sql);
			}
		}
		$sql = '';
		$and = '';
		$match = explode(' ', $search);
		foreach ($match as $code) {
			if ($code > '') {
				$sql .= $and.'('.$field.' like "%'.mysql_real_escape_string($code).'%")';
				$and = ' and ';
			}
		}
		return($sql);
	}

	function havingFilter() {
		$op = array(
			'equal' => "(%s = '%s')",
			'notequal' => "(%s <> '%s')",
			'less' => "(%s < '%s')",
			'greater' => "(%s > '%s')",
			'contains' => "(%s like '%%%s%%')",
			'beginwith' => "(%s like '%s%%')",
			'endwith' => "(%s like '%%%s')",
		);
		$sql = '';
		foreach($this->column as $col => $c)
			if (is_array($this->columnSelection) && in_array($col, $this->columnSelection))
				$column[$col] = $c;
		if (is_array($_SESSION[$this->realtimeDataClassFile]['filterRules'])) {
			foreach ($_SESSION[$this->realtimeDataClassFile]['filterRules'] as $f) {
				$c = $column[$f->field];
				$f->value = trim($f->value);
				if (($c->filterpost) &&($f->value > '')) {
					if ($f->op == 'contains')
						$sql .= ' and '.$this->Match($f->field, $f->value);
					else if ($c->filtertype == 'datebox')
						$sql .= ' and '.sprintf($op[$f->op], $f->field, strftime('%Y-%m-%d', strtotime($f->value)));
					else if ($c->filtertype == 'datetimebox')
						$sql .= ' and '.sprintf($op[$f->op], $f->field, strftime('%Y-%m-%d %H:%M:%S', strtotime($f->value)));
					else
						$sql .= ' and '.sprintf($op[$f->op], $f->field, mysql_real_escape_string($f->value));
				}
			}
		}
		return($sql);
	}

	function whereFilter() {
		$op = array(
			'equal' => "(%s = '%s')",
			'notequal' => "(%s <> '%s')",
			'less' => "(%s < '%s')",
			'greater' => "(%s > '%s')",
			'contains' => "(%s like '%%%s%%')",
			'beginwith' => "(%s like '%s%%')",
			'endwith' => "(%s like '%%%s')",
		);
		$sql = '';
		foreach($this->column as $col => $c)
			if (is_array($this->columnSelection) && in_array($col, $this->columnSelection))
				$column[$col] = $c;
		if (is_array($_SESSION[$this->realtimeDataClassFile]['filterRules'])) {
			foreach ($_SESSION[$this->realtimeDataClassFile]['filterRules'] as $k => $f) {
				if (is_array($this->columnSelection) && in_array($f->field, $this->columnSelection)) {
					$c = $column[$f->field];
					$f->value = trim($f->value);
					if ((!$c->filterpost) &&($f->value > '')) {
						if ($c->table)
							$field = $c->table.'.'.$c->field;
						else
							$field = $c->field;
						if (($c->filtertype == 'combobox') && is_array($c->editopts) && in_array('multiple', $c->editopts) && $c->editopts['multiple'])
							$sql .= ' and '.$this->Match($field, $f->value);
						else if ($f->op == 'contains')
							$sql .= ' and '.$this->Match($field, $f->value);
						else if ($c->filtertype == 'datebox')
							$sql .= ' and '.sprintf($op[$f->op], $field, strftime('%Y-%m-%d', strtotime($f->value)));
						else if ($c->filtertype == 'datetimebox')
							$sql .= ' and '.sprintf($op[$f->op], $field, strftime('%Y-%m-%d %H:%M:%S', strtotime($f->value)));
						else
							$sql .= ' and '.sprintf($op[$f->op], $field, mysql_real_escape_string($f->value));
					}
				}
				else
					unset($_SESSION[$this->realtimeDataClassFile]['filterRules'][$k]);
			}
		}
		return($sql);
	}

	function Query() {
		$column = array();
		$leftjoin = array();
		if (is_array($this->leftjoin)) {
			foreach ($this->leftjoin as $f)
				$leftjoin[$f] = $f;	
		}
		else if ($this->leftjoin > '')
			$leftjoin[$this->leftjoin] = $this->leftjoin;	
		$sql = 'select ';
		if ($this->selectDistinct)
			$sql .= 'distinct ';
		$comma = '';
		if (is_array($this->additionalFields))
			foreach($this->additionalFields as $k => $v) {
				$sql .= $comma.$v.' as `'.$k."`\n";
				$comma = ', ';
			}
		foreach($this->column as $col => $c) {
			if (is_array($this->columnSelection) && in_array($col, $this->columnSelection)) {
				if ($c->select)
					$sql .= $comma.$c->select.' as `'.$col."`\n";
				else if ($c->table)
					$sql .= $comma.$c->table.'.'.$c->field.' as `'.$col."`\n";
				else
					$sql .= $comma.$c->field.' as `'.$col."`\n";
				$comma = ', ';
				$column[$col] = $c;
				if (is_array($c->leftjoin)) {
					foreach ($c->leftjoin as $f)
						$leftjoin[$f] = $f;	
				}
				else if ($c->leftjoin > '')
					$leftjoin[$c->leftjoin] = $c->leftjoin;	
			}
		}

		$sql .= ' from '.$this->tableName.' as '.$this->tableAlias."\n";
		foreach ($leftjoin as $j)
			$sql .= $j."\n";
		if ($this->tableSubset > '')
			$sql .= ' where '.$this->tableSubset;
		$sql .= $this->whereFilter();
		if ($this->geoDataOption
			&& ($x1 = (isset($_REQUEST['x1'])?$_REQUEST['x1']:$_SESSION[$this->realtimeDataClassFile]['geoLocation']['x1']))
			&& ($x2 = (isset($_REQUEST['x2'])?$_REQUEST['x2']:$_SESSION[$this->realtimeDataClassFile]['geoLocation']['x2']))
			&& ($y1 = (isset($_REQUEST['y1'])?$_REQUEST['y1']:$_SESSION[$this->realtimeDataClassFile]['geoLocation']['y1']))
			&& ($y2 = (isset($_REQUEST['y2'])?$_REQUEST['y2']:$_SESSION[$this->realtimeDataClassFile]['geoLocation']['y2']))
		) {
			$_SESSION[$this->realtimeDataClassFile]['geoLocation']['x1'] = $x1;
			$_SESSION[$this->realtimeDataClassFile]['geoLocation']['x2'] = $x2;
			$_SESSION[$this->realtimeDataClassFile]['geoLocation']['y1'] = $y1;
			$_SESSION[$this->realtimeDataClassFile]['geoLocation']['y2'] = $y2;
			if ($x1 > $x2) {
				$sql .= " and (((geo.LONGITUDE >= $x1) and (geo.LONGITUDE <= 180))";
				$sql .= " or ((geo.LONGITUDE >= -180) and (geo.LONGITUDE <= $x2)))";
			}
			else
				$sql .= " and (geo.LONGITUDE >= $x1) and (geo.LONGITUDE <= $x2)";
			$sql .= " and (geo.LATITUDE >= $y1) and (geo.LATITUDE <= $y2)";
		}
		else
			unset($_SESSION[$this->realtimeDataClassFile]['geoLocation']);
		if ($this->groupByClause > '')
			$sql .= $this->groupByClause;
		if ($this->queryHavingClause)
			$sql .= $this->queryHavingClause;
		else
			$sql .= ' having 1';
		$sql .= $this->havingFilter();
/*
		if ($this->groupViewColumn > '') {
			$sql .= ' order by '.$this->groupViewColumn.' asc';
			if (array_key_exists($this->SortByColumn, $column))
				$sql .= ', '.$this->SortByColumn.' '.$this->SortByDirection;
		}
		else if (array_key_exists($this->SortByColumn, $column))
			$sql .= ' order by '.$this->SortByColumn.' '.$this->SortByDirection;
*/
// multiSort option
		$comma = ' order by ';
		if ($this->groupViewColumn > '') {
			$sql .= $comma.$this->groupViewColumn.' asc';
			$comma = ', ';
		}
		$sort = explode(',', $this->SortByColumn);
		$order = explode(',', $this->SortByDirection);
		if (count($sort) == count($order)) {
			foreach ($sort as $n => $s) {
				if (array_key_exists($s, $column)||(is_array($this->additionalFields) && array_key_exists($s, $this->additionalFields))) {
					$sql .= $comma.'`'.$s.'` '.$order[$n];
					$comma = ', ';
				}
			}
		}
		else if (array_key_exists($this->SortByColumn, $column))
			$sql .= $comma.'`'.$this->SortByColumn.'` '.$this->SortByDirection;
		return($sql);
	}

	var $matchingData = array();
	public function generateOutputData($temp=false) {
		if (!is_array($this->columnSelection)) {
			$this->columnSelection = array();
			foreach($this->column as $k => $v)
				if ((!$v->hidden) && $v->canDisplay)
					$this->columnSelection[] = $k;
		}

		if ($_REQUEST['sort'] > '') {
			$_SESSION[$this->realtimeDataClassFile]['SortByColumn'] = $_REQUEST['SortByColumn'] = $this->SortByColumn = $_REQUEST['sort'];
			$_SESSION[$this->realtimeDataClassFile]['SortByDirection'] = $_REQUEST['SortByDirection'] = $this->SortByDirection = $_REQUEST['order'];
			$_SESSION[$this->realtimeDataClassFile]['resultsperpage'] = $_REQUEST['resultsperpage'] = $_REQUEST['rows'];
//			$this->sessionUpdate();
		}

//		if ($_REQUEST['filterRules'] > '') {
//			if (ini_get('magic_quotes_gpc'))
//				$_SESSION[$this->realtimeDataClassFile]['filterRules'] = json_decode(stripslashes($_REQUEST['filterRules']));
//			else
//				$_SESSION[$this->realtimeDataClassFile]['filterRules'] = json_decode($_REQUEST['filterRules']);
//		}
		$sql = $this->Query();

		$data = array();
		$data['rows'] = array();
		if ($temp) {
			$this->querydb('drop table if exists '.$temp);
			$this->querydb('create temporary table '.$temp.' '.$sql);
			if ($this->groupByClause > '')
				$sql = 'select * from '.$temp;
			else
				$sql = 'select distinct * from '.$temp;
			if ($r = $this->querydb($sql))
				$data['total'] = mysql_num_rows($r);

			if ($_REQUEST['page'] > '') {
				$page = $_REQUEST['page'];
				$rows = $_REQUEST['rows'];
				$offset = ($page - 1) * $rows;
				$sql .= sprintf(" limit %d,%d", $offset, $rows);
			}

			if ($r = $this->querydb($sql)) {
				while ($rec = mysql_fetch_assoc($r)) {
					foreach($this->column as $k => $v)
						$rec[$k] = $this->fieldFormat($k, $rec);
					$data['rows'][] = $rec;
				}
			}
		}
		else {
			// Improved query performance avoids selecting everything into a temporary table
			$offset = 0;
			if ($_REQUEST['page'] > '') {
				$page = $_REQUEST['page'];
				$rows = $_REQUEST['rows'];
				$offset = ($page - 1) * $rows;
				$sql .= sprintf(" # limit %d,%d", $offset, $rows);
			}
			if ($r = $this->querydb($sql)) {
				$rows = $data['total'] = @mysql_num_rows($r);
				if ($_REQUEST['page'] > '') {
					$rows = $_REQUEST['rows'];
					@mysql_data_seek($r, $offset);
				}
				while ($rows && ($rec = @mysql_fetch_assoc($r))) {
					foreach($this->column as $k => $v) {
						$filter = false;
						$fmt = strip_tags($this->fieldFormat($k, $rec));
						// Yellow highlighting of matching search data
						foreach ($_SESSION[$this->realtimeDataClassFile]['filterRules'] as $f) {
							$column = $this->column[$k];
							if (($column->filtertype != 'combobox') && ($f->field == $k)) {
								if (!$this->matchingData[$k]) {
	//								switch ($column->filtertype) {
	//								case 'combobox':
	//									$this->matchingData[$k] = array($this->getComboDesc($k, $f->value));
	//									continue;
	//								default:
										$this->matchingData[$k] = explode(' ', $f->value);
	//									break;
	//								}
								}
								foreach ($this->matchingData[$k] as $m)
									if ($m > '')
										$fmt = $this->highlight($m, $fmt);

								$filter = true;
								$fmt = str_replace(array(chr(21),chr(22)), array('<span style="color:black;background-color:yellow;">','</span>'), $fmt);
								break;
							}
						}
						if ($filter)
							$rec[$k] = $fmt;
						else
							$rec[$k] = $this->fieldFormat($k, $rec);
					}
					$data['rows'][] = $rec;
					$rows--;
				}
			}
		}
		$data['debug'] = $this->debugData;
		return($data);
	}

	function highlight($word, $subject) {
		if ($word) {
//			http://stackoverflow.com/questions/11830593/how-to-highlight-search-keyword-using-php-preg-replace-with-parentheses
//			return preg_replace('#'.preg_quote($word).'#i', chr(21).'\\0'.chr(22), $subject);
			return preg_replace(chr(20).preg_quote($word).chr(20).'i', chr(21).'\\0'.chr(22), $subject);
/*
			$regex_chars = '\.+?(){}[]^$*';
			for ($i=0; $i<strlen($regex_chars); $i++)
				$word = str_replace($regex_chars[$i], '\\'.$regex_chars[$i], $word);
			// Use non-printable characters, to stop recursive replacement
			return eregi_replace('(.*)('.$word.')(.*)', '\1'.chr(21).'\2'.chr(22).'\3', $subject);
*/
		}
		else
			return($subject);
	}

	function fieldFormat($field, $rec, $htmlmode=true) {
		$column = $this->column[$field];
		switch($column->format) {
		case "PERIOD":
			$ret .= $this->getPeriodDesc($rec[$field]);
			break;
		case "WEEK":
			$y = substr($rec[$field], 0, 4);
			$w = substr($rec[$field], 4);
			$ret .= sprintf('%d week %d', $y, $w);
			break;
		case "DATETIME":
			// Note that %p is used instead of %P because strptime() (called in recordEditLoad())
			// does not recognised this formate parameter.
			if ($rec[$field] <> 0)
				$ret .= strftime('%e %b %Y %I:%M %p', strtotime($rec[$field]));
			break;
		case "DATE":
			if ($rec[$field] <> 0)
				$ret .= strftime('%e %b %Y', strtotime($rec[$field]));
			break;
		case "TIME":
			if ($rec[$field] != null)
				$ret .= strftime('%I:%M %p', strtotime($rec[$field]));
			break;
		case "INTEGER":
			if ($rec[$field] != 0)
				$ret = number_format($rec[$field]);
			break;
		case "CURRENCY":
			if ($rec[$field] != 0) {
				$cents = ($_SESSION['user']['showDollarsCents'] == 'cents')?2:0;
				if ($htmlmode || ($this->currencySymbol == '$'))
					$ret .= $this->currencySymbol.number_format($rec[$field], $cents);
				else
					$ret .= number_format($rec[$field], $cents);
			}
			break;
		case "PERCENT":
			if ($rec[$field] != 0)
				$ret .= number_format($rec[$field],1).'%';
			break;
		case "MIXEDCASE":
			$ret = ucwords(strtolower($rec[$field]));
			break;
		default:
			if (($column->format > 0) && ($rec[$field] != NULL))	// When format is a decimal precision, e.g. 1 or 2
				$ret = number_format($rec[$field], $column->format);
			else
				$ret = $rec[$field];
			break;
		}
//		if ($htmlmode && ($ret == ""))
//			$ret = "&nbsp;";
		return($ret);
	}

	var $comboCache = array();
	function getComboDesc($field, $d) {
		if (!$this->comboCache[$field])
			$this->comboCache[$field] = array();
		if (!$this->comboCache[$field][$d]) {
			$sql = $this->column[$field]->filterlist;
			if (method_exists($this, $sql)) {
				$data = $this->$sql();
				foreach ($data as $rec)
					if ($rec['value'] == $d)
						$this->comboCache[$field][$d] = $rec['value'];
			}
			else if ($r = $this->querydb($sql)) {
				while ($rec = mysql_fetch_assoc($r)) {
					if ($rec['value'] == $d)
						$this->comboCache[$field][$d] = $rec['value'];
				}
			}
		}
		return($this->comboCache[$field][$d]);
	}

	function reportFormButtons($rec) {
		$classname = $rec['classname'];
		$ret = '
			<div style="float:left;padding-left:10px;"><a id="'.$classname.'ButtonRun" class="easyui-linkbutton" data-options="disabled:true,iconCls:\'icon-ok\'" onclick="'.$classname.'EditSubmit();" title="Save all changes and close this window">Run</a></div>
			<div style="float:left;padding-left:10px;"><a class="easyui-linkbutton" data-options="iconCls:\'icon-undo\'" onclick="restoreDefaultSettings(\'restoreDefaultSettings\', 1, \'Restore default settings?\');" title="Restore all settings to the default">Defaults</a></div>
			';
		if ($rec['instructions'] > '')
		$ret .= '<div style="float:left;padding-left:10px;"><a class="easyui-linkbutton" data-options="iconCls:\'icon-help\'" onclick="$(\'#reportHelp\').panel(\'open\')">Help</a></div>
		';
		$ret .= '<div style="float:left;padding-left:10px;"><a class="easyui-linkbutton" data-options="iconCls:\'icon-cancel\'" onclick="$(\'#'.$this->lookupDetailWindow.'\').window(\'close\', false);" title="Close this window without making any changes">Cancel</a></div>
		';
		return($ret);
	}

	// If the form doesn't have any settings, this should be called with $helponly=true
	function reportFormShow($helponly=false, $options=array()) {
		$sql = "select * from ONLINE_REPORT where classfile = '".$_REQUEST['classfile']."'";
		if ($r = $this->querydb($sql))
			$rec = mysql_fetch_assoc($r);

		$progress = array('downloadProgressText' => 'Starting....', 'downloadProgressValue' => 1);
		$this->downloadProgressUpdate($progress);

		if ($helponly)
			print '
				<div class="easyui-panel" id="'.$rec['classname'].'Panel" data-options="onOpen: function(){
						$(\'#'.$this->lookupDetailWindow.'\').window({onResize: function(w, h){$(\'#'.$rec['classname'].'Panel\').panel(\'resize\', {height: h-94, width: w-34});}})
						$(\'#'.$this->lookupDetailWindow.'\').window(\'setTitle\', \''.$rec['title'].' - Settings\');
				}" style="padding:20px;">
				<div id="reportHelp" class="easyui-panel" title="&nbsp;Report help" style="width:100%;height:100%;padding:10px;"
					data-options="closed:false,iconCls:\'icon-help\',collapsible:false,minimizable:false,maximizable:false,closable:true">
					'.$rec['instructions'].'
				</div>
				</div>
				<div style="float:right;padding-top:10px;padding-bottom:10px;">
					<div style="float:left;padding-left:10px;"><a id="'.$rec['classname'].'ButtonRun" class="easyui-linkbutton" data-options="disabled:false,iconCls:\'icon-ok\'" onclick="'.$rec['classname'].'EditSubmit();" title="Save all changes and close this window">Run</a></div>
					<div style="float:left;padding-left:10px;"><a class="easyui-linkbutton" data-options="iconCls:\'icon-cancel\'" onclick="$(\'#'.$this->lookupDetailWindow.'\').window(\'close\', false);" title="Close this window without making any changes">Cancel</a></div>
				</div>
			';
		else {
			$opts = "fit:true
				, showHeader:true
				, url:'".$rec['classfile']."?cmd=formLoad'
				, method:'get'
				, showGroup:true
				, scrollbarSize:16
				, columns: propertyColumns
				, onLoadSuccess: function(data) {
					for(var i=0; i<data.rows.length; i++) {
						if ((data.rows[i].editor.type == 'combobox') && (data.rows[i].editor.options.mode == 'remote')) {
							var index = i;
							var value = data.rows[i].value;
							$.ajax({
								url: data.rows[index].editor.options.url,
								dataType: 'json',
								success: function(result) {
									data.rows[index].editor.options.data = result;
									$('#".$rec['classname']."').propertygrid('refreshRow', index);
								}
							});
						}
					}
					$('#".$rec['classname']."ButtonRun').linkbutton({disabled: false});
				}
				, _onBeforeEdit: function(index,row) {
					// End cell editing as soon as an option is selected for a combobox
					if (row.editor.type == 'combobox' && (!row.editor.options.multiple))
						row.editor.options.onSelect = function(){
							window.setTimeout(function() {
								$('#".$rec['classname']."').propertygrid('endEdit', index);
							}, 100);
						}
				}
				, onEndEdit: function(index,row,changes) {
					var ed = $(this).propertygrid('getEditor',{index:index,field:'value'});
					if ((row.editor.type == 'combobox') && (row.editor.options.mode == 'remote')) {
						row.editor.options.data = $(ed.target).combobox('getData');
					}
				}
			";
			$opts = $this->arrayToDataOptions($options, $opts);
			print '
				<div class="easyui-panel" id="'.$rec['classname'].'Panel" data-options="onOpen: function(){
						$(\'#'.$this->lookupDetailWindow.'\').window({onResize: function(w, h){$(\'#'.$rec['classname'].'Panel\').panel(\'resize\', {height: h-94, width: w-34});}})
						$(\'#'.$this->lookupDetailWindow.'\').window(\'setTitle\', \''.$this->title.' - Settings\');
				}" style="padding:20px;">
				<div id="reportHelp" class="easyui-panel" title="&nbsp;Report help" style="width:100%;height:50%;padding:10px;"
					data-options="closed:true,iconCls:\'icon-help\',collapsible:false,minimizable:false,maximizable:false,closable:true">
					'.$rec['instructions'].'
				</div>
				 <table id="'.$rec['classname'].'" class="easyui-propertygrid" data-options="'.$opts.'">
				</table>
				</div>
				<div style="float:right;padding-top:10px;padding-bottom:10px;">
			';
			print $this->reportFormButtons($rec);
			print '</div>';
		}
		print '
			<script>
				
				var propertyColumns = [[
					{field: "name", title: "Settings", width: 50},
					{field: "value", title: "Value (click to change)", width: 50, formatter: function(value, row, index) {
						if (row.editor.type == "combobox") {
							var ret = "";
							var data = {};
							if (data = row.editor.options.data)
								if (row.editor.options.multiple) {
									var comma = "";
									var v = value.split(",");
									for (var x=0; x<v.length; x++) {
										for (var i=0; i<data.length; i++) {
											if (data[i].code == v[x]) {
												ret += comma+data[i].text;
												comma =",";
											}
										}
									}
									return(ret);
								}
								else {
									for(var i=0; i<data.length; i++)
										if (data[i].code == value) {
											ret = data[i].text;
											break;
										}
								}
							return(ret);
						}
						return(value);
					}}
				]];

				function restoreDefaultSettings(cmd, refresh, confirm) {
					if (confirm > "")
						$.messager.confirm("Confirm", confirm, function(r) {if (r) restoreDefaultSettings(cmd, refresh, "")});
					else {
						$.ajax({
							async: false,
							url: "'.$rec['classfile'].'",
							dataType: "json",
							method: "get",
							data: {
								cmd: "restoreDefaultSettings"
							}
						});
						$("#'.$rec['classname'].'").propertygrid("reload");
					}
				}

				function '.$rec['classname'].'EditSubmit() {
					var rows = [];
					var data = {cmd: "runReport", sendto: ""};
					var p = $("#'.$rec['classname'].'.easyui-propertygrid");
					if (p.length) {
						var n;
						rows = p.propertygrid("getRows");
						for (n = 0; n < rows.length; n++)
							data[rows[n].id] = rows[n].value;
					}
					dl_common("report", "'.$rec['classfile'].'", data);
				}
			</script>
		';
	}
	
	var $xlsTextFormat;
	var $xlsWrapFormat;
	var $xlsIntegerFormat;
	var $xlsCurrencyFormat;
	var $xlsPercentFormat;
	var $xlsDateFormat;
	var $xlsDateTimeFormat;
	var $xlsTimeFormat;

	function excelFormat(&$workbook, $rec, $field, $row, $col) {
		$c = $this->column[$field];
		$v = $rec[$field];
		switch($c->format) {
		case 'TIME':
			// Convert HH:MM:SS to an XLS time number, (0.0 <= $v < 1.0)
			$t = (substr($v,0,2)/24)+(substr($v,3,2)/(24*60))+(substr($v,6,2)/(24*60*60));
			$workbook->getActiveSheet()->setCellValue($this->XLScoord($row, $col), $t);
			$workbook->getActiveSheet()->getStyle($this->XLScoord($row, $col))->applyFromArray($this->xlsTimeFormat);
			break;
		case 'DATETIME':
			// Convert YYYY-MM-DD HH:MM:SS to an XLS day number fractional value
			$t = gmmktime(substr($v,11,2)+0, substr($v,14,2)+0, substr($v,17,2)+0, substr($v,5,2)+0, substr($v,8,2)+0, substr($v,0,4)+0)/(24*60*60);
			$t += 25569.0;
			$workbook->getActiveSheet()->setCellValue($this->XLScoord($row, $col), $t);
			$workbook->getActiveSheet()->getStyle($this->XLScoord($row, $col))->applyFromArray($this->xlsDateTimeFormat);
			break;
		case 'DATE':
			// Convert YYYY-MM-DD to an XLS day number, where 31/12/1899 is day zero.
			$yy = substr($v,0,4)+0;
			$mm = substr($v,5,2)+0;
			$dd = substr($v,8,2)+0;
			if ($yy && $mm && $dd) {
				$t = gmmktime(0, 0, 0, $mm, $dd, $yy)/(24*60*60);
				$t += 25569.0;
				$workbook->getActiveSheet()->setCellValue($this->XLScoord($row, $col), $t);
				$workbook->getActiveSheet()->getStyle($this->XLScoord($row, $col))->applyFromArray($this->xlsDateFormat);
			}
			break;
		case 'PERIOD':
			$workbook->getActiveSheet()->setCellValue($this->XLScoord($row, $col), $this->fieldFormat($field, $rec, false));
			break;
		case 'STDCOST':
		case 'CURRENCY':
			$workbook->getActiveSheet()->setCellValue($this->XLScoord($row, $col), $v);
			$workbook->getActiveSheet()->getStyle($this->XLScoord($row, $col))->applyFromArray($this->xlsCurrencyFormat);
			break;
		case 'INTEGER':
			$workbook->getActiveSheet()->setCellValue($this->XLScoord($row, $col), $v+0);
			$workbook->getActiveSheet()->getStyle($this->XLScoord($row, $col))->applyFromArray($this->xlsIntegerFormat);
			break;
		case 'TRAFFIC':
		case 'PERCENT':
			$workbook->getActiveSheet()->setCellValue($this->XLScoord($row, $col), $v/100);
			$workbook->getActiveSheet()->getStyle($this->XLScoord($row, $col))->applyFromArray($this->xlsPercentFormat);
			break;
		case 'MIXEDCASE':
			$v = ucwords(strtolower($v));
		default:
			if ($c->format > 0) {
				if ($v != NULL) {	// When format is a decimal precision, e.g. 1 or 2
					$fmt = array('numberformat' => array('code' => '0.'.str_repeat('0', $c->format)));
					$workbook->getActiveSheet()->setCellValue($this->XLScoord($row, $col), $v);
					$workbook->getActiveSheet()->getStyle($this->XLScoord($row, $col))->applyFromArray($fmt);
				}
			}
			else if ($v != '~') {
				$workbook->getActiveSheet()->setCellValueExplicit($this->XLScoord($row, $col), $v);
				if ($c->filtertype == 'textarea')
					$workbook->getActiveSheet()->getStyle($this->XLScoord($row, $col))->applyFromArray($this->xlsWrapFormat);
				else
					$workbook->getActiveSheet()->getStyle($this->XLScoord($row, $col))->applyFromArray($this->xlsTextFormat);
			}
			break;
		}
		switch ($c->align) {
		case 'right':
			$fmt = array('alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT, 'vertical' => PHPExcel_Style_Alignment::VERTICAL_TOP, 'wrap' => false));
			$workbook->getActiveSheet()->getStyle($this->XLScoord($row, $col))->applyFromArray($fmt);
			break;
		case 'center':
			$fmt = array('alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER, 'vertical' => PHPExcel_Style_Alignment::VERTICAL_TOP, 'wrap' => false));
			$workbook->getActiveSheet()->getStyle($this->XLScoord($row, $col))->applyFromArray($fmt);
			break;
		}
	}
	
	function downloadQuery($returnResult=false, $worksheetGroupBy=null, $opt=array()) {
		
		// Close the session so the downloadProgressUpdate handler doesn't get blocked by the standard session handler file locking.
		if (ini_get('session.save_handler') == 'files')
			session_write_close();

		$progress = array('downloadProgressText' => 'Preparing spreadsheet query. Please wait...', 'downloadProgressValue' => 1);
		$this->downloadProgressUpdate($progress);

		$sql = $this->Query();

		$trx = new onlineTrxHistory('Download', $this->title, $sql);
		if ($result = $this->querydb($sql)) {
			$nrows = mysql_num_rows($result);
			$cnt = 0;
			$time = time();
			
			$filename = ($_SESSION['user']['username'] > '')?$_SESSION['user']['username']:'auto';
			$filename = $this->downloadDirectory.'/'.$filename.strftime('-%Y%m%d-%H%M%S');

			// If there are more than 64,000 rows, then generate the spreadsheet as a tab separated file
			if (($nrows > 64000) && ($fp = fopen($filename.'.xls', 'w'))) {
				$filename .= '.xls';

				$row = 0;
				while ($rec = mysql_fetch_assoc($result)) {
					$cnt++;
					if ($time != time()) {
						// Update the progress every second
						$time = time();
						$pct = floor(($cnt/$nrows)*100);
						$txt = sprintf('Generating spreadsheet - Writing row %d of %d', $cnt, $nrows);
						$progress = array('downloadProgressText' => $txt, 'downloadProgressValue' => $pct);
						$this->downloadProgressUpdate($progress);
					}
					$fs = "";
					if ($row == 0) {
						foreach ($rec as $k => $v) {
							if ($this->columnHeader[$k]) {
								fwrite($fp, $fs);
								fwrite($fp, str_replace('<br/>', ' ', $this->columnHeader[$k]));
								$fs = "\t";
							}
						}
						fwrite($fp, "\n");
					}
					$fs = "";
					foreach ($rec as $k => $v) {
						if ($this->column[$k]) {
							fwrite($fp, $fs.$this->fieldFormat($k, $rec, false));
							$fs = "\t";
						}
					}
					fwrite($fp, "\n");
					$row++;
				}
				$progress = array('downloadProgressText' => 'Finished. Saving the spreadsheet. Please wait...', 'downloadProgressValue' => 100);
				$this->downloadProgressUpdate($progress);
				fclose($fp);
			}
			else if (1) {	// Change this to 0 to use the old Spreadsheet_Excel_Writer
				// =====================================================================================
				// New PHPExcel based spreadsheet writer. Requires XML support; i.e. yum install php-xml
				// =====================================================================================
				require_once "PHPExcel_1.8.0/Classes/PHPExcel.php"; 

				PHPExcel_Settings::setCacheStorageMethod(PHPExcel_CachedObjectStorageFactory::cache_to_sqlite3, array( 'memoryCacheSize' => '24MB' ));
				ini_set('memory_limit', '256M');
				$workbook = new PHPExcel();
				if ($nrows > 40000)
					set_time_limit(0);
				else if ($nrows > 20000)
					set_time_limit(360);
				else if ($nrows > 10000)
					set_time_limit(180);
				else if ($nrows > 5000)
					set_time_limit(90);
				else
					set_time_limit(60);

				$colHeadingFormat = array(
//					'font' => array('bold' => true, 'name' => 'Helvetica', 'size' => 10),
					'font' => array('bold' => true),
					'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER, 'vertical' => PHPExcel_Style_Alignment::VERTICAL_TOP, 'wrap' => true),
				);
				$headerLeftFormat = array(
					'font' => array('bold' => true),
					'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT),
				);
				$this->xlsTextFormat = array(
//					'font' => array('bold' => false, 'name' => 'Arial', 'size' => 10, 'vertical' => PHPExcel_Style_Alignment::VERTICAL_TOP),
					'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT, 'vertical' => PHPExcel_Style_Alignment::VERTICAL_TOP, 'wrap' => false),
				);
				$this->xlsWrapFormat = array(
//					'font' => array('bold' => false, 'name' => 'Arial', 'size' => 10, 'vertical' => PHPExcel_Style_Alignment::VERTICAL_TOP),
					'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT, 'vertical' => PHPExcel_Style_Alignment::VERTICAL_TOP, 'wrap' => true),
				);
				$this->xlsIntegerFormat = array('numberformat' => array('code' => '#,###;[RED]-#,###'));
				$this->xlsCurrencyFormat = array('numberformat' => array('code' => '[$$-C09]#,##0.00;[RED]-[$$-C09]#,##0.00'));
				$this->xlsPercentFormat = array('numberformat' => array('code' => '0.00%'));
				$this->xlsDateFormat = array('numberformat' => array('code' => 'D MMM YYYY'));
				$this->xlsDateTimeFormat = array('numberformat' => array('code' => 'D MMM YYYY HH:MM AM/PM'));
				$this->xlsTimeFormat = array('numberformat' => array('code' => 'HH:MM AM/PM'));

				$worksheetName = "~";
				$worksheet = $workbook->getActiveSheet();
				$worksheet->freezePane('A2');
				$worksheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(1, 1);	// Print row 0 as page header

				$row = 0;
				while ($rec = mysql_fetch_assoc($result)) {
					$cnt++;
					if ($time != time()) {
						// Update the progress every second
						$time = time();
						$pct = floor(($cnt/$nrows)*100);
						$txt = sprintf('Generating spreadsheet - Writing row %d of %d', $cnt, $nrows);
						$progress = array('downloadProgressText' => $txt, 'downloadProgressValue' => $pct);
						$this->downloadProgressUpdate($progress);
					}
					$d = array();

					if ($worksheetGroupBy && ($rec[$worksheetGroupBy] != $worksheetName)) {
						$name = ($rec[$worksheetGroupBy]>'')?substr(str_replace('& ', '', $rec[$worksheetGroupBy]), 0, 30):'Unknown category';
						if ($worksheetName != '~') {
							$worksheet = new PHPExcel_Worksheet($workbook, $name);
							$workbook->addSheet($worksheet);
						}
						else
							$worksheet->setTitle($name);
						$worksheetName = $rec[$worksheetGroupBy];
						$row = 0;
					}
					if ($row == 0) {

						$hdr = array();
						$n = 0;
						$grp = null;
						foreach($this->column as $k => $c) {
							foreach ($rec as $field => $v) {
								if ($k == $field) {
									$hdr[$n] = '';
									if ($grp != $c->group)
										$grp = $hdr[$n] = $c->group;
									$n++;
								}
							}
						}

						if ($grp) {
							$worksheet->fromArray($hdr, NULL, $this->XLScoord(0, 0));
							$worksheet->getStyle($this->XLScoord(0, 0).':'.$this->XLScoord(0, $n-1))->applyFromArray($headerLeftFormat);
							$row++;
						}

						foreach($this->column as $k => $c)
							foreach ($rec as $field => $v) {
								if ($k == $field) {
									$n = count($d);
									$worksheet->getColumnDimension($this->XLScoord(null, $n))->setWidth($c->width/15);
									$v = str_replace('<br/>', ' ', $c->title);
									$worksheet->setCellValueExplicit($this->XLScoord($row, $n), $v);
									$worksheet->getStyle($this->XLScoord($row, $n))->applyFromArray($colHeadingFormat);
									$d[] = $v;
									break;
								}
						}
						$row++;
						$freezeColumn = isset($opt['freezeColumn'])?$opt['freezeColumn']:0;
						$worksheet->freezePane($this->XLScoord($row, $freezeColumn));
						$worksheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(1, $row);	// Print row 0 as page header
					}
					$col = 0;
					foreach($this->column as $k => $c) {
						foreach ($rec as $field => $v) {
							if ($field == $k) {
								$this->excelFormat($workbook, $rec, $field, $row, $col);
								$col++;
							}
						}
					}
					$row++;
				}
				$workbook->setActiveSheetIndex(0);
				$progress = array('downloadProgressText' => 'Finished. Saving the spreadsheet. Please wait...', 'downloadProgressValue' => 100);
				$this->downloadProgressUpdate($progress);

	//			$objWriter = PHPExcel_IOFactory::createWriter($workbook, "Excel2007");
				$objWriter = new PHPExcel_Writer_Excel5($workbook);	// Old BIFF format still supports UTF8
	//			$objWriter = new PHPExcel_Writer_Excel2007($workbook);	// 2007 uses excess memory and crashes with > 10000 rows
				$filename .= '.xls';
				$objWriter->save($filename);
			}
			else {

				require_once "Spreadsheet/Excel/Writer.php"; 
				$filename .= '.xls';
				$workbook = new Spreadsheet_Excel_Writer($filename);
				$worksheet = $workbook->addWorksheet();

				$colHeadingFormat = $workbook->addFormat(); 
				$colHeadingFormat->setBold(); 
				$colHeadingFormat->setFontFamily('Helvetica'); 
				$colHeadingFormat->setBold(); 
				$colHeadingFormat->setSize('10'); 
				$colHeadingFormat->setAlign('center'); 

				$this->xlsIntegerFormat = $workbook->addFormat();
				$this->xlsIntegerFormat->setNumFormat('#,###;[RED]-#,###');

				$this->xlsCurrencyFormat = $workbook->addFormat();
				$this->xlsCurrencyFormat->setNumFormat('[$$-C09]#,##0.00;[RED]-[$$-C09]#,##0.00');

				$this->xlsPercentFormat = $workbook->addFormat();
				$this->xlsPercentFormat->setNumFormat('0.00%');

				$this->xlsDateFormat = $workbook->addFormat();
				$this->xlsDateFormat->setNumFormat('D MMM YYYY');

				$this->xlsDateTimeFormat = $workbook->addFormat();
				$this->xlsDateTimeFormat->setNumFormat('D MMM YYYY HH:MM AM/PM');

				$this->xlsTimeFormat = $workbook->addFormat();
				$this->xlsTimeFormat->setNumFormat('HH:MM AM/PM');

				$row = 0;
				while ($rec = mysql_fetch_assoc($result)) {
					$cnt++;
					if ($time != time()) {
						// Update the progress every second
						$time = time();
						$pct = floor(($cnt/$nrows)*100);
						$txt = sprintf('Generating spreadsheet - Writing row %d of %d', $cnt, $nrows);
						$progress = array('downloadProgressText' => $txt, 'downloadProgressValue' => $pct);
						$this->downloadProgressUpdate($progress);
					}
					$d = array();
					if ($row == 0) {
						foreach ($rec as $k => $v) {
							foreach($this->column as $col => $c)
								if ($col == $k) {
									$n = count($d);
									$worksheet->setColumn($n, $n, $c->width/15);
									$d[] = str_replace('<br/>', ' ', $c->title);
									break;
								}
						}
						$worksheet->WriteRow($row++, 0, $d, $colHeadingFormat);

					}
					$col = 0;
					foreach ($rec as $k => $v) {
						$c = $this->column[$k];
						$style = is_array($this->additionalFields) && array_key_exists($k, $this->additionalFields);
						if (!$style) {
							switch($c->format) {
							case 'TIME':
								// Convert HH:MM:SS to an XLS time number, (0.0 <= $v < 1.0)
								$t = (substr($v,0,2)/24)+(substr($v,3,2)/(24*60))+(substr($v,6,2)/(24*60*60));
								$worksheet->write($row, $col, $t, $this->xlsTimeFormat);
								break;
							case 'DATETIME':
								// Convert YYYY-MM-DD HH:MM:SS to an XLS day number fractional value
								$t = gmmktime(substr($v,11,2)+0, substr($v,14,2)+0, substr($v,17,2)+0, substr($v,5,2)+0, substr($v,8,2)+0, substr($v,0,4)+0)/(24*60*60);
								$t += 25569.0;
								$worksheet->write($row, $col, $t, $this->xlsDateTimeFormat);
								break;
							case 'DATE':
								// Convert YYYY-MM-DD to an XLS day number, where 31/12/1899 is day zero.
								$yy = substr($v,0,4)+0;
								$mm = substr($v,5,2)+0;
								$dd = substr($v,8,2)+0;
								if ($yy && $mm && $dd) {
									$t = gmmktime(0, 0, 0, $mm, $dd, $yy)/(24*60*60);
									$t += 25569.0;
									$worksheet->write($row, $col, $t, $this->xlsDateFormat);
								}
								break;
							case 'PERIOD':
								$worksheet->write($row, $col, $this->fieldFormat($k, $rec, false));
								break;
							case 'MIXEDCASE':
								$worksheet->writeString($row, $col, ucwords(strtolower($v)));
								break;
							case 'CURRENCY':
								$worksheet->write($row, $col, $v, $this->xlsCurrencyFormat);
								break;
							case 'INTEGER':
								$worksheet->write($row, $col, $v+0, $this->xlsIntegerFormat);
								break;
							case 'TRAFFIC':
							case 'PERCENT':
								$worksheet->write($row, $col, $v/100, $this->xlsPercentFormat);
								break;
							default:
								// utf8_decode converts to ISO-8859-1, which is generally used by PCs.
								if ($v != '~')
									$worksheet->writeString($row, $col, utf8_decode($v));
								break;
							}
							$col++;
						}
					}
					$row++;
				}
				$freeze = array(1,0,1,0); 
				$worksheet->freezePanes($freeze);
				$worksheet->repeatRows(0, 1);	// Print row 0 as page header

				$progress = array('downloadProgressText' => 'Finished. Saving the spreadsheet. Please wait...', 'downloadProgressValue' => 100);
				$this->downloadProgressUpdate($progress);
				$workbook->close();
			}

			$row--;
			$message = 'This download transaction has been registered.';
			$message .= '<br>'.$nrows.' records in '.basename($filename);
			$onlineTrxID = $trx->write('success', $message, print_r($_SESSION[$this->realtimeDataClassFile], true));
			$data = array('result' => 'success', 'debug' => $this->debugData, 'onlineTrxID' => $onlineTrxID, 'filename' => $filename);
		}
		else {
			$message = 'The download has failed: '.mysql_error();
			$onlineTrxID = $trx->write('failure', $message, print_r($_SESSION[$this->realtimeDataClassFile], true));
			$data = array('result' => 'failure', 'debug' => $this->debugData, 'onlineTrxID' => $onlineTrxID, 'message' => $message);
		}
		$progress = array('downloadProgressText' => '', 'downloadProgressValue' => 0);
		$this->downloadProgressUpdate($progress);
		$data['downloadNotify'] = $this->downloadNotify;
		$data['sendto'] = 'screen';
		$data['debug'] = $this->debugData;
		if ($returnResult)
			return($data);
		die(json_encode($data));
	}
	
	public function XLScoord($row, $col) {
		$cell = '';
		if ($col > 25) {
			$cell .= chr(64+floor($col/26));
			$col %= 26;
		}
		$cell .= chr(65+$col);
		if ($row !== null)
			$cell .= ($row+1);
		return($cell);
	}

	function loadColumnSelection() {
		$data = array();
		foreach($this->column as $col => $v)
			if ($v->canDisplay)
				$data[] = array('ck' => true, 'id' => $col, 'name' => str_replace('<br/>', ' ', $v->title), 'group' => $v->group);
		die(json_encode($data));
	}

	function columnSelectCombo($updateFunction, $title='Additional columns') {
		if (!is_array($this->columnSelection)) {
			$this->columnSelection = array();
			foreach($this->column as $col => $v)
				if ((!$v->hidden) && $v->canDisplay)
					$this->columnSelection[] = $col;
		}

		$comma = '';
		$txt = $val = '';
		if (is_array($this->columnSelection)) {
			$val = '[';
			foreach($this->columnSelection as $v) {
				$txt .= $comma.$v;
				$val .= $comma."'$v'";
				$comma = ',';
			}
			$val .= ']';
		}
		else {
			$val = "'".$this->columnSelection."'";
			$txt = $this->columnSelection;
		}

		print "<script>
				var columnSelectionInitialValue = ".$val.";
			</script>
		";

		print $this->htmlTableOpen("style='width:223px;'");

		print $this->htmlTableRowOpen();
		print $this->htmlTableCell(2, $title);
		print $this->htmlTableRowClose();

		print $this->htmlTableRowOpen();
		$data = null;
		$combo = 'combobox';
		$options = array(
			'delay'		=> 300,
			'width'		=> 221,
			'panelWidth'	=> 300,
			'showHeader'	=> false,
			'multiple'	=> true,
			'idField'	=> 'id',
			'valueField'	=> 'id',
			'textField'	=> 'name',
			'url' 		=> $this->realtimeDataClassFile,
			'mode'		=> 'remote',
			'method'	=> 'get',
			'groupField'	=> 'group',
			'columns'	=> "[[{field:'ck',checkbox:true},{field:'name',title:'Column',width:120}]]",
			'onBeforeLoad'	=> "function(param){
				$('#columnSelection').".$combo."('setValues', $val);
				param.cmd = 'loadColumnSelection';
			}",
			'fitColumns'	=> true,

//			'onChange'	=> "function() {
//				columnSelectionInitialValue = null;
//			}",
			'onUnselect'	=> "function(rec) {
				updateSessionData('".$this->realtimeDataClassFile."', {
					columnSelection: $('#columnSelection').".$combo."('getValues')
				}, function(){ $updateFunction; });
			}",
			'onSelect'	=> "function(rec) {
				updateSessionData('".$this->realtimeDataClassFile."', {
					columnSelection: $('#columnSelection').".$combo."('getValues')
				}, function(){ $updateFunction; });
			}",

//			'onLoadSuccess'	=> "function(){
//				$('#columnSelection').combogrid('setValues', '');
//				if (columnSelectionInitialValue)
//					$('#columnSelection').combogrid('setValues', $val);
//			}",
		);
		$cell = $this->htmlComboBox($data, $this->columnSelection, 'columnSelection', $options, 'easyui-'.$combo);
		print $this->htmlTableCell(2, $cell);
		print $this->htmlTableRowClose();

		print $this->htmlTableClose();
	}

	function recordDelete() {
		$this->recordEdit($_REQUEST['windowid'], $_REQUEST['formName'], json_decode($_REQUEST['keydata']), 'delete');		
	}

	function recordCopy() {
		$this->recordEdit($_REQUEST['windowid'], $_REQUEST['formName'], json_decode($_REQUEST['keydata']), 'copy');		
	}

	function recordEditButtons($win=null, $form=null, $mode='edit', $onSave=null) {
		if ($win == null) {
			$win = $_REQUEST['win'];
			$form = $_REQUEST['form'];
			$mode = $_REQUEST['mode'];
			$onSave = urldecode($_REQUEST['onSave']);
			$disabled = 'false';
		}
		else
			$disabled = 'true';
		if ($mode == 'edit') {
			print '<div style="float:left;padding-left:10px;"><a id="'.$form.'EditButtonDelete" class="easyui-linkbutton" data-options="disabled:'.$disabled.',iconCls:\'fa fa-trash-o\'" onclick="'.$form.'Delete();" title="Delete this record">Delete</a></div>'."\n";
			print '<div style="float:left;padding-left:10px;"><a id="'.$form.'EditButtonCopy" class="easyui-linkbutton" data-options="disabled:'.$disabled.',iconCls:\'fa fa-copy\'" onclick="'.$form.'Copy();" title="Copy this as a new record">Copy</a></div>'."\n";
		}
		if ($mode == 'delete') {
			print '<div style="float:left;padding-left:10px;"><a id="'.$form.'EditButtonConfirm" class="easyui-linkbutton" data-options="disabled:'.$disabled.',iconCls:\'icon-ok\'" onclick="'.$onSave.'" title="Delete the record and close this window">Confirm</a></div>'."\n";
			print '<div style="float:left;padding-left:10px;"><a id="'.$form.'EditButtonCancel" class="easyui-linkbutton" data-options="iconCls:\'icon-cancel\'" onclick="$(\'#'.$win.'\').window(\'close\', false);" title="Close this window without making any changes">Cancel</a></div>'."\n";
		}
		else if ($mode == 'view')
			print '<div style="float:left;padding-left:10px;"><a id="'.$form.'EditButtonClose" class="easyui-linkbutton" data-options="iconCls:\'icon-cancel\'" onclick="$(\'#'.$win.'\').window(\'close\', false);" title="Close this window">Close</a></div>'."\n";
		else {
			print '<div style="float:left;padding-left:10px;"><a id="'.$form.'EditButtonSave" class="easyui-linkbutton" data-options="disabled:'.$disabled.',iconCls:\'icon-ok\'" onclick="'.$onSave.'" title="Save all changes and close this window">Save</a></div>'."\n";
			print '<div style="float:left;padding-left:10px;"><a id="'.$form.'EditButtonCancel" class="easyui-linkbutton" data-options="iconCls:\'icon-cancel\'" onclick="$(\'#'.$win.'\').window(\'close\', false);" title="Close this window without making any changes">Cancel</a></div>'."\n";
		}
	}

	// $win id the window if used for the jquery selector
	// $key is an assoc array containing the record key
	// $mode may be either 'new' or 'edit', 'delete', 'view' or 'editonly' (no copy or delete)
/*	function recordEdit        ($win, $form, $key, $mode='edit', $onSave=null, $onResize=null) */
	function recordPropertyEdit($win, $form, $key, $mode='edit', $onSave=null, $onResize=null) {

		if (!$onResize)
			$onResize = '$("#'.$form.'Panel").panel("resize", {height: h-94, width: w-34});';
		if (!$onSave)
			$onSave = $form.'EditSubmit(\'recordSaveConfirm\');';
	
		print '
			<div class="easyui-panel" id="'.$form.'Panel" data-options="border:false,onOpen: function(){'.$form.'EditOpen($(this));}" style="padding:0px;">
		';
//		$url = $this->realtimeDataClassFile."?cmd=loadSubscriptionList&userid=${userid}&book=${book}&legacy=${legacy}&mode=${mode}";
		$url = $this->realtimeDataClassFile.'?cmd=recordPropertyEditLoad';
		foreach($key as $k => $v)
			$url .= "&key[$k]=".urlencode($v);
		print '
			 <table id="'.$form.'" class="easyui-propertygrid" data-options="fit:true
					, showHeader:false
					, url:\''.$url.'\'
					, method:\'get\'
					, showGroup:true
					, scrollbarSize:16
					, columns: propertyColumns
					, onLoadSuccess: function(data) {
//						$(this).propertygrid(\'collapseGroup\');
						var group, g;
						g = -1;
						for (x=0; x<data.total; x++) {
							var row = data.rows[x];
							if (row.group != group) {
								group = row.group;
								g++;
							}
						}
						$(\'#'.$form.'EditButtonSave\').linkbutton({disabled: false});
						$(\'#'.$form.'EditButtonDelete\').linkbutton({disabled: false});
						$(\'#'.$form.'EditButtonConfirm\').linkbutton({disabled: false});
					}
				">
			</table>
';
		print '
			</div>
		';
		if ($win) {
			print '
				<div style="float:right;padding-top:10px;padding-bottom:10px;">
			';
			$this->recordEditButtons($win, $form, $mode, $onSave);
			print '
				</div>
			';
		}

		print '
			<script>
				var propertyColumns = [[
					{field: "name", width: 50},
					{field: "value", width: 50, formatter: function(value, row, index) {
						if (row.editor.type == "combobox") {
							var data;
							if (data = row.editor.options.data)
								for(var i=0;i<data.length;i++)
									if (data[i].code == value)
										return(data[i].text);
						}
						return(value);
					}}
				]];

				function '.$form.'Delete() {
					var href = \''.$this->realtimeDataClassFile.'?cmd=recordDelete&formName=addressForm&windowid='.$win.'&keydata='.json_encode($key).'\';
					$("#'.$win.'").window("refresh", href);
				}
				// Override the default datebox format function
				function '.$form.'EditSubmit() {
					var execOnSuccess = null;
					var cmd = '.$form.'EditSubmit.arguments[0];
					if ('.$form.'EditSubmit.arguments.length > 1)
						execOnSuccess = '.$form.'EditSubmit.arguments[1];

					var rows = $("#'.$form.'").propertygrid("getChanges");
					$.ajax({
						async: false,
						url: "'.$this->realtimeDataClassFile.'",
						dataType: "json",
						method: "post",
						data: {
							cmd: cmd,
							windowid: "'.$win.'",
							keydata: \''.str_replace("'", "\\'", json_encode($key)).'\',
							editmode: "'.$mode.'",
							rows: rows
						},
						success: function(data) {
//							var data = JSON.parse(data);
							if (data.result == "failure") {
//								alert(data.message)
								href ="'.$this->realtimeDataClassFile.'?cmd=onlineTrxMessage&onlineTrxID="+data.onlineTrxID;
								$("#onlineTrxResult").window({title: "Failure"});
								$("#onlineTrxResult").window({href: href});
								$("#onlineTrxResult").window("open", false);
							}
							else if ((data.result == "success") && execOnSuccess)
								execOnSuccess(data);
							else {
								$("#'.$win.'").window("close", false);
								$("#searchResults0").'.$this->dataGridClass.'("reload");
							}
						},
						error: function(data) {
							$.messager.alert("Error", "AJAX error in recordEdit() submit");
						}
					});
				}

				function '.$form.'EditOpen(p) {
					$("#'.$win.'").window({onResize: function(w, h){'.$onResize.'}})
		';
		if ($mode == 'new')
			print '$("#'.$win.'").window("setTitle", "'.$this->title.' - Enter a new record");';
		else if ($mode == 'delete')
			print '$("#'.$win.'").window("setTitle", "'.$this->title.' - Delete a record");';
		else
			print '$("#'.$win.'").window("setTitle", "'.$this->title.' - Edit");';
		print '
				}
			</script>
		';

	}

	function recordEdit($win, $form, $key, $mode='edit', $onSave=null, $onResize=null) {
		$sql = "select title, classfile from ONLINE_REPORT where classname = '".$form."'";
		if (($r = $this->querydb($sql)) && ($rec = mysql_fetch_assoc($r)) && ($rec['classfile'] > '')) {
			$this->realtimeDataClassFile = $rec['classfile'];
			$this->title = $rec['title'];
		}
		if (!$onResize)
			$onResize = '$("#'.$form.'Panel").panel("resize", {height: h-94, width: w-34});';
		if (!$onSave) {
			if ($mode == 'delete')
				$onSave = $form.'EditSubmit(\'recordDeleteConfirm\');';
			else
				$onSave = $form.'EditSubmit(\'recordSaveConfirm\');';
		}
	
		print '
			<div class="easyui-panel" id="'.$form.'Panel" data-options="onOpen: function(){'.$form.'EditOpen($(this));}" style="padding:20px;">
			<form id="'.$form.'" method="post" enctype="multipart/form-data">
				<fieldset style="border-width:0px;" id="'.$form.'FieldSet">
				<table cellpadding="4" style="width:100%;">
		';
//		print_r($rec);
		$fields = array();
		foreach($this->column as $col => $c) {
			if ($c->canEdit) {
				$fields[] = $col;
				$readonly = $options = $comma = '';
				if (is_array($c->editopts))
					$options = $this->arrayToDataOptions($c->editopts, $options);
				$style = 'width:'.$c->width.'px;';
				if (($mode == 'delete') || ($mode == 'view') || $c->readonly) {
					$style .= 'background-color:#f0f0e0;';
					$readonly = 'readonly';
					$options = $this->arrayToDataOptions(array('readonly' => true, 'disabled' => true), $options);
				}
				switch ($c->filtertype) {
				case 'input':	// Native input box, non-jeasy-ui
					$options = $readonly;
					if (is_array($c->editopts))
						foreach ($c->editopts as $k => $v)
							$options .= ' '.$k.'="'.$v.'"';
					$field = '<input '.$options.' id="'.$col.'" name="'.$col.'" style="'.$style.'">';
					break;
				case 'switchbutton':
				case 'datebox':
				case 'datetimebox':
//					if ($readonly == 'readonly')
//						$options = $this->arrayToDataOptions(array('disabled' => true), $options);
					$field = '<input '.$readonly.' id="'.$col.'" name="'.$col.'" class="easyui-'.$c->filtertype.'" style="'.$style.'" data-options="'.$options.'">';
					break;
				case 'combotree':
				case 'combobox':
					$id = $name = $col;
					$opts = array(
						'groupField'	=> 'group',
						'idField'	=> 'value',
						'textField'	=> 'text',
						'mode'		=> 'remote',
						'width'		=> $c->width,
						'editable'	=> false,
//						'url'		=> $this->realtimeDataClassFile.'?cmd=loadFilterCombo&id='.$id,
					);
					if ($c->filterlist > '')
						$opts['url'] = $this->realtimeDataClassFile.'?cmd=loadFilterCombo&id='.$id;

					if (is_array($c->editopts) && ($c->filtertype == 'combobox') && ($c->editopts['mode'] == 'remote')) {
						$opts['icons'] = "[{
							iconCls:'fa fa-times rd-clear rd-clear-wide'
							, handler: function(e) {
								\$(e.data.target).combobox('clear');
								\$(e.data.target).combobox('reload');
							}
						}]";
					}

					$options = $this->arrayToDataOptions($opts, $options);
					if (is_array($c->editopts))
						$options = $this->arrayToDataOptions($c->editopts, $options);
					if (($c->filtertype == 'combobox') && is_array($c->editopts) && in_array('multiple', $c->editopts) && $c->editopts['multiple'])
						$name .= '[]';
//					if ($readonly == 'readonly')
//						$options = $this->arrayToDataOptions(array('disabled' => true), $options);
					$field = '<input id="'.$id.'" name="'.$name.'" class="easyui-'.$c->filtertype.'" style="'.$style.'" data-options="'.$options.'">';
					break;
				case 'textarea':
					$opt = array('multiline' => true);
					if (is_array($c->editopts) && (!array_key_exists('height', $c->editopts)))
						$opt['height'] = 60;
					$options = $this->arrayToDataOptions($opt, $options);
				default:
					$field = '<input class="easyui-textbox" '.$readonly.' id="'.$col.'" name="'.$col.'" style="'.$style.'" data-options="'.$options.'">';
					break;
				}
				if (($c->filtertype == 'input') && ($c->editopts['type'] == 'hidden'))
					print $field;
				else
					print "<tr>
						<td valign='top' align='right'>".$c->title." : </td>
						<td>${field}</td>
					</tr>\n";
			}
		}

		if (is_array($this->additionalFields))
			foreach($this->additionalFields as $k => $v) 
				print "<input name='$k' id='$k' type='hidden' value=''>\n";

		if (($mode != 'new') && ($mode != 'copy'))
			foreach($key as $k => $v)
				if (!in_array($k, $fields))
					print "<input name='$k' id='$k' type='hidden' value='".urlencode($v)."'>\n";
		print '
				</table>
				</fieldset>
			</form>
			</div>
		';
		if ($win) {
			print '
				<div class="'.$form.'Buttons" style="float:right;padding-top:5px;">
				<div class="panel-loading">Loading....</div>
			';
//			$this->recordEditButtons($win, $form, $mode, $onSave);
			print '
				</div>
			';
		}
		
		print '
			<script>
				function '.$form.'Delete() {
					var href = \''.$this->realtimeDataClassFile.'?cmd=recordDelete&formName='.$form.'&windowid='.$win.'&keydata='.str_replace("'", "\\'", json_encode($key)).'\';
					$("#'.$win.'").window("refresh", href);
				}
				function '.$form.'Copy() {
					var href = \''.$this->realtimeDataClassFile.'?cmd=recordCopy&formName='.$form.'&windowid='.$win.'&keydata='.str_replace("'", "\\'", json_encode($key)).'\';
					$("#'.$win.'").window("refresh", href);
				}
				function '.$form.'EditSubmit() {
					var execOnSuccess = null;
					var cmd = '.$form.'EditSubmit.arguments[0];
					if ('.$form.'EditSubmit.arguments.length > 1)
						execOnSuccess = '.$form.'EditSubmit.arguments[1];

					if ((cmd == "recordDeleteConfirm") || $("#'.$form.'").form("validate")) {
//						$("#searchResults0").'.$this->dataGridClass.'("loading");

						$("#'.$form.'").form("submit", {
							url: "'.$this->realtimeDataClassFile.'",
							method: "post",
							onSubmit: function(param){
								param.cmd = cmd;
								param.windowid = "'.$win.'";
								param.keydata = \''.str_replace("'", "\\'", json_encode($key)).'\';
								param.editmode = "'.$mode.'";
								
							},
							success: function(data) {
								$("#'.$form.'FieldSet").attr("disabled", true);
								$("#onlineTrxResult").window("close", false);
								var data = JSON.parse(data);
								if (data.result == "failure") {
									href ="'.$this->realtimeDataClassFile.'?cmd=onlineTrxMessage&onlineTrxID="+data.onlineTrxID;
									$("#onlineTrxResult").window({title: "Failure"});
									$("#onlineTrxResult").window({href: href});
									$("#onlineTrxResult").window("open", false);
									$("#'.$form.'FieldSet").attr("disabled", false);
								}
								else if ((data.result == "success") && execOnSuccess)
									execOnSuccess(data);
								else {
									$("#'.$win.'").window("close", false);
									$("#searchResults0").'.$this->dataGridClass.'("reload");
								}
							},
							error: function(data) {
								$.messager.alert("Error", "AJAX error in recordEdit() submit");
							}
						});
						return(true);
			
			}
					return(false);
				}
				function '.$form.'EditOpen(p) {
			';
		if ($win)
			print '
					var o = $("#'.$win.'").window("options");
//					p.panel("resize", {height: o.height-'.$vmargin.', width: o.width-'.$hmargin.'});
					$("#'.$win.'").window({onResize: function(w, h){'.$onResize.'}})
			';
		if ($win) {
		if (($mode == 'new') || ($mode == 'copy'))
			print '$("#'.$win.'").window("setTitle", "'.$this->title.' - Enter a new record");';
		else if ($mode == 'delete')
			print '$("#'.$win.'").window("setTitle", "'.$this->title.' - Delete a record");';
		else if (($mode == 'edit') || ($mode == 'editonly'))
			print '$("#'.$win.'").window("setTitle", "'.$this->title.' - Edit");';
		else
			print '$("#'.$win.'").window("setTitle", "'.$this->title.'");';
		}

		if ($mode == 'new')
			$url = $this->realtimeDataClassFile.'?cmd=recordEditClear';
		else {
			$url = $this->realtimeDataClassFile.'?cmd=recordEditLoad';
			foreach($key as $k => $v)
				$url .= "&key[$k]=".urlencode($v);
		}
		print '
			$("#'.$form.'FieldSet").attr("disabled", true);
				$("#'.$form.'").form("load","'.$url.'");
				$("#'.$form.'").form({onLoadSuccess: function(data){
/*
					var v;
					if (v = $("#'.$form.'EditButtonSave"))
						v.linkbutton({disabled: false});
					if (v = $("#'.$form.'EditButtonDelete"))
						v.linkbutton({disabled: false});
					if (v = $("#'.$form.'EditButtonConfirm"))
						v.linkbutton({disabled: false});
					if (v = $("#'.$form.'EditButtonCopy"))
						v.linkbutton({disabled: false});
*/
					$.ajax({
						url: "'.$this->realtimeDataClassFile.'",
						data: {
							cmd: "recordEditButtons",
							win: "'.$win.'",
							form: "'.$form.'",
							mode: "'.$mode.'",
							onSave: "'.urlencode($onSave).'"
						},
						success: function(data) {
							var v;
							if (v = $(".'.$form.'Buttons")) {
								v.css("padding-top", "10px");
								v.html(data);
							}
							$(".'.$form.'Buttons .easyui-linkbutton").linkbutton();
						}
					});
					$("#'.$form.'FieldSet").attr("disabled", false);
				}});
		';


		print '
				}
			</script>
		';
	}

	function recordEditLoad($returnResult=false) {
			$leftjoin = array();
			if (is_array($this->leftjoin)) {
				foreach ($this->leftjoin as $f)
					$leftjoin[$f] = $f;	
			}
			else if ($this->leftjoin > '')
				$leftjoin[$this->leftjoin] = $this->leftjoin;	
			$sql = 'select ';
			$comma = '';
			foreach($this->column as $col => $c) {
				if ($c->canEdit) {
					if ($c->table)
						$sql .= $comma.$c->table.'.'.$c->field.' as `'.$col.'`';
					else
						$sql .= $comma.$c->field.' as `'.$col.'`';
					$comma = ', ';
					if (is_array($c->leftjoin)) {
						foreach ($c->leftjoin as $f)
							$leftjoin[$f] = $f;	
					}
					else if ($c->leftjoin > '')
						$leftjoin[$c->leftjoin] = $c->leftjoin;	
				}
			}
			if (is_array($this->additionalFields))
				foreach($this->additionalFields as $k => $v) {
					$sql .= $comma.$v.' as `'.$k."`\n";
					$comma = ', ';
				}
			$sql .= ' from '.$this->tableName.' as '.$this->tableAlias;
			if (count($leftjoin))
				foreach ($leftjoin as $j)
					$sql .= $j;
			$and = ' where ';
			if (is_array($_REQUEST['key']))
				foreach($_REQUEST['key'] as $k => $v) {
					$sql .= $and."($k = '".(mysql_real_escape_string($v))."')";
					$and = ' and ';
				}

			if ($r = $this->querydb($sql))
				$rec = mysql_fetch_assoc($r);
			$comma = '';
			$data = array();
			foreach($this->column as $k => $c) {
				if ($c->canEdit) {
//					$rec[$k] = strip_tags($rec[$k]);
//					$rec[$k] = str_replace("'", "\\'", $rec[$k]);
//					$rec[$k] = str_replace('&nbsp;', '', $rec[$k]);
//					$rec[$k] = str_replace("\r", '', $rec[$k]);
					if (($c->filtertype == 'combobox') && is_array($c->editopts) && in_array('multiple', $c->editopts) && $c->editopts['multiple']) {
						$rec[$k.'[]'] = explode(',', $rec[$k]);
						$k .= '[]';
					}
					else
						$rec[$k] = $this->fieldFormat($k, $rec, false);
//					print $comma.$k.": '".utf8_encode(str_replace("\n", "\\n", $rec[$k]))."'\n";
//					$data[$k] = utf8_encode(str_replace("\n", "\\n", $rec[$k]));
					if ($c->filtertype == 'datetimebox') {
						if ($d = strptime($rec[$k], '%e %b %Y %I:%M %p')) {
							$d['tm_year'] += 1900;
							$rec[$k] = $d['tm_year'].'/'.($d['tm_mon']+1).'/'.$d['tm_mday'].' '.$d['tm_hour'].':'.$d['tm_min'].':'.$d['tm_sec'];
						}
					}
					$data[$k] = ($rec[$k]);
					$comma = ',';
				}
			}
			if (is_array($this->additionalFields))
				foreach($this->additionalFields as $k => $v)
					$data[$k] = ($rec[$k]);
		$data['_debug_'] = $this->debugData;
		if ($returnResult)
			return($data);
		die(json_encode($data));
	}

	function recordPropertyEditLoad($returnResult=false) {
		$result = array('rows' => array());
		$leftjoin = array();
		if (is_array($this->leftjoin)) {
			foreach ($this->leftjoin as $f)
				$leftjoin[$f] = $f;	
		}
		else if ($this->leftjoin > '')
			$leftjoin[$this->leftjoin] = $this->leftjoin;	
		$sql = 'select ';
		$comma = '';
		foreach($this->column as $col => $c) {
			if ($c->canEdit) {
				if ($c->table)
					$sql .= $comma.$c->table.'.'.$c->field.' as `'.$col.'`';
				else
					$sql .= $comma.$c->field.' as '.$col;
				$comma = ', ';
				if (is_array($c->leftjoin)) {
					foreach ($c->leftjoin as $f)
						$leftjoin[$f] = $f;	
				}
				else if ($c->leftjoin > '')
					$leftjoin[$c->leftjoin] = $c->leftjoin;	
			}
		}
		$sql .= ' from '.$this->tableName.' as '.$this->tableAlias;
		if (count($leftjoin))
			foreach ($leftjoin as $j)
				$sql .= $j;
		$and = ' where ';
		foreach($_REQUEST['key'] as $k => $v) {
			$sql .= $and."($k = '".(mysql_real_escape_string($v))."')";
			$and = ' and ';
		}

		if ($r = $this->querydb($sql))
			$rec = mysql_fetch_assoc($r);
		$comma = '';
		foreach($this->column as $k => $c) {
			if ($c->canEdit) {

				$rec[$k] = strip_tags($rec[$k]);
//					$rec[$k] = str_replace("'", "\\'", $rec[$k]);
				$rec[$k] = str_replace('&nbsp;', '', $rec[$k]);
				$rec[$k] = str_replace("\r", '', $rec[$k]);
				$rec[$k] = $this->fieldFormat($k, $rec, false);
//					print $comma.$k.": '".utf8_encode(str_replace("\n", "\\n", $rec[$k]))."'\n";
//					$data[$k] = utf8_encode(str_replace("\n", "\\n", $rec[$k]));
				if ($c->filtertype == 'datetimebox') {
					if ($d = strptime($rec[$k], '%e %b %Y %I:%M %p')) {
						$d['tm_year'] += 1900;
						$rec[$k] = $d['tm_year'].'/'.($d['tm_mon']+1).'/'.$d['tm_mday'].' '.$d['tm_hour'].':'.$d['tm_min'].':'.$d['tm_sec'];
					}
				}
				$result['rows'][] = array(
						'id' => $k
						, 'name' => $c->title
						, 'value' => ($rec[$k])
						, 'editor' => 'text'
						, 'group' => $c->group
					);
				$data[$k] = ($rec[$k]);
				$comma = ',';
			}
		}
		$result['_debug_'] = $this->debugData;
		if ($returnResult)
			return($result);
		die(json_encode($result));
	}

	// Set default values for a new record
	function recordEditClear($returnResult=false) {
		$data = array();
		foreach($this->column as $k => $c)
			if ($c->canEdit)
				$data[$k] = $c->default;
		if ($returnResult)
			return($data);
		die(json_encode($data));
	}

	function recordDeleteConfirm($returnResult=false) {
		$sql = 'delete from '.$this->tableName;
		$key = (array)json_decode($_REQUEST['keydata']);
		if (is_array($key)) {
			$and = ' where ';
			foreach($key as $k => $v) {
				$k = preg_replace('/^'.$this->tableAlias.'\\./', '', $k);
				$sql .= $and."($k = '".mysql_real_escape_string($v)."')";
				$and = ' and ';
			}
			$trx = new onlineTrxHistory('Delete record', $this->title, $sql);
			if ($r = $this->querydb($sql)) {
				$data = array('result' => 'success', 'message' => 'OK', 'debug' => $this->debugData);
				if (!$returnResult)
					$data['onlineTrxID'] = $trx->write('success', 'OK', print_r($_REQUEST, true));
			}
			else {
				$message = mysql_error();
				$onlineTrxID = $trx->write('failure', $message, print_r($_REQUEST, true));
				$data = array('result' => 'failure', 'message' => $message, 'debug' => $this->debugData, 'onlineTrxID' => $onlineTrxID);
			}
		}
		else {
			$trx = new onlineTrxHistory('Delete record', $this->title, $sql);
			$message = 'Missing key data. Please try again.';
			$onlineTrxID = $trx->write('failure', $message, print_r($_REQUEST, true));
			$data = array('result' => 'failure', 'message' => $message, 'debug' => $this->debugData, 'onlineTrxID' => $onlineTrxID);
		}
		if ($returnResult)
			return($data);
		die(json_encode($data));
	}

	// If
	function recordSaveConfirm($returnResult=false) {
		$rec = array();
		foreach ($this->column as $col => $c)
			if (($c->filtertype == 'combobox') && is_array($c->editopts) && in_array('multiple', $c->editopts) && $c->editopts['multiple'] && (!isset($_REQUEST[$col])))
				$_REQUEST[$col] = '';
		if (($_REQUEST['editmode'] != 'new') && ($_REQUEST['editmode'] != 'copy')) {
			$leftjoin = array();
			if (is_array($this->leftjoin)) {
				foreach ($this->leftjoin as $f)
					$leftjoin[$f] = $f;	
			}
			else if ($this->leftjoin > '')
			$leftjoin[$this->leftjoin] = $this->leftjoin;	
			foreach($_REQUEST as $k => $v) {
				foreach ($this->column as $col => $c) {
					if ($col == $k) {
						if (is_array($c->leftjoin)) {
							foreach ($c->leftjoin as $f)
								$leftjoin[$f] = $f;	
						}
						else if ($c->leftjoin > '')
							$leftjoin[$c->leftjoin] = $c->leftjoin;	
					}
				}
			}

			$sql = 'update '.$this->tableName.' as '.$this->tableAlias;
			foreach ($leftjoin as $j)
				$sql .= $j;
			$comma = ' set ';
			foreach($_REQUEST as $k => $v) {
				foreach ($this->column as $col => $c) {
					if ($col == $k) {
						if ($c->filtertype == 'combobox') {
							if ($v == '')
								$v = null;
							if (is_array($c->editopts) && in_array('multiple', $c->editopts) && $c->editopts['multiple'] && is_array($v)) {
								$k = str_replace('[]', '', $k);
								$a = $v;
								$v = $sep = '';
								foreach($a as $b) {
									if ($b > '') {
										$v .= $sep.$b;
										$sep = ',';
									}
								}
							}
						}
						if ($c->filtertype == 'datetimebox') {
							if ($v) {
								$d = date_parse($v);
								$v = $d['year'].'-'.$d['month'].'-'.$d['day'].' '.$d['hour'].':'.$d['minute'].':'.$d['second'];
							}
						}
						if ($c->filtertype == 'datebox') {
							if ($v) {
								$d = date_parse($v);
								$v = $d['year'].'-'.$d['month'].'-'.$d['day'];
							}
						}
						if ($c->filteropts['prefix'] )
							$v = str_replace($c->filteropts['prefix'], '', $v);
						if ($c->filteropts['groupSeparator'] )
							$v = str_replace($c->filteropts['groupSeparator'], '', $v);
						if ($c->format == 'CURRENCY')
							$v = str_replace(array($this->currencySymbol,','), '', $v);
						if (!$c->readonly) {
							if ($v === null)
								$sql .= $comma."`".$c->table.'`.`'.$c->field."` = null\n";
							else
								$sql .= $comma."`".$c->table.'`.`'.$c->field."` = '".(mysql_real_escape_string($v))."'\n";
							$comma = ', ';
							$rec[$c->table.'.'.$c->field] = $v;
						}
						break;
					}
				}
			}
			$key = (array)json_decode($_REQUEST['keydata']);
			$and = ' where ';
			foreach($key as $k => $v) {
				$sql .= $and."($k = '".mysql_real_escape_string($v)."')";
				$and = ' and ';
			}
			$trx = new onlineTrxHistory('Edit record', $this->title, $sql);
		}
		else {
			$sql = 'insert into '.$this->tableName;
			$comma = ' set ';
			foreach($_REQUEST as $k => $v) {
				foreach ($this->column as $col => $c) {
					if (($c->table == $this->tableAlias) && ($col == $k)) {
						if (($c->filtertype == 'combobox') && is_array($c->editopts) && in_array('multiple', $c->editopts) && $c->editopts['multiple'] && is_array($v)) {
							$k = str_replace('[]', '', $k);
							$a = $v;
							$v = $comma = '';
							foreach($a as $b) {
//								if ($b > '') {
									$v .= $comma.$b;
									$comma = ',';
//								}
							}
						}
						if ($c->filtertype == 'datetimebox') {
							$d = date_parse($v);
							$v = $d['year'].'/'.$d['month'].'/'.$d['day'].' '.$d['hour'].':'.$d['minute'].':'.$d['second'];
						}
						if ($c->filtertype == 'datebox') {
							$d = date_parse($v);
							$v = $d['year'].'/'.$d['month'].'/'.$d['day'];
						}
						if ($c->filteropts['prefix'] )
							$v = str_replace($c->filteropts['prefix'], '', $v);
						if ($c->filteropts['groupSeparator'] )
							$v = str_replace($c->filteropts['groupSeparator'], '', $v);
						if ($c->format == 'CURRENCY')
							$v = str_replace(array($this->currencySymbol,','), '', $v);
						if (!$c->readonly) {
							if ($v === null)
								$sql .= $comma."`".$c->field."` = null\n";
							else
								$sql .= $comma."`".$c->field."` = '".mysql_real_escape_string($v)."'";
							$comma = ', ';
							$rec[$c->field] = $v;
						}
						break;
					}
				}
			}
			$trx = new onlineTrxHistory('New record', $this->title, $sql);
		}
//die(print_r($_REQUEST, true));
//print $sql;
		if ($r = $this->querydb($sql)) {
			if (($_REQUEST['editmode'] == 'new') || ($_REQUEST['editmode'] == 'copy'))
				$rec['insertid'] = mysql_insert_id();
			$data = array('result' => 'success', 'message' => 'OK', 'record' => $rec, 'debug' => $this->debugData);
			if (!$returnResult)
				$data['onlineTrxID'] = $trx->write('success', 'OK', print_r($_REQUEST, true));
		}
		else {
			$message = mysql_error();
			$onlineTrxID = $trx->write('failure', $message, print_r($_REQUEST, true));
			$data = array('result' => 'failure', 'message' => $message, 'record' => $rec, 'debug' => $this->debugData, 'onlineTrxID' => $onlineTrxID);
		}
		$data['request'] = $_REQUEST;
		if ($returnResult)
			return($data);
		if (defined('JSON_HEX_TAG'))
			die(json_encode($data, JSON_HEX_TAG));
		else
			die(json_encode($data));
	}

	private function directoryListSort($a, $b) {
		if ($a['type'] != $b['type'])
			return(strcmp($a['type'], $b['type']));
		return(strcasecmp($a['text'], $b['text']));
	}

	private function directoryList($rootPath, $branch, $opt) {
		if ($dir = scandir($rootPath.'/'.$branch)) {
		$list = array();
		foreach ($dir as $k => $d) {
			// No dot files or folders
			if (in_array('nodot', $opt) && preg_match('/^\./', $d))
				continue;
			$filename = ($branch > '')?($branch.'/'.$d):$d;
			$filepath = $rootPath.'/'.$filename;
			if (filetype($filepath) == 'link')
				$filename = readlink($filepath);
			$list[] = array(
				'id'	=> $filename,
				'text'	=> $d,
				'type'	=> filetype($filepath),
				'size'	=> filesize($filepath),
			);
		}
		// Sort and list directories first
		if (in_array('sort', $opt))
			usort($list, array($this,'directoryListSort'));
		return($list);
		}
	}

	public function directoryComboTree($rootPath, $select=null, $pattern=null, $branch='', $maxdepth=0, $opt=array('nodot', 'sort'), $depth=0) {
		$list = array();
		if ($tree = @$this->directoryList($rootPath, $branch, $opt)) {
			foreach($tree as $k => $node) {
				if ($node['type'] == 'dir') {
					$pathname = ($branch > '')?($branch.'/'.$node['text']):$node['text'];
					if ((($maxdepth == 0) || ($depth < $maxdepth)) && ($c = $this->directoryComboTree( $rootPath, $select, $pattern, $pathname, $maxdepth, $opt, $depth+1))) {
						$node['state'] = ($select && (strncmp($pathname, dirname($select), strlen($pathname)) == 0))?'open':'closed';
						$node['children'] = $c;
						if (is_null($pattern) || preg_match($pattern, $node['id'].'/'))
							$list[] = $node;
					}
				}
				else if (($node['type'] == 'file') && (is_null($pattern) || preg_match($pattern, $node['id'])))
					$list[] = $node;
			}
		}
		return($list);
	}

}


?>