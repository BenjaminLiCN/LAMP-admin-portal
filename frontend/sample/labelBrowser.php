<?php
	include('../session.php');
	online_session_start();

	include('dataLookup.class.php');
	
//	include('norwoodCustomerBrowser.class.php');

	class labelBrowserClass extends dataLookup {

		var $customSettings = array(
			'SortByColumn' => 'LongName',
			'SortByDirection' => 'asc',
			'showImageSize' => null,
			'showImageColumns' => null,
			'showImageRows' => null,
			'showSide' => null,
			'showWatermark' => false,
			'excludeNoImage' => false,
			'excludeObsolete' => false,
			'includeRoyalty' => true,
			'filterRules' => null,
			'showStockLabels' => true,
			'showHorticultural' => true,
			'excludeNorwoodLabels' => false,
			'showCommercial' => true,
			'favouritesListSelect' => '',
			'showSelected' => false,
			'jobBagFilter' => '',
			
			'resultsperpage' => 24,
			'pageNo'	=> 1,

//			'downloadDirectory' => '/var/tmp',	// Directory used to create downloads
//			'downloadDelete' => false,		// Delete temporary files after download?
			'downloadNotify' => 0,			// Warn/notify users that the download has been registered (set to '1=yes' or '0=no')
		);

		function __construct($classFile='') {

			$this->column['PKEY'] = new dataLookupColumn(
				array(
					'title' =>	'Label code',
					'table' =>	'c',
					'field' =>	'PKEY',
					'width' =>	200,
//					'hidden'	=> false,
//					'align'	=>	'left',
//					'filtertype'	=> 'combobox',
//					'filterlist'	=> "select distinct NAME as value, NAME as text from KF_WORDERS_GROUP",
//					'editopts'	=> array('editable' => true),
				)
			);

			$this->column['LongName'] = new dataLookupColumn(
				array(
					'title' =>	'Description',
					'table' =>	'c',
					'field' =>	'LongName',
					'width' =>	800,
					'format'	=> 'MIXEDCASE',
//					'hidden'	=> false,
//					'align'	=>	'left',
//					'filtertype'	=> 'combobox',
//					'filterlist'	=> "select distinct NAME as value, NAME as text from KF_WORDERS_GROUP",
//					'editopts'	=> array('editable' => true),
				)
			);

			$this->column['CommonName'] = new dataLookupColumn(
				array(
					'title' =>	'Common name/Barcode',
					'table' =>	'c',
					'field' =>	'CommonName',
					'width' =>	360,
					'format'	=> 'MIXEDCASE',
					'hidden'	=> true,
//					'align'	=>	'left',
//					'filtertype'	=> 'combobox',
//					'filterlist'	=> "select distinct NAME as value, NAME as text from KF_WORDERS_GROUP",
//					'editopts'	=> array('editable' => true),
				)
			);

			$this->column['ShapeCode'] = new dataLookupColumn(
				array(
					'title' 	=> 'Shape no.',
					'table' =>	'c',
					'field' =>	'ShapeCode',
					'width' 	=> 200,
					'hidden'	=> true,
					'format'	=> 'SHAPEURL',
//					'filtertype'	=> 'combobox',
//					'filterlist'	=> "select distinct CODE as value, NAME as text from KF_COGSACCT_ERP order by 2",
				)
			);

			$this->column['ShapeName'] = new dataLookupColumn(
				array(
					'title' 	=> 'Shape name',
					'table' =>	'c',
					'field' =>	'ShapeName',
					'width' 	=> 480,
					'hidden'	=> true,
					'format'	=> 'MIXEDCASE',
//					'filtertype'	=> 'combobox',
//					'filterlist'	=> "select distinct CODE as value, NAME as text from KF_COGSACCT_ERP order by 2",
				)
			);

			$this->column['CustomerCode'] = new dataLookupColumn(
				array(
					'title' =>	'Customer/owner no.',
					'table' =>	'c',
					'field' =>	'CustomerCode',
					'width' =>	200,
					'hidden'	=> true,
					'align'	=>	'left',
					
				)
			);

			$this->column['CustomerName'] = new dataLookupColumn(
				array(
					'title' =>	'Customer/owner name',
					'table' =>	'c',
					'field' =>	'CustomerName',
					'width' =>	800,
					'format'	=> 'MIXEDCASE',
					'hidden'	=> true,
					'align'	=>	'left',
					
				)
			);

			$this->column['ProductType'] = new dataLookupColumn(
				array(
					'title' =>	'Product type',
					'table' =>	'c',
					'field' =>	'TYPE',
					'width' =>	200,
					'align'	=>	'left',
					'format' => 	'PRODUCTTYPE',
					'filtertype'	=> 'combobox',
					'filterlist'	=> "select 'S' as value, 'Stock' as text union all select 'J' as value, 'Custom' as text",
//					'filteropts'	=> array(
//						'data' => array(
//							array('code' => 'S', 'value' => 'Stock'),
//							array('code' => 'J', 'value' => 'Custom'),
//						),
//					),
				)
			);

			$this->column['PriceGrpCode'] = new dataLookupColumn(
				array(
					'title' 	=> 'Price group code',
					'table' =>	'c',
					'field' =>	'PriceGrpCode',
					'width' 	=> 200,
					'hidden'	=> true,
//					'format'	=> 'MIXEDCASE',
//					'filtertype'	=> 'combobox',
//					'filterlist'	=> "select distinct CODE as value, NAME as text from KF_COGSACCT_ERP order by 2",
				)
			);

			$this->column['PriceGrpName'] = new dataLookupColumn(
				array(
					'title' 	=> 'Price group description',
					'table' =>	'c',
					'field' =>	'PriceGrpName',
					'width' 	=> 480,
					'hidden'	=> true,
					'format'	=> 'MIXEDCASE',
//					'filtertype'	=> 'combobox',
//					'filterlist'	=> "select distinct CODE as value, NAME as text from KF_COGSACCT_ERP order by 2",
				)
			);

			$this->column['StyleCode'] = new dataLookupColumn(
				array(
					'title' 	=> 'Style code',
					'table' =>	'c',
					'field' =>	'StyleCode',
					'width' 	=> 200,
					'hidden'	=> true,
//					'format'	=> 'MIXEDCASE',
//					'filtertype'	=> 'combobox',
//					'filterlist'	=> "select distinct CODE as value, NAME as text from KF_COGSACCT_ERP order by 2",
				)
			);

			$this->column['StyleName'] = new dataLookupColumn(
				array(
					'title' 	=> 'Style description',
					'table' =>	'c',
					'field' =>	'StyleName',
					'width' 	=> 480,
					'hidden'	=> true,
					'format'	=> 'MIXEDCASE',
//					'filtertype'	=> 'combobox',
//					'filterlist'	=> "select distinct CODE as value, NAME as text from KF_COGSACCT_ERP order by 2",
				)
			);

			$this->column['RoyaltyCode'] = new dataLookupColumn(
				array(
					'title' 	=> 'Royalty parameter',
					'table' =>	'c',
					'field' =>	'RoyaltyCode',
					'width' 	=> 200,
					'hidden'	=> true,
				)
			);

			$this->column['RoyaltyName'] = new dataLookupColumn(
				array(
					'title' 	=> 'Royalty parameter description',
					'table' =>	'c',
					'field' =>	'RoyaltyName',
					'width' 	=> 480,
					'hidden'	=> true,
					'format'	=> 'MIXEDCASE',
				)
			);

			if ($_REQUEST['cmd'] <= '')
				$_SESSION[$classFile]['showSelected'] = $this->customSettings['showSelected'] = $this->showSelected = false;
			parent::__construct($classFile, $this->customSettings);
			if (!$this->showImageSize)
				$this->showImageSize = $_SESSION['user']['browserSize'];
			if (!$this->showImageColumns)
				$this->showImageColumns = $_SESSION['user']['browserColumns'];
			if (!$this->showImageRows)
				$this->showImageRows = $_SESSION['user']['browserRows'];
			if (!$this->showSide)
				$this->showSide = $_SESSION['user']['browserSide'];
			if (count($_SESSION['user']['royalty_owner']))
				$this->showWatermark = true;
	

//			$addr = $this->addressSelect;

			$this->title = 'Label browser';
			$this->tableName = 'NW_BROWSER';
			$this->tableAlias = "c";
			$this->resultDisplayHandler = 'outputGrid';
			$this->excludeReportOnCategories = 0;
			$this->tableSubset = '(1';
			$this->checkboxSelection = "rowCheckUncheck";
			$this->selectDistinct = true;

			$this->pagerButtons[] = "<a href='#' onclick='javascript:dl(\"".$_REQUEST['mode']."\", \"".$_REQUEST['dashboard']."\");' class='easyui-linkbutton' title='Download these results to Excel' data-options=\"iconCls:'".iconSave."',plain:true\"></a>";

			$this->tableIndex = 'LABELCODE';
			$this->additionalFields = array(
//				'COGSACCT' => "c.COGSACCT",
//				'NAME' => "c.NAME",
				'OBSOLETE' => "c.OBSOLETE",
				'LABELTYPE' => "c.TYPE",
				'LABELCODE' => "c.PKEY",
				'rowCheckID' => "concat_ws(':',c.TYPE,c.PKEY)",
				'checked' => "s.CODE",
				'ROTATE' => 'j.REVERSED xor j.ROTATED',
			);

			if (in_array($_REQUEST['cmd'], array('outputData', 'outputImages', 'printSelect', 'emailSelect', 'downloadQuery', 'rotateBacks'))) {
				$this->querydb("drop table if exists temp_select");
				$this->querydb("create temporary table temp_select ( TYPE char(1) not null, CODE char(20) not null, primary key (TYPE, CODE))");
				if (is_array($_SESSION['user']['selectedBidderQueue'])) {
					foreach ($_SESSION['user']['selectedBidderQueue'] as $v) {
						list($type, $code) = explode(':', $v);
						$sql = "insert into temp_select (TYPE, CODE) values ('$type', '$code')";
						$this->querydb($sql);
					}
				}
			}
			$this->leftjoin = array(
				" left join temp_select as s on (s.TYPE = c.TYPE) and (s.CODE = c.PKEY)",
				" left join ONLINE_JPEGIMAGE as j on (j.TYPE = c.TYPE) and (j.CODE = c.PKEY)",

			);
			if (count($_SESSION['user']['royalty_owner']))
				$this->leftjoin[] = " left join IN_MASTER_ROYALTY on (IN_MASTER_ROYALTY.ITEM_CODE = c.PKEY) and (IN_MASTER_ROYALTY.C_OWNER = 'R')";

			if (!$this->excludeNoImage)
				$this->tableSubset .=" and (c.PDF = 'Y')";
			if (!$this->excludeObsolete)
				$this->tableSubset .= " and (c.OBSOLETE != 'Y')";
			if ($this->jobBagFilter > '') {
				$sql = "select concat(CUSTOMER,'-',LABEL) as PKEY from ZN_JOB_BAG_LINE where JOB_BAG = '".$this->jobBagFilter."'";
				$comma = '';
				if ($r = $this->querydb($sql)) {
					$this->tableSubset .= " and (c.PKEY in (";
					while ($rec = mysql_fetch_assoc($r)) {
						$this->tableSubset .= $comma."'".$rec['PKEY']."'";
						$comma = ',';
					}
					$this->tableSubset .= "))";
				}
			}

			if ($this->showStockLabels && $this->showHorticultural && $this->showCommercial && $this->includeRoyalty)
				;
			else {
				$or = '';
				$this->tableSubset .= ' and (0';
				if ($this->showStockLabels)
					$this->tableSubset .= " or (((c.CustomerCode < '') or (c.CustomerCode is null)) and (c.TYPE = 'S'))";
				if ($this->showHorticultural)
					$this->tableSubset .= " or ((c.TYPE = 'J') and (c.GROUP_CODE = 'NUR'))";
				if ($this->showCommercial)
					$this->tableSubset .= " or ((c.TYPE = 'J') and (c.GROUP_CODE = 'COM'))";
				if (!$this->excludeNorwoodLabels)
					$this->tableSubset .= " or ((c.TYPE = 'J') and (c.RoyaltyCode = '000000'))";
				if ($this->includeRoyalty)
					$this->tableSubset .= " or ((c.CustomerCode > '') and (c.TYPE = 'S'))";
				$this->tableSubset .= ')';
			}
			
			if (!$this->excludeNorwoodLabels)
				$this->tableSubset .=" and ((c.TYPE <> 'J') or (c.CustomerCode <> '000000'))";

			if ($this->showSelected) {
				$this->tableSubset .= " and (s.CODE > '')";
			}
			$this->tableSubset .= ')';
//			if (!$this->includeRoyalty)
//				$this->tableSubset .= " and ((c.CustomerCode is null) or (c.CustomerCode = '') or (c.TYPE != 'S'))";

			$this->querydb("drop table if exists temp_labeltype");
			$this->querydb("create temporary table temp_labeltype select 'S' as CODE, 'Stock' as NAME union all  select 'J' as CODE, 'Custom' as NAME");
			$this->querydb("alter table temp_labeltype add primary key (CODE)");

			if (count($_SESSION['user']['royalty_owner']) || count($_SESSION['user']['custno'])) {
				$this->tableSubset .= " and (";
				if (count($_SESSION['user']['royalty_owner'])) {
					$this->tableSubset .= "(IN_MASTER_ROYALTY.C_ROYALTY_PAID_TO in (";
					$comma = '';
					foreach ($_SESSION['user']['royalty_owner'] as $c) {
						$this->tableSubset .= $comma."'".$c."'";
						$comma = ',';
					}
					$this->tableSubset .= "))";
				}
				else
					$this->tableSubset .= "0";
				$this->tableSubset .= " or ";
				if (count($_SESSION['user']['custno'])) {
					$this->tableSubset .= "(c.customerCode in (";
					$comma = '';
					foreach ($_SESSION['user']['custno'] as $c) {
						$this->tableSubset .= "$comma'$c'";
						$comma = ', ';
					}
					$this->tableSubset .= "))";
					$and = ' and ';
				}
				else
					$this->tableSubset .= "0";
				$this->tableSubset .= ")";
			}

		}

		function excelFormat(&$workbook, $rec, $field, $row, $col) {
			$ret = parent::excelFormat($workbook, $rec, $field, $row, $col);
			if ($rec['OBSOLETE'] == 'Y')
				$workbook->getActiveSheet()->getStyle($this->XLScoord($row, $col))->applyFromArray(array('font' => array('color' => array('rgb' => 'FF0000'))));
		}

		function fieldFormat($field, $rec, $htmlmode=true) {
			$column = $this->column[$field];
			switch($column->format) {
			case 'PRODUCTTYPE':
				$ret = $rec[$field];
				if ($ret == 'S')
					$ret = 'Stock';
				else if ($ret == 'J')
					$ret = 'Custom';
				break;
			case 'SHAPEURL':
				$parm = $rec[$field];
				$ret = "<a onclick='javascript:showDetailCommonEntry(event,\"shape:$parm\");'>".$parm."</a>";
				break;
			default:
				$ret = parent::fieldFormat($field, $rec, $htmlmode);
			}
			return($ret);
		}

		function outputGrid() {
			$n = $_SESSION[$this->realtimeDataClassFile]['resultsperpage'] = $this->resultsperpage = ($this->showImageColumns * $this->showImageRows);
			$options = array(
				'idField' => 'rowCheckID',
				'pageList' => '~~['.$this->resultsperpage.']',
				'onLoadSuccess' => "function(data) {
					var pkey = '';
					var n = 0;
					$('#debugWindow').window({content: data.debug});
					$.each(data.rows, function(index, d) {
						pkey += '&pkey[]='+d.LABELCODE;
						if (++n >= $n)
							return(false);
					});
					$('#menuBasicFieldSet').attr('disabled', false);
					$('#imagePanel').panel('refresh','".$this->realtimeDataClassFile."?cmd=outputImages'+pkey);
				}",
				'rowStyler' => "function(index, row) { if (row.OBSOLETE == 'Y') return 'color:red;';}",
			);
			$data = parent::outputGrid($options);
		}

		function getImageModeTag($rec) {
			$frontData = htmlspecialchars(serialize(array($rec['LABELTYPE'],$rec[$this->tableIndex],0,$this->showImageSize,($this->showWatermark),($rec['OBSOLETE'] == 'Y'))));
			$backData = htmlspecialchars(serialize(array($rec['LABELTYPE'],$rec[$this->tableIndex],1,$this->showImageSize,($this->showWatermark),($rec['OBSOLETE'] == 'Y'))));
			$backData .= '&rotate='.$rec['ROTATE'];
			$border = "border='3' style='border-color: #ffffff;'";
			if ($rec['OBSOLETE'] == 'Y')
				$border = "border='3' style='border-color: red;'";
			if ($this->showSide == 1)
				return("<IMG SRC='/norwood/online/utilities/showLabelJPEG.php?data=$frontData' $border><br/>");
			else if ($this->showSide == 2)
				return("<IMG SRC='/norwood/online/utilities/showLabelJPEG.php?data=$backData' $border><br/>");
			else
				return("<IMG SRC='/norwood/online/utilities/showLabelJPEG.php?data=$frontData' $border><IMG SRC='/norwood/online/utilities/showLabelJPEG.php?data=$backData' $border><br/>");
		}

		// override the loadColumnSelection method so that a single selection in the "additional columns" combo
		// may display another column as well, e.g. selcting "price group" will display both the price group code and name
		// See also the sessionUpdate override method

		function loadColumnSelection() {
			$data = array();
			$data[] = array('id' => 'PKEY', 'name' => 'Label code');
			$data[] = array('id' => 'LongName', 'name' => 'Description');
			$data[] = array('id' => 'CommonName', 'name' => 'Common name/Barcode (S/J)');
			$data[] = array('id' => 'CustomerCode', 'name' => 'Royalty owner/Job bag customer (S/J)');
			$data[] = array('id' => 'ProductType', 'name' => 'Product type');
			$data[] = array('id' => 'ShapeCode', 'name' => 'Shape');
			$data[] = array('id' => 'PriceGrpCode', 'name' => 'Price group');
			$data[] = array('id' => 'StyleCode', 'name' => 'Style');
			if (in_array('nol_access', $_SESSION['user']['royalty_admin']) && (count($_SESSION['user']['royalty_owner']) == 0))
				$data[] = array('id' => 'RoyaltyCode', 'name' => 'Royalty parameters');
			die(json_encode($data));
		}

		function sessionUpdate() {
			if (isset($_REQUEST['columnSelection']) && is_array($_REQUEST['columnSelection'])) {
				$columnSelection = array();
				foreach ($_REQUEST['columnSelection'] as $k => $v) {
					if ($v == 'CustomerCode')
						$columnSelection[] = 'CustomerName';
					if ($v == 'ProductType')
						$columnSelection[] = 'ProductType';
					if ($v == 'ShapeCode')
						$columnSelection[] = 'ShapeName';
					if ($v == 'PriceGrpCode')
						$columnSelection[] = 'PriceGrpName';
					if ($v == 'StyleCode')
						$columnSelection[] = 'StyleName';
					if ($v == 'RoyaltyCode')
						$columnSelection[] = 'RoyaltyName';
					$columnSelection[$v] = $v;
				}
				$_REQUEST['columnSelection'] = $columnSelection;
			}
			parent::sessionUpdate();
		}

		// Override the normal result drill-down
		function getResultDrillDownLink($rec, $k=0) {
			$ret = " onclick='javascript:showDetailCommonEntry(event,\"label:".$rec[$this->orderByField[showLabelCode]]."\");'";
			return($ret);
		}

		function outputImages() {
			$selectedItems = $_SESSION['selectedItems'];

			if (is_array($_REQUEST['pkey'])) {
				$comma = '';
				$this->tableSubset .= ' and (c.PKEY in (';
				foreach ($_REQUEST['pkey'] as $v) {
					$this->tableSubset .= $comma."'".$v."'";
					$comma = ',';
				}
				$this->tableSubset .= '))';
			}

			$sql = $this->Query();

			$data = array();
			$data['rows'] = array();

			$this->querydb('drop table if exists temp1');
			$this->querydb('create temporary table temp1 '.$sql);
			if ($this->groupByClause > '')
				$sql = "select * from temp1";
			else
				$sql = "select distinct * from temp1";


			if ($r = $this->querydb($sql))
				$data['total'] = mysql_num_rows($r);
			if (!is_array($_REQUEST['pkey'])) {
				$page = $this->pageNo;
				$rows = $this->resultsperpage;
				$offset = ($page - 1) * $rows;
				$sql .= sprintf(" limit %d,%d", $offset, $rows);
			}

			print $this->htmlTableOpen('style="border-bottom:1px solid silver;" border="0" cellpadding="0" cellspacing="0" width="100%"');
			if ($result[1] = $this->querydb($sql)) {
				$recno = 0;

				$groupby = '~unmatchable~';
				while ($rec[1] = mysql_fetch_array($result[1])) {
					$code = $rec[1][$this->tableIndex];
						if (property_exists($this, 'allowImageGroupBy') && in_array($this->SortByColumn, $this->allowImageGroupBy)) {
							if ($rec[1][$this->SortByColumn] !== $groupby) {
							while (($recno % $this->showImageColumns) != 0) {
									print $this->htmlTableCell(1, '');
									$recno++;
								}
								foreach($this->orderByField as $k1 => $v)
									if ($v == $this->SortByColumn)
										break;
								foreach ($this->columnHeader as $k2 => $v)
									if ($k1 & $k2)
										break;
								print $this->htmlTableRowOpen('highlight');
								print $this->htmlTableCell($this->showImageColumns, $v.' - '.$this->fieldFormat($k1, $rec[1]));
								print $this->htmlTableRowClose();
								$groupby = $rec[1][$this->SortByColumn];
							}
						}
						if (($recno % $this->showImageColumns) == 0)
							print $this->htmlTableRowOpen('onmouseout="javascript:highlightRow(this,false);" onmouseover="javascript:highlightRow(this,true);"');
						$width = 100/$this->showImageColumns;
						$width = "class='salesHeaderColumn' align='center' width='$width%' valign='top'";
//						if (method_exists($this, 'getResultDrillDownLink'))
//							$width .= $this->getResultDrillDownLink($rec[1]);
						$width .= " onclick='javascript:showLabelDetail({PKEY: \"".$code."\"});'";
//						$width .= " onclick='javascript:showDetailCommonEntry(event,\"label:".$code."\");'";

						$cell = "\n";
//						if ($this->allowSelection) {
							$v = (($selectedItems[$this->allowSelection][$code] === true)||($selectedItems[$this->allowSelection][$code] == 'true'))?'on':'';
//							$cell = $this->htmlCheckBox('selectedItems['.$this->allowSelection.']['.$code.']', $v, "onclick='checkUncheck(event, this);'").'&nbsp;';
							$cell = $this->htmlCheckBox($rec[1]['rowCheckID'], ($rec[1]['checked']>''?'on':''), "class='tickbox' onclick='checkUncheck(event, this, \"".$rec[1]['rowCheckID']."\");'").'&nbsp;';
							$cell .= $this->highlight($this->matchingData[$this->tableIndex], $code).'<br/>';
//						}
						$cell .= $this->getImageModeTag($rec[1]);
						$cell .= '<table>';
						foreach($this->column as $col => $c) {
							if (is_array($this->columnSelection) && in_array($col, $this->columnSelection) && ($c->field != 'PKEY') && ($rec[1] > '')) {
								$cell .= '<tr>';
								$cell .= "<td valign='top' align='right'>".str_replace(' ', '&nbsp;', $c->title.' :')."</td>";
								$cell .= "<td valign='top'>".$this->fieldFormat($col, $rec[1]).'</td>';
								$cell .= '</tr>';
							}
						}
						$cell .= '</table>';

						print $this->htmlTableCell(1, $cell, $width);
					$recno++;
					if (($this->showImageMode) && (($recno % $this->showImageColumns) == 0))
						print $this->htmlTableRowClose();
				}
				if (($this->showImageMode) && ($recno % $this->showImageColumns)) {
					while ($recno % $this->showImageColumns) {
						print $this->htmlTableCell(1, '', "align='center', width=$width%");
						$recno++;
					}
					print $this->htmlTableRowClose();
				}
			}
			print $this->htmlTableClose();
		}

		function outputData() {
			$_SESSION[$this->realtimeDataClassFile]['pageNo'] = $_REQUEST['page'];
			$data = parent::generateOutputData();
			print (json_encode($data));
		}

		function loadJobBagFilter() {
			$q = $_REQUEST['q'];
			$maxRows = 50;
			$sql = sprintf("select distinct j.JOB_BAG as id, j.JOB_NAME as text
				 from ZN_JOB_BAG as j
				 where (j.JOB_BAG > '')
				 and ((j.JOB_BAG like '%%%s%%') or (j.JOB_NAME like '%%%s%%'))
			", $q, $q);
			$sql .= " order by 2 limit $maxRows";
			$r = $this->querydb($sql);
			$data = array();
			while ($rec = mysql_fetch_assoc($r)) {
				foreach($rec as $k => $v)
					if ($k == 'name')
						$v = ucwords(strtolower($v));
					$rec[$k] = htmlspecialchars($v);
				$data[] = $rec;
			}
			$data[0]['debug'] = $this->debugData;
			die(json_encode($data));
		}

		function menuBasic() {
			print '<form id="menuBasic" ><fieldset style="padding:0px; border-width:0px;" id="menuBasicFieldSet">';
			$updateFunction = "\$('#mainLayout').layout('panel', 'center').layout('panel', 'north').panel('refresh','".$this->realtimeDataClassFile."?cmd=".$this->resultDisplayHandler."')";
			$updateFunction = "\$('#resultPanel').panel('refresh','".$this->realtimeDataClassFile."?cmd=".$this->resultDisplayHandler."');";
			$this->columnSelectCombo($updateFunction);

			$updateFunction = "\$('#searchResults0').datagrid('reload');";
			$updateFunction .= "\$('#menuBasicFieldSet').attr('disabled', true);";
			if (count($GLOBALS['user']['royalty_owner'])) {
				$options = array(
					'excludeNoImage' => array('Missing images', 'Tick to include items without images'),
					'excludeObsolete' => array('Obsolete labels', 'Tick to include obsolete labels'),
				);
				$this->menuIncudeOptions($options, $updateFunction, '<b>Include</b>');
				return;
			}

			print '<div title="Enter the number or name of a job bag to show only those items">';
			{
				$name = 'jobBagFilter';
				print $this->htmlTableOpen("style='width:223px;'");
				print $this->htmlTableRowOpen();
				$data = null;
				$options = array(
					'url'		=> $this->realtimeDataClassFile,
					'delay'		=> 300,
					'width'		=> 160,
					'panelWidth'	=> 500,
					'multiple'	=> false,
					'idField'	=> 'id',
					'textField'	=> 'text',
					'mode'		=> 'remote',
					'method'	=> 'get',
					'fitColumns'	=> true,
					'columns'	=> "[[{field:'id',title:'Code',width:50},{field:'text',title:'Description',width:120}]]",
					'onBeforeLoad'	=> "function(param){
						param.cmd = 'loadJobBagFilter';
					}",
					'icons'		=> "[{
								iconCls:'fa fa-times rd-clear rd-clear-narrow'
								, handler: function(e) {
									\$(e.data.target).combogrid('clear');
									updateSessionData('".$this->realtimeDataClassFile."', {".$name.": ''}, function(){ $updateFunction; });
								}
							}]",
					'onUnselect'	=> "function(rec) {
						updateSessionData('".$this->realtimeDataClassFile."', {
							$name: $('#$name').combogrid('getValue')
						}, function(){ $updateFunction; });
					}",
					'onSelect'	=> "function(rec) {
						updateSessionData('".$this->realtimeDataClassFile."', {
							$name: $('#$name').combogrid('getValue')
						}, function(){ $updateFunction; });
					}",
					'onOpen'	=> "function() {
							$('#$name').combogrid('setValue', '".$this->jobBagFilter."');
					}",
				);
				$cell = $this->htmlComboBox(null, $this->settings[$name], $name, $options, 'easyui-combogrid');
				print $this->htmlTableCell(1, "Job bag:");
				print $this->htmlTableCell(1, $cell, "align='right'");
				print $this->htmlTableRowClose();

				print $this->htmlTableClose();
			}
			print '</div>';

			$options = array(
				'showStockLabels' => array('Stock labels', 'Norwood stock labels'),
				'includeRoyalty' => array('Royalty/Promotional labels', 'Untick to exclude royalty and promotional stock labels'),
			);
			$this->menuIncudeOptions($options, $updateFunction, '<b>Include stock labels</b>');

			$options = array(
				'showHorticultural' => array('Horticultural job bag labels', 'Horticultural customer print to order labels and other items'),
				'showCommercial' => array('Commercial job bag items', 'Commercial customer print to order items'),
			);
			$this->menuIncudeOptions($options, $updateFunction, '<b>Include job bag labels</b>');

			$options = array(
				'excludeNorwoodLabels' => array('Norwood item codes', 'Tick to include Norwood (000000) item codes'),
				'excludeNoImage' => array('Missing images', 'Tick to include items without images'),
				'excludeObsolete' => array('Obsolete labels', 'Tick to include obsolete labels'),
			);
			$this->menuIncudeOptions($options, $updateFunction, '<b>Other items</b>');
		print '</fieldset></form>';
		}
		
		function optionCombo($sql, $select, $name, $options, $title) {
			if (($r = $this->querydb($sql)) && ($rec = mysql_fetch_assoc($r))) {
				$sql = $rec['Lookup'];
				if ($r = $this->querydb($sql)) {
					$assoc = array();
					while ($rec = mysql_fetch_array($r))
						if ($rec[0] > '')
							$assoc[] = $rec;

					print $this->htmlTableRowOpen();
					print $this->htmlTableCell(1, $title);
//					print $this->htmlTableRowClose();
					
//					print $this->htmlTableRowOpen();
					$cell = $this->htmlComboBox($assoc, $select, $name, $options);
					print $this->htmlTableCell(1, $cell);
					print $this->htmlTableRowClose();
				}
			}
		}

		function imageOptions() {
			$updateFunction = "\$('#imagePanel').panel('refresh','".$this->realtimeDataClassFile."?cmd=outputImages')";
			$updateFunction = "\$('#resultPanel').panel('refresh','".$this->realtimeDataClassFile."?cmd=".$this->resultDisplayHandler."')";
			print $this->htmlTableOpen("style='padding:4px;'");

			$options = array(
				'width'	=> 120,
				'valueField'	=> 'code',
				'textField'	=> 'value',
				'editable'	=> false,
				'panelHeight'	=> 'auto',
				'onSelect'	=> "function(rec) {
					updateSessionData('".$this->realtimeDataClassFile."', {
						showImageSize: $('#showImageSize').combobox('getValue'),
						showImageColumns: $('#showImageColumns').combobox('getValue'),
						showImageRows: $('#showImageRows').combobox('getValue'),
						showSide: $('#showSide').combobox('getValue')
					}, function(){ $updateFunction; });
				}",
			);

			$sql = "select Lookup from ONLINE_USER_PARAMETERS where Parameter = 'browserSize'";
			$this->optionCombo($sql, $this->showImageSize, 'showImageSize', $options, 'Image size :');

			$sql = "select Lookup from ONLINE_USER_PARAMETERS where Parameter = 'browserColumns'";
			$this->optionCombo($sql, $this->showImageColumns, 'showImageColumns', $options, 'Image columns :');

			$sql = "select Lookup from ONLINE_USER_PARAMETERS where Parameter = 'browserRows'";
			$this->optionCombo($sql, $this->showImageRows, 'showImageRows', $options, 'Image rows :');

			$sql = "select Lookup from ONLINE_USER_PARAMETERS where Parameter = 'browserSide'";
			$this->optionCombo($sql, $this->showSide, 'showSide', $options, 'View sides :');

			if (count($GLOBALS['user']['royalty_owner']))
				;
			else {
				print $this->htmlTableClose();
				$options = array(
					'showWatermark' => array('Show watermark', 'Include a watermark on all images'),
				);
				$this->menuIncudeOptions($options, $updateFunction, 'Watermark:');
			}
		}

		function menuAdvanced() {
			$this->advancedSettingsMenu(false);
		}

		function outputDetailWindow() {

			$code = strip_tags($this->lookupDetailData['PKEY']);
			
			include_once('include/norwoodLabelDetail.class.php');
			$win = new norwoodLabelDetail($GLOBALS['realtimeConfig']['ROOT'].'/include/norwoodLabelDetail.class.php', 'lookupDetailWindow', $code, $this->showWatermark);
		}

		function favouritesListOptions() {
			print '<div id="selectionMenu1" class="easyui-panel" data-options="border:false,noheader:true,closed:false">';

				$hint = 'Only show the currently selected labels';
				$this->advancedSettingsMenuButton('Show selected labels only', $hint, 'fa fa-check', 'javascript:showSelectedToggle(true);', 0);

//				print '<p id="selectionTitle1" align="center">&nbsp;</p>';

				$hint = 'Untick all the selected results">Clear (untick) all selections';
				$this->advancedSettingsMenuButton('Clear (untick) all selections', $hint, 'fa fa-times-circle', 'javascript:rowCheckUncheck("reset", {});', 5, 'selectionTitle1');

				$hint = 'Load from favourites list';
				$this->advancedSettingsMenuButton('Load from favourites list', $hint, 'fa fa-arrow-up', 'javascript:rowCheckUncheck("load", {});', 5);

			print '</div>';

				print '<div id="selectionMenu2" class="easyui-panel" data-options="border:false,noheader:true,closed:true">';
				$hint = 'Show all the labels';
				$this->advancedSettingsMenuButton('Show all labels', $hint, 'fa fa-times', 'javascript:showSelectedToggle(false);', 0);

//				print '<p id="selectionTitle2" align="center">&nbsp;</p>';

				$hint = 'Untick all the selected results">Clear (untick) all selections';
				$this->advancedSettingsMenuButton('Clear (untick) all selections', $hint, 'fa fa-times-circle', 'javascript:rowCheckUncheck("reset", {});', 5, 'selectionTitle2');

				$hint = 'Send all the selected labels in an e-mail message';
				$this->advancedSettingsMenuButton('E-mail this selection', $hint, 'fa fa-envelope-o', 'javascript:emailPrintSelect("emailSelect", "Generating the e-mail");', 5);

				if (count($GLOBALS['user']['royalty_owner']) && count($GLOBALS['user']['custno']))
					;
				else {
					$hint = 'Print all the selected labels to your preferred printer';
					$this->advancedSettingsMenuButton('Print this selection', $hint, 'fa fa-print', 'javascript:emailPrintSelect("printSelect", "Printing the selection");', 5);
				}

				if ($this->showSide > 1) {
					$hint = 'Rotate the backs of all the selected images';
					$this->advancedSettingsMenuButton('Rotate label backs', $hint, 'fa fa-undo', 'javascript:rotateBacks();', 5);
				}

				$hint = 'Reset the selection and delete the favourites list selected below';
				$this->advancedSettingsMenuButton('Delete this favourites list', $hint, 'fa fa-bomb', 'javascript:deleteList();', 5);

				$hint = 'Save all the selected labels to the favourites list selected belo';
				$this->advancedSettingsMenuButton('Save to favourites list', $hint, 'fa fa-arrow-down', 'javascript:rowCheckUncheck("save", {});', 5);

			print '</div>
			';

			print '<div style="height:5px;"/>';

			$options = array(
				'editable' => true,
				'url' => $this->realtimeDataClassFile.'?cmd=loadFavouritesList',
			);
			print '<div title="Select an existing favourites list, or type the name of a new list here to create one">';
			$this->menuCombo('Favourites list:', 'favouritesListSelect', null, null, 'easyui-combobox', $options);
			print '</div>';

		}
		
		function loadFavouritesList() {
			$data = array(array('id' => '', 'text' => 'Select or type a new name here'));
			$sql = "select name as id, name as text from mailinglist_header order by 1";
			if ($r = $this->querydb($sql))
				while ($rec = mysql_fetch_assoc($r))
					$data[] = $rec;
			$data = array();
			foreach ($_SESSION['user']['favouritesLists']['label'] as $k => $v)
				$data[] = array('id' => $k, 'text' => $k);
			die(json_encode($data));
		}

		function rowCheckUncheck() {
//print_r($_REQUEST);
			$u = is_array($_SESSION['user']['selectedBidderQueue'])?$_SESSION['user']['selectedBidderQueue']:array();
			switch($_REQUEST['action']) {
			case 'selectall':
//				$this->optimisedQuery();
				$sql = $this->Query();
				$u = array();
				if ($r = $this->querydb($sql))
					while ($rec = mysql_fetch_assoc($r))
						$u[] = $rec['rowCheckID'];
				$_SESSION['user']['selectedBidderQueue'] = $u;
				break;
			case 'add':
				if (($existing = count($u)) <= 0)
					die(json_encode(array('result' => 'failure', 'message' => 'Nothing is selected')));
				if ($this->favouritesListSelect <= '')
					die(json_encode(array('result' => 'failure', 'message' => 'You need to select a favourites list first')));
				$new = 0;
				foreach($u as $id) {
					list($userid, $book) = explode(':', $id);
					$sql = "insert ignore into mailinglist_item (name, userid, book) values('".mysql_real_escape_string($this->favouritesListSelect)."', '$userid', '$book')";
					if ($message = mysql_error())
						die(json_encode(array('result' => 'failure', 'message' => $message, 'debug' => $this->debugData)));
					$r = $this->querydb($sql);
					$new += mysql_affected_rows();
				}
				$existing -= $new;
				$message = $new.' new items added to '.$this->favouritesListSelect;
				if ($existing)
					$message .= '<br/>('.$existing.' items already selected)';
				$_SESSION['user']['selectedBidderQueue'] = null;
				$data = array('result' => 'success', 'message' => $message, 'debug' => $this->debugData);
				die(json_encode($data));
			case 'reset':
				$_SESSION['user']['selectedBidderQueue'] = null;
				break;
			case 'onCheck':
				$u = array_merge(explode(',', $_REQUEST['rowData']['rowCheckID']), $u);
				$_SESSION['user']['selectedBidderQueue'] = array_unique($u);
				break;
			case 'onCheckAll':
				foreach ($_REQUEST['rowData'] as $r) {
					$u = array_merge(explode(',', $r['rowCheckID']), $u);
					$_SESSION['user']['selectedBidderQueue'] = array_unique($u);
				}
				break;
			case 'onUncheck':
				$v = array();
				$u = explode(',', $_REQUEST['rowData']['rowCheckID']);
				foreach ($_SESSION['user']['selectedBidderQueue'] as $b)
					if (!in_array($b, $u))
						$v[] = $b;
				$_SESSION['user']['selectedBidderQueue'] = $v;
				break;
			case 'onUncheckAll':
				$v = array();
				$u = array();
				foreach ($_REQUEST['rowData'] as $r)
					$u = array_merge(explode(',', $r['rowCheckID']), $u);
				if (is_array($_SESSION['user']['selectedBidderQueue']))
					foreach ($_SESSION['user']['selectedBidderQueue'] as $b)
						if (!in_array($b, $u))
							$v[] = $b;
				$_SESSION['user']['selectedBidderQueue'] = $v;
				break;
			case 'delete':
				unset($_SESSION['user']['favouritesLists']['label'][$_REQUEST['favouritesListSelect']]);
				$_SESSION['user']['selectedBidderQueue'] = null;
				break;
			case 'save':
				$_SESSION['user']['favouritesLists']['label'][$_REQUEST['favouritesListSelect']] = $_SESSION['user']['selectedBidderQueue'];
				break;
			case 'load':
				foreach ($_SESSION['user']['favouritesLists']['label'] as $k => $v)
					if ($k == $this->favouritesListSelect) {
						$a = array();
						foreach ($v as $label => $state) {
							if ($state === true) {
								if ($label[6] == '-')
									$a[] = 'J:'.$label;
								else
									$a[] = 'S:'.$label;
							}
							else
								$a[] = $state;
						}
						$u = array_merge($a, $u);
						$_SESSION['user']['selectedBidderQueue'] = array_unique($u);
					}
				break;
			}
			$n = count($_SESSION['user']['selectedBidderQueue']);
/*
			if ($n > 1)
				sort($_SESSION['user']['selectedBidderQueue']);
			if ($n == 1)
				$message = '1 item selected';
			else if ($n > 1)
				$message = $n.' items selected';
			else
				$message = 'No items selected';
*/
			$message = '&nbsp;&nbsp;Clear selection';
			if ($n >= 1)
				$message .= ' ('.$n.')';
			$data = array('result' => 'success', 'message' => $message, 'debug' => $this->debugData, 'count' => $n, 'q' => $_SESSION['user']['favouritesLists']['label']);
			die(json_encode($data));
		}

		function createLabelCID($product, $rec, $back, $jpeg) {
			$code = $rec['LABELCODE'];
			$usage = $rec['LABELTYPE'];
			$size = $this->showImageSize;
			$wm = $this->showWatermark;
			$targetFile = "/tmp/$jpeg";

			include "../utilities/showLabelJPEG.php";
		}
		
		function printSelect() {
			$trx = new onlineTrxHistory('Print selected labels', $this->title, $sql);
			if ($this->showSelected) {
				$printer = array();
				$sql = "select Lookup from ONLINE_USER_PARAMETERS where Parameter = 'labelBrowserPrinter'";
				if (($r = $this->querydb($sql)) && ($rec = mysql_fetch_assoc($r)) && ($r = $this->querydb($rec['Lookup'])))
					while ($rec = mysql_fetch_assoc($r))
						$printer[$rec['code']] = $rec['value'];
				$selectPrinter = $_SESSION['user']['labelBrowserPrinter'];
				$sql = $this->Query();
				$r = $this->querydb($sql);
				$op = "JDFP";
				$n = 0;
				while ($rec = mysql_fetch_array($r)) {
					$cmd = "/usr/local/bin/jdf_duplex_print $op $selectPrinter";
					$cmd .= " ".$rec['LABELCODE'];
					$this->debugData .= '# '.$cmd."<br/>\n";
					system($cmd);
					$n++;
				}
				$message = $n.' pages have been sent to printer '.$printer[$selectPrinter];
				$onlineTrxID = $trx->write('success', $message, print_r($_SESSION[$this->realtimeDataClassFile], true));
				$data = array('result' => 'success', 'debug' => $this->debugData, 'onlineTrxID' => $onlineTrxID);
			}
			else {
				$onlineTrxID = $trx->write('failure', $message, print_r($_SESSION[$this->realtimeDataClassFile], true));
				$data = array('result' => 'failure', 'debug' => $this->debugData, 'onlineTrxID' => $onlineTrxID);
			}
			die(json_encode($data));
		}

		function rotateBacks() {
			$sql = $this->Query();
			$r = $this->querydb($sql);

			$sql = "update ONLINE_JPEGIMAGE set REVERSED=(not REVERSED) where (CODE in ('~'";
			while ($rec = mysql_fetch_array($r)) {
				$sql .= ", '".$rec['LABELCODE']."'";
			}
			$sql .= "))";
			$this->querydb($sql);
			$this->showSelectedMode = 1;
			print $this->debugData;
		}

		function emailSelect() {
			require($GLOBALS['localPath']."online/phpmailer/class.phpmailer.php");

			if ($this->showSelected) {
				$mail = new PHPMailer();
				if ($GLOBALS['realtimeConfig']['SMTPHost']) {
					$mail->isSMTP();
					$mail->Host = $GLOBALS['realtimeConfig']['SMTPHost'];
				}
				else 
					$mail->isSendmail();
				$mail->From = $GLOBALS['realtimeConfig']['MailFrom'];
				$mail->Sender = $GLOBALS['realtimeConfig']['MailFrom'];
				$mail->FromName = $GLOBALS['realtimeConfig']['AppName'];
				$mail->AddAddress($_SESSION['user']['email'], $_SESSION['user']["fullname"]);
				$mail->Subject = "Selected labels";
				if ($_SESSION['loadedFavouritesList'][$this->allowSelection])
					$mail->Subject .= " - ".$_SESSION['loadedFavouritesList'][$this->allowSelection];

				$mail->IsHTML(true);
				$mail->Body = '<html>
				<head>
					<script>

					function ViewLabelPDF(data) {
						url = "http://www.norwood.com.au/norwood/online/utilities/showLabelPDF.php?data="+data;
						win = newWindow("itemdetails",url,800,600,"no");
						win.focus();
					}

					function ViewShapePDF(data) {
						url = "http://www.norwood.com.au/norwood/online/utilities/showShapePDF.php?data="+data;
						win = newWindow("itemdetails",url,800,600,"no");
						win.focus();
					}
					</script>

				</head>

				<body bgcolor="#FFFFFF" topmargin="0" leftmargin="0" marginwidth="0" marginheight="0">
				<table width="100%" border="1" cellpadding="0" cellspacing="0" style="font-size:8.5pt;font-family:Arial; color:black">
				';

				$colspan = ($this->showSide == 3)?($this->showImageColumns/2):$this->showImageColumns;
				$slideColumn = 0;
				$code = $pdf = '';
				$sql = $this->Query();
				$r = $this->querydb($sql);
				$trx = new onlineTrxHistory('E-mail selected labels', $this->title, $sql);
				while ($rec = mysql_fetch_array($r)) {
					if (($slideColumn % $colspan) == 0)
						$mail->Body .= "<tr class='normalLeft' >\n";
					$width = 99/$colspan;
					$other = "width='$width%' align='center' valign=top";
					if ($this->allowSelection == 'stock')
						$rec['LABELTYPE'] = 'S';
					if ($this->allowSelection == 'custom')
						$rec['LABELTYPE'] = 'J';
					if ($rec['LABELTYPE'] == 'S') {
						$code = $rec['LABELCODE'];
						$data = str_replace(array('"', '&'), array('%22', '%26'), serialize(array('S',$code)));
						$pdf = "http://www.norwood.com.au/norwood/online/utilities/showLabelPDF.php?data=$data";
						$mail->Body .= "<td $other><span style='font-size:8.5pt;font-family:Arial; color:black'>$code<br>";
					}
					else if ($rec['LABELTYPE'] == 'J') {
						$code = $rec['LABELCODE'];
						$part = split('-', $code);
						$data = str_replace(array('"', '&'), array('%22', '%26'), serialize(array('J',$code)));
						$pdf = "http://www.norwood.com.au/norwood/online/utilities/showLabelPDF.php?data=$data";
						$mail->Body .= "<td $other><span>$part[0]-$part[1]<br>";
					}
					$mail->Body .= "<A href=\"$pdf\"><IMG SRC='cid:$code' BORDER='0'></A>";
					if ($this->showSide & 2)
						$mail->Body .= "<A href=\"$pdf\"><IMG SRC='cid:$code.back' BORDER='0'></A>";
					$mail->Body .= "<table style='font-size:8.5pt;font-family:Arial; color:black'>\n";
					foreach( $this->column as $col => $c)
						if (is_array($this->columnSelection) && in_array($col, $this->columnSelection))
							$mail->Body .= '<tr><td align="right">'.$c->title.' :</td><td><b>'.$this->fieldFormat($col, $rec)."</b></td></tr>\n";
					$mail->Body .= "</table>\n";
					$mail->Body .= "</span><br/></td>\n";
					$slideColumn++;
					if (($slideColumn % $colspan) == 0)
						$mail->Body .= "</tr>\n";
				}
				while ($slideColumn % $colspan) {
					$mail->Body .= "<td>&nbsp;</td>";
					$slideColumn++;
				}
				$mail->Body .= '</table>
				</body>
				</html>
				';

				mysql_data_seek($r, 0);
				while ($rec = mysql_fetch_array($r)) {
					$code = $rec['LABELCODE'];
					if ($this->allowSelection == 'stock')
						$rec['LABELTYPE'] = 'S';
					if ($this->allowSelection == 'custom')
						$rec['LABELTYPE'] = 'J';
					if ($this->allowSelection == 'shape') {
						$cid = $code = $rec[$this->orderByField[showShapeCode]];
					
						$rootpath = "/images/jpeg";

						if ($this->showImageSize == 1) $subdir = "/thumbnail/";
						if ($this->showImageSize == 2) $subdir = "/tagpic/";
						if ($this->showImageSize == 3) $subdir = "/preview/";

						
						$path = $rootpath.$subdir.'shapes/'.sprintf('%02d/%05d.jpg', $code/1000, $code);
						
						if (!file_exists($path))
							$path = $rootpath.$subdir.'noimage.jpg';
						$jpeg = basename($path);
						$mail->AddEmbeddedImage($path, $cid, $jpeg, 'base64', 'image/jpeg');
					}
					else {
						$cid = $code;
						$jpeg = $code.".jpg";
						if ($this->showSide & 1) {
							$this->createLabelCID($product, $rec, 0, $jpeg);			
							if (file_exists("/tmp/$jpeg"))
								$mail->AddEmbeddedImage("/tmp/$jpeg", $cid, "$jpeg", 'base64', 'image/jpeg');
						}
						$cid = $code.".back";
						$jpeg = $code."b.jpg";
						if ($this->showSide & 2) {
							$this->createLabelCID($product, $rec, 1, $jpeg);
							if (file_exists("/tmp/$jpeg"))
								$mail->AddEmbeddedImage("/tmp/$jpeg", $cid, "$jpeg", 'base64', 'image/jpeg');
						}
					}
				}
				if ($mail->send())
					$message = 'The e-mail has been sent to '.$_SESSION['user']['email'];
				else
					$message = "ERROR. Failed to send the e-mail. ".$mail->ErrorInfo;

				mysql_data_seek($r, 0);

				while ($rec = mysql_fetch_array($r)) {
					$code = $rec['LABELCODE'];
					$jpeg = $code.".jpg";
					if (file_exists("/tmp/$jpeg"))
						unlink("/tmp/$jpeg");
					$jpeg = $code."b.jpg";
					if (file_exists("/tmp/$jpeg"))
						unlink("/tmp/$jpeg");
				}
				
				$onlineTrxID = $trx->write('success', $message, print_r($_SESSION[$this->realtimeDataClassFile], true));
				$data = array('result' => 'success', 'debug' => $this->debugData, 'onlineTrxID' => $onlineTrxID);
			}
			else {
				$onlineTrxID = $trx->write('failure', $message, print_r($_SESSION[$this->realtimeDataClassFile], true));
				$data = array('result' => 'failure', 'debug' => $this->debugData, 'onlineTrxID' => $onlineTrxID);
			}
			die(json_encode($data));
		}
	}

	$classFile = $_SERVER['PHP_SELF'];
	$page = new labelBrowserClass($classFile);

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

	session_write_close();

?>
<script>
	var savedFilterRules = [];

	function showLabelDetail(rowData) {
		var classfile = '<?php print $page->realtimeDataClassFile; ?>';
		updateSessionData(classfile, {lookupDetailData: rowData}, function() {
			var win = $('#<?php print $page->lookupDetailWindow; ?>');
			if (win) {
				var url = classfile+'?cmd=outputDetailWindow';
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
		});
	}

	function addRecord() {
		var win = $('#lookupDetailWindow');
		var url = '<?php print $page->realtimeDataClassFile; ?>?cmd=outputDetailWindow&mode=new';
		win.window('open');
		win.window('refresh', url);
	}

	function checkUncheck(event, element, id) {
		var index = $('#searchResults0').datagrid('getRowIndex', id);
		event.returnValue = true;
		event.cancelBubble = true;
		if (element.checked)
			$('#searchResults0').datagrid('checkRow', index);
		else
			$('#searchResults0').datagrid('uncheckRow', index);
	}

	function deleteList() {
		$.messager.confirm('Delete this favourites list', 'Please confirm?', function(r){
			if (r) {
				var d = $("#favouritesListSelect").combobox("getText");
				data = {cmd: 'rowCheckUncheck', action: 'delete', favouritesListSelect: d};
				updateSessionData('<?php print $classFile; ?>', {favouritesListSelect: d}, function() { 
					$.ajax({
						url: '<?php print $page->realtimeDataClassFile; ?>',
						dataType: 'json',
						method: 'post',
						data: data,
						success: function(data) {
							$('#favouritesListSelect').combobox('reload');
							$('#favouritesListSelect').combobox('setValue', '');
							showSelectedToggle(false);
						}
					});
				});
			}
		});
	}

	var selectedLabelCount = 0;
	function rowCheckUncheck(action, data) {
		var v;
		// Propagate check/uncheck actions from the datagrid to the image pane
		if ((action == 'onCheckAll') || (action == 'selectall'))
			$('.tickbox').prop('checked', true);
		if ((action == 'onUncheckAll') || (action == 'reset'))
			$('.tickbox').prop('checked', false);
		if ((action == 'onCheck') && (v = document.getElementById(data.rowData.rowCheckID)))
			v.checked = true;
		if ((action == 'onUncheck') && (v = document.getElementById(data.rowData.rowCheckID)))
			v.checked = false;
		if (((action == 'load') || (action == 'save')) && ($('#favouritesListSelect').combobox('getValue') <= '')) {
			alert('Make a selection from the favourites list first');
			return;
		}
			
		v = $('#searchResults0').datagrid('getData');
		if ((action == 'selectall') && (v.total > 800)) {
			alert('You cannot select more than 800 labels');
			action = '';
		}
		if (action == 'save') {
			var d = $("#favouritesListSelect").combobox("getText");
			data = {cmd: 'rowCheckUncheck', action: action, favouritesListSelect: d};
			updateSessionData('<?php print $classFile; ?>', {favouritesListSelect: d}, function() { 
				$.ajax({
					url: '<?php print $page->realtimeDataClassFile; ?>',
					dataType: 'json',
					method: 'post',
					data: data,
					success: function(data) {
						$('#favouritesListSelect').combobox('reload');
						$('#favouritesListSelect').combobox('setValue', d);
					}
				});
			});
			return;
		}
		else if ((action == 'onCheckAll') || (action == 'onUncheckAll')) {
			// Reduce the size of data when using CheckAll/UncheckAll
			// Otherwise this fails on the MG server
			var d = {cmd: 'rowCheckUncheck', action: action, rowData: []};
			for(var i=0; i<data.rowData.length; i++)
				d.rowData[i] = {rowCheckID: data.rowData[i].rowCheckID};
			data = d;
		}
		else {
			data.cmd = 'rowCheckUncheck';
			data.action= action;
		}
		$.ajax({
//			async: false,
			url: '<?php print $page->realtimeDataClassFile; ?>',
			dataType: 'json',
			method: 'post',
			data: data,
			success: function(data) {
				if (data.result == "success") {
					selectedLabelCount = data.count;
					$("#selectionTitle1").linkbutton({text: data.message});
					$("#selectionTitle2").linkbutton({text: data.message});
//					$("#selectionTitle1").html(data.message);
//					$("#selectionTitle2").html(data.message);
					if (action == 'load')
						showSelectedToggle(true);
					else if (action == 'reset') {
//						$('#selectionMenu1').panel('open');
//						$('#selectionMenu2').panel('close');
//						$('#searchResults0').datagrid('reload');
						showSelectedToggle(false);
					}
					else if (action == 'selectall')
						$('#searchResults0').datagrid('reload');
				}
				else if (data.result == "failure")
					alert(data.message)
			}
		});
	}
	
	function rotateBacks() {
		$.ajax({
			data: {
				cmd: 'rotateBacks'
			},
			success: function(data) {
				$('#resultPanel').panel('refresh','?cmd=outputGrid');
			}
		});
	}

	function emailPrintSelect(cmd, title) {
		$("#onlineTrxResult").window("clear");
		$("#onlineTrxResult").window({href: "<?php print $page->realtimeDataClassFile; ?>?cmd=downloadProgress"});
		$("#onlineTrxResult").window('setTitle',  title);
		$("#onlineTrxResult").window("open", false);
		$.ajax({
			url: '<?php print $page->realtimeDataClassFile; ?>',
			dataType: 'json',
			method: 'get',
			data: {
				cmd: cmd
			},
			success: function(data) {
				if (data.result == 'success') {
//					if ((cmd == 'emailSelect') || ('<?php print $page->downloadNotify; ?>' == '1')) {
						href = "<?php print $page->realtimeDataClassFile; ?>?cmd=onlineTrxMessage&onlineTrxID="+data.onlineTrxID;
						$("#onlineTrxResult").window({href: href});
						$("#onlineTrxResult").window('setTitle', "The request is complete");
						$("#onlineTrxResult").window("open", false);
//					}
//					else
//						$("#onlineTrxResult").window("close", false);
				}
				else {
					href = "<?php print $page->realtimeDataClassFile; ?>?cmd=onlineTrxMessage&onlineTrxID="+data.onlineTrxID;
					$("#onlineTrxResult").window({href: href});
					$("#onlineTrxResult").window('setTitle', 'The request has failed');
					$("#onlineTrxResult").window("open", false);
				}
			}
		});
	}
	
	var datagridPagerButtons;
	function showSelectedToggle(state) {
		if (state) {
			datagridPagerButtons = $('#searchResults0PagerButtons').html();

			if (selectedLabelCount <= 0) {
				alert("There are no selected labels to show");
				return;
			}
			$('#selectionMenu1').panel('close');
			$('#selectionMenu2').panel('open');
			$('#mainLayout').layout('panel', 'center').panel('setTitle', 'Label Browser - Showing selected labels only');

			updateSessionData('<?php print $classFile; ?>', {showSelected: true}, function(data){
				savedFilterRules = data.filterRules;
				// The type of each filterRule must be set, otherwise the 'enableFiler' method breaks.
				for (var x=0; x<savedFilterRules.length; x++) {
					if (savedFilterRules[x].field == 'ProductType')
						savedFilterRules[x].type = 'combobox';
					else
						savedFilterRules[x].type = savedFilterRules[x].type||'text';
				}
//				$('#searchResults0').datagrid({'filterRules':[]});
				$('#searchResults0').datagrid('removeFilterRule');
//				$('#searchResults0').datagrid('disableFilter');
				$('#searchResults0').datagrid('reload');
			});
		}
		else {
			$('#selectionMenu1').panel('open');
			$('#selectionMenu2').panel('close');
			$('#mainLayout').layout('panel', 'center').panel('setTitle', 'Label Browser');
			
			updateSessionData('<?php print $classFile; ?>', {showSelected: false}, function(){

//				$('#searchResults0').datagrid('enableFilter', savedFilterRules);
				var e, x, y, rule = [], n = 0, elem;
				e = $(".datagrid-filter");
				for (x=0; x<e.length; x++) {
					rule[x] = {field: e[x].name, op: 'contains', value: '', type: 'text'};
					$('#searchResults0').datagrid('removeFilterRule', rule[x].field);
					for (y=0; y<savedFilterRules.length; y++)
						if (e[x].name == savedFilterRules[y].field) {
							e[x].value = savedFilterRules[y].value;
							e[x].type = savedFilterRules[y].type;
//							if ((e[x].name == 'ProductType') && (elem = $('#searchResults0').datagrid('getFilterComponent', 'ProductType')))
//								elem.combobox('load', [
//									{code: 'S', value: 'Stock'},
//									{code: 'J', value: 'Custom'}
//								]);
							rule[x] = savedFilterRules[y];
							break;
						}
					$('#searchResults0').datagrid('addFilterRule', rule[x]);
//					if (e[x].name == 'ProductType') {
//						if (elem = $('#searchResults0').datagrid('getFilterComponent', 'ProductType'))
//							window.setTimeout(function(){
//								elem.combobox('load', [
//									{code: 'S', value: 'Stock'},
//									{code: 'J', value: 'Custom'}
//								]);
//							}, 2000);
//					}
				}
				$('#searchResults0').datagrid('enableFilter', rule);
				$('#searchResults0').datagrid('doFilter');

				// The pager buttons disappear when the filter is enabled again. To overcome this,
				// the pager buttons are saved into datagridPagerButtons when the filter is disabled,
				// and then they are restored to a newly created div when the filter is enabled again.
				$('#searchResults0').append('<div id="searchResults0PagerButtons"></div>')
				$('#searchResults0PagerButtons').html(datagridPagerButtons)
				$('#searchResults0').datagrid('getPager').pagination({
					buttons: $('#searchResults0PagerButtons')
				});

				window.setTimeout(function(){
					$('#resultPanel').panel('refresh', '?cmd=outputGrid');
				}, 500);
			});
		}
	}
</script>
<style>
.salesHeaderColumn {
    border-right: 1px solid #C0C0C0;
    border-bottom: 1px solid #C0C0C0;
    padding: 4px;
}
</style>
<body>

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

</body>

<?php
	include('footer.html');
//	print_r($_SERVER);
?>
</html>
