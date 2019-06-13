<?php
	define("findDescription",	0x00100);
	define("findProductLine",	0x00200);
	define("findHomeBin",		0x00400);
	define("findSafetyLevels",	0x00800);
	define("findRoyaltyPrice",	0x01000);
	define("findResponsibility",	0x02000);
	define("findTurnover",		0x04000);
	define("findStockTurnover",	0x08000);
	define("findWorkInProgress",	0x10000);

	class inventoryLookup extends dataLookup {

		var $customSettings = array(
			'includeZeroStock' => true,
			'includeObsoleteStock' => false,
			'includeDescriptive' => false,
			'showColumnsSelection'	=> findDescription,
			'ownerList'	=> null,
			'multiSort' => false,
			'favouritesListSelection' => null,
			'royaltyOwner' => '',
		);

		function __construct($classFile='', $settings=null) {
			if ($settings)
				foreach ($settings as $k => $v)
					$this->customSettings[$k] = $v;

			$this->column['partno'] = new dataLookupColumn(
				array(
					'title' =>	'Item code',
//					'id' =>		'partno',
					'field' =>	'i.ITEM_CODE',
					'width'		=> 300,
					'align'	=>	'left',
//					'format'	=> 'ITEMNO',
					'selectable'	=> false,		// This column is not available in to the columnSelectCombo method
					'group'		=> 'General details',
				)
			);

			$this->column['partname'] = new dataLookupColumn(
				array(
					'title'		=> 'Item description',
//					'id'		=> 'partname',
					'field'		=> 'i.LONG_DESC',
					'width'		=> 600,
					'align'		=> 'left',
					'format'	=> 'MIXEDCASE',
					'selectable'	=> false,		// This column is not available in to the columnSelectCombo method
					'group'		=> 'General details',
				)
			);

			$this->column['commonname'] = new dataLookupColumn(
				array(
					'title' 	=> 'Common name',
//					'id' 		=> 'cogsacct',
					'field' 	=> "i.C_COMMON_NAME",
//					'select' 	=> "IN_INVCAT.NAME",
					'width'		=> 400,
					'align'		=> 'left',
					'hidden'	=> true,
					'format'	=> 'MIXEDCASE',
//					'filtertype'	=> 'combobox',
//					'filterlist'	=> "select distinct CODE as value, NAME as text from IN_INVCAT order by NAME",
//					'leftjoin'	=> " left join IN_INVCAT on (i.INVEN_CATEG = IN_INVCAT.CODE)",
					'selectable'	=> false,		// This column is not available in to the columnSelectCombo method
					'group'		=> 'General details',
				)
			);

			$this->column['productline'] = new dataLookupColumn(
				array(
					'title' 	=> 'Product line',
//					'id' 		=> 'cogsacct',
					'field' 	=> "i.PRODUCT_LINE",
					'select' 	=> "IN_PRODLINE.SHORT_DESC",
					'width'		=> 400,
					'align'		=> 'left',
					'hidden'	=> true,
					'format'	=> 'MIXEDCASE',
					'filtertype'	=> 'combobox',
					'filterlist'	=> "select distinct PKEY as value, concat(SHORT_DESC, ' [', PKEY, ']') as text from IN_PRODLINE where (PKEY like ('%%%s%%')) or (SHORT_DESC like ('%%%s%%')) order by text",
					'leftjoin'	=> " left join IN_PRODLINE on (i.PRODUCT_LINE = IN_PRODLINE.PKEY)",
					'filteropts'	=> array('mode' => 'remote', 'panelWidth' => 300),
					'selectable'	=> false,		// This column is not available in to the columnSelectCombo method
					'group'		=> 'General details',
				)
			);

			$this->column['showInvCategory'] = new dataLookupColumn(
				array(
					'title' 	=> 'Inventory category',
//					'id' 		=> 'cogsacct',
					'field' 	=> "i.INVEN_CATEG",
					'select' 	=> "IN_INVCAT.NAME",
					'width'		=> 300,
					'align'		=> 'left',
					'hidden'	=> true,
					'format'	=> 'MIXEDCASE',
					'filtertype'	=> 'combobox',
					'filterlist'	=> "select distinct CODE as `group`, CODE as value, concat(NAME, ' [', CODE, ']') as text from IN_INVCAT where (CODE like ('%%%s%%')) or (NAME like ('%%%s%%')) having (text > '') order by text",
					'leftjoin'	=> " left join IN_INVCAT on (i.INVEN_CATEG = IN_INVCAT.CODE)",
					'filteropts'	=> array(
								'mode' => 'remote',
								'panelWidth' => 300,
//								'groupField' => 'group',
							),
					'group'		=> 'General details',
				)
			);

			$this->column['showStockCategory'] = new dataLookupColumn(
				array(
					'title' 	=> 'Stock category',
//					'id' 		=> 'cogsacct',
					'field' 	=> "i.C_STOCK_CATEGORY",
					'select' 	=> "IN_STOCKCAT.NAME",
					'width'		=> 300,
					'align'		=> 'left',
					'hidden'	=> true,
					'format'	=> 'MIXEDCASE',
					'filtertype'	=> 'combobox',
					'filterlist'	=> "select distinct CODE as value, concat(NAME, ' [', CODE, ']') as text from IN_STOCKCAT where (CODE like ('%%%s%%')) or (NAME like ('%%%s%%')) having (text > '') order by text",
					'leftjoin'	=> " left join IN_STOCKCAT on (i.C_STOCK_CATEGORY = IN_STOCKCAT.CODE)",
					'filteropts'	=> array('mode' => 'remote', 'panelWidth' => 300),
					'group'		=> 'General details',
				)
			);

			$this->column['introdate'] = new dataLookupColumn(
				array(
					'title'		=> 'Introduction date',
					'table'		=> 'i',
					'field'		=> 'DATE_INTRO',
					'width'		=> 200,
					'align'		=> 'center',
					'format'	=> 'DATE',
					'filtertype'	=> 'datebox',
					'filterpost'	=> true,
					'hidden'	=> true,
					'group'		=> 'Availability',
				)
			);

			$this->column['setupdate'] = new dataLookupColumn(
				array(
					'title'		=> 'Setup complete date',
					'table'		=> 'i',
					'field'		=> 'DATE_SETUP_COMPLETE',
					'width'		=> 200,
					'align'		=> 'center',
					'format'	=> 'DATE',
					'filtertype'	=> 'datebox',
					'filterpost'	=> true,
					'hidden'	=> true,
					'group'		=> 'Availability',
				)
			);

			$this->column['firtsprint'] = new dataLookupColumn(
				array(
					'title'		=> 'First printed',
					'table'		=> 'NW_INVENTORY',
					'field'		=> 'FIRST_PRINT',
					'width'		=> 200,
					'align'		=> 'center',
					'format'	=> 'DATE',
					'filtertype'	=> 'datebox',
					'filterpost'	=> true,
					'hidden'	=> true,
					'leftjoin'	=> " left join NW_INVENTORY on (i.ITEM_CODE = NW_INVENTORY.ITEM_CODE)",
					'group'		=> 'Availability',
				)
			);

			$this->column['lastprint'] = new dataLookupColumn(
				array(
					'title'		=> 'Last printed',
					'table'		=> 'NW_INVENTORY',
					'field'		=> 'LAST_PRINT',
					'width'		=> 200,
					'align'		=> 'center',
					'format'	=> 'DATE',
					'filtertype'	=> 'datebox',
					'filterpost'	=> true,
					'hidden'	=> true,
					'leftjoin'	=> " left join NW_INVENTORY on (i.ITEM_CODE = NW_INVENTORY.ITEM_CODE)",
					'group'		=> 'Availability',
				)
			);

			$this->column['obsolete'] = new dataLookupColumn(
				array(
					'title' 	=> 'Obsolete',
//					'id' 		=> 'cogsacct',
					'field' 	=> "i.OBSOLETE_ITEM",
//					'select' 	=> "IN_INVCAT.NAME",
					'width'		=> 100,
					'align'		=> 'center',
					'hidden'	=> true,
					'format'	=> 'OBSOLETE',
					'filtertype'	=> 'combobox',
					'filterlist'	=> "select distinct CODE as value, NAME as text from temp_obsolete order by NAME",
//					'leftjoin'	=> " left join IN_INVCAT on (i.INVEN_CATEG = IN_INVCAT.CODE)",
					'selectable'	=> false,		// This column is not available in to the columnSelectCombo method
					'group'		=> 'Availability',
				)
			);

			$this->column['method'] = new dataLookupColumn(
				array(
					'title' 	=> 'Method',
//					'id' 		=> 'cogsacct',
					'field' 	=> "IN_MASTER_LEVELS.C_SAFETY_METH",
					'select' 	=> "temp_safety_method.NAME",
					'width'		=> 100,
					'align'		=> 'center',
					'hidden'	=> true,
//					'format'	=> 'OBSOLETE',
					'filtertype'	=> 'combobox',
					'filterlist'	=> "select distinct CODE as value, NAME as text from temp_safety_method order by NAME",
					'leftjoin'	=> array(" left join IN_MASTER_LEVELS on (IN_MASTER_LEVELS.PKEY = i.PKEY)", " left join temp_safety_method on (temp_safety_method.CODE = (if (IN_MASTER_LEVELS.C_SAFETY_OVR > 0, 'O', IN_MASTER_LEVELS.C_SAFETY_METH)))"),
					'selectable'	=> false,		// This column is not available in to the columnSelectCombo method
					'group'		=> 'Safety levels',
				)
			);

			$this->column['q1'] = new dataLookupColumn(
				array(
					'title'		=> 'Q1',
//					'id'		=> 'qoh',
					'field'		=> "sum(if (IN_MASTER_LEVELS.C_SAFETY_OVR > 0, IN_MASTER_LEVELS.C_SAFETY_OVR, if((IN_MASTER_LEVELS.C_SAFETY_METH = 'A'), IN_MASTER_LEVELS.C_AUTO_Q1, IN_MASTER_LEVELS.C_SAFETY_Q1)))",
					'width'		=> 100,
					'align'		=> 'right',
					'format'	=> 'INTEGER',
//					'filtertype'	=> 'numberbox',
//					'filterprec'	=> 0,
//					'filterpost'	=> true,	// Filter after the 'group by' statement
					'leftjoin'	=> " left join IN_MASTER_LEVELS on (IN_MASTER_LEVELS.PKEY = i.PKEY)",
					'hidden'	=> true,
					'selectable'	=> false,		// This column is not available in to the columnSelectCombo method
					'group'		=> 'Safety levels',
				)
			);

			$this->column['q2'] = new dataLookupColumn(
				array(
					'title'		=> 'Q2',
//					'id'		=> 'qoh',
					'field'		=> "sum(if (IN_MASTER_LEVELS.C_SAFETY_OVR > 0, IN_MASTER_LEVELS.C_SAFETY_OVR, if((IN_MASTER_LEVELS.C_SAFETY_METH = 'A'), IN_MASTER_LEVELS.C_AUTO_Q2, IN_MASTER_LEVELS.C_SAFETY_Q2)))",
					'width'		=> 100,
					'align'		=> 'right',
					'format'	=> 'INTEGER',
//					'filtertype'	=> 'numberbox',
//					'filterprec'	=> 0,
//					'filterpost'	=> true,	// Filter after the 'group by' statement
					'leftjoin'	=> " left join IN_MASTER_LEVELS on (IN_MASTER_LEVELS.PKEY = i.PKEY)",
					'hidden'	=> true,
					'selectable'	=> false,		// This column is not available in to the columnSelectCombo method
					'group'		=> 'Safety levels',
				)
			);

			$this->column['q3'] = new dataLookupColumn(
				array(
					'title'		=> 'Q3',
//					'id'		=> 'qoh',
					'field'		=> "sum(if (IN_MASTER_LEVELS.C_SAFETY_OVR > 0, IN_MASTER_LEVELS.C_SAFETY_OVR, if((IN_MASTER_LEVELS.C_SAFETY_METH = 'A'), IN_MASTER_LEVELS.C_AUTO_Q3, IN_MASTER_LEVELS.C_SAFETY_Q3)))",
					'width'		=> 100,
					'align'		=> 'right',
					'format'	=> 'INTEGER',
//					'filtertype'	=> 'numberbox',
//					'filterprec'	=> 0,
//					'filterpost'	=> true,	// Filter after the 'group by' statement
					'leftjoin'	=> " left join IN_MASTER_LEVELS on (IN_MASTER_LEVELS.PKEY = i.PKEY)",
					'hidden'	=> true,
					'selectable'	=> false,		// This column is not available in to the columnSelectCombo method
					'group'		=> 'Safety levels',
				)
			);

			$this->column['q4'] = new dataLookupColumn(
				array(
					'title'		=> 'Q4',
					'field'		=> "sum(if (IN_MASTER_LEVELS.C_SAFETY_OVR > 0, IN_MASTER_LEVELS.C_SAFETY_OVR, if((IN_MASTER_LEVELS.C_SAFETY_METH = 'A'), IN_MASTER_LEVELS.C_AUTO_Q4, IN_MASTER_LEVELS.C_SAFETY_Q4)))",
					'width'		=> 100,
					'align'		=> 'right',
					'format'	=> 'INTEGER',
					'leftjoin'	=> " left join IN_MASTER_LEVELS on (IN_MASTER_LEVELS.PKEY = i.PKEY)",
					'hidden'	=> true,
					'selectable'	=> false,		// This column is not available in to the columnSelectCombo method
					'group'		=> 'Safety levels',
				)
			);

			if (@in_array('nol_access', $_SESSION['user']['royalty_admin'])) {
				$this->column['cost'] = new dataLookupColumn(
					array(
						'title'		=> 'Cost',
						'field'		=> "temp_price.cost/100",
						'width'		=> 100,
						'align'		=> 'right',
						'format'	=> 'CURRENCY',
						'leftjoin'	=> " left join temp_price on (i.PKEY = temp_price.PKEY)",
						'hidden'	=> true,
						'selectable'	=> false,		// This column is not available in to the columnSelectCombo method
						'group'		=> 'Pricing',
					)
				);

				$this->column['royalty'] = new dataLookupColumn(
					array(
						'title'		=> 'Royalty',
						'field'		=> "temp_price.royalty/100",
						'width'		=> 100,
						'align'		=> 'right',
						'format'	=> 'CURRENCY',
						'leftjoin'	=> " left join temp_price on (i.PKEY = temp_price.PKEY)",
						'hidden'	=> true,
						'selectable'	=> false,		// This column is not available in to the columnSelectCombo method
						'group'		=> 'Pricing',
					)
				);
			}

			$this->column['sell'] = new dataLookupColumn(
				array(
					'title'		=> 'Sell',
					'field'		=> "temp_price.sell/100",
					'width'		=> 100,
					'align'		=> 'right',
					'format'	=> 'CURRENCY',
					'leftjoin'	=> " left join temp_price on (i.PKEY = temp_price.PKEY)",
					'hidden'	=> true,
					'selectable'	=> false,		// This column is not available in to the columnSelectCombo method
					'group'		=> 'Pricing',
				)
			);

			$this->column['uom'] = new dataLookupColumn(
				array(
					'title'		=> 'UOM',
					'field'		=> "temp_price.UOM",
					'width'		=> 100,
					'align'		=> 'right',
					'format'	=> 'default',
					'leftjoin'	=> " left join temp_price on (i.PKEY = temp_price.PKEY)",
					'hidden'	=> true,
					'selectable'	=> false,		// This column is not available in to the columnSelectCombo method
					'group'		=> 'Pricing',
				)
			);

			$this->column['free'] = new dataLookupColumn(
				array(
					'title'		=> 'Free quantity',
//					'id'		=> 'qoh',
					'field'		=> "max(IN_BAL.FREE_QTY)",
					'width'		=> 200,
					'align'		=> 'right',
					'format'	=> 'INTEGER',
					'filtertype'	=> 'numberbox',
					'filterprec'	=> 0,
					'filterpost'	=> true,	// Filter after the 'group by' statement
					'leftjoin'	=> " left join IN_BAL on (IN_BAL.ITEM = i.ITEM_CODE)",
					'group'		=> 'Inventory levels',
				)
			);

			$this->column['avail'] = new dataLookupColumn(
				array(
					'title'		=> 'Available qty',
//					'id'		=> 'qoh',
					'field'		=> "max(IN_BAL.AVAIL_QTY)",
					'width'		=> 200,
					'align'		=> 'right',
					'format'	=> 'INTEGER',
					'filtertype'	=> 'numberbox',
					'filterprec'	=> 0,
					'filterpost'	=> true,	// Filter after the 'group by' statement
					'leftjoin'	=> " left join IN_BAL on (IN_BAL.ITEM = i.ITEM_CODE)",
					'group'		=> 'Inventory levels',
				)
			);

			$this->column['qoh'] = new dataLookupColumn(
				array(
					'title'		=> 'Qty on hand',
//					'id'		=> 'qoh',
					'field'		=> "max(IN_BAL.BAL_QTY)",
					'width'		=> 200,
					'align'		=> 'right',
					'format'	=> 'INTEGER',
					'filtertype'	=> 'numberbox',
					'filterprec'	=> 0,
					'filterpost'	=> true,	// Filter after the 'group by' statement
					'leftjoin'	=> " left join IN_BAL on (IN_BAL.ITEM = i.ITEM_CODE)",
					'group'		=> 'Inventory levels',
				)
			);

			$this->column['onorder'] = new dataLookupColumn(
				array(
					'title'		=> 'Qty reserved',
//					'id'		=> 'onorder',
					'field'		=> "max(IN_BAL.RESERVED_QTY)",
					'width'		=> 200,
//					'hidden'	=> true,
					'align'		=> 'right',
					'format'	=> 'INTEGER',
					'filtertype'	=> 'numberbox',
					'filterprec'	=> 0,
					'filterpost'	=> true,	// Filter after the 'group by' statement
					'leftjoin'	=> " left join IN_BAL on (IN_BAL.ITEM = i.ITEM_CODE)",
					'group'		=> 'Inventory levels',
				)
			);

			$this->column['qbo'] = new dataLookupColumn(
				array(
					'title'		=> 'Backorder qty',
//					'id'		=> 'onorder',
					'field'		=> "max(IN_BAL.BORDER_QTY)",
					'width'		=> 200,
//					'hidden'	=> true,
					'align'		=> 'right',
					'format'	=> 'INTEGER',
					'filtertype'	=> 'numberbox',
					'filterprec'	=> 0,
					'filterpost'	=> true,	// Filter after the 'group by' statement
					'leftjoin'	=> " left join IN_BAL on (IN_BAL.ITEM = i.ITEM_CODE)",
					'group'		=> 'Inventory levels',
				)
			);

			$this->column['qoo'] = new dataLookupColumn(
				array(
					'title'		=> 'Qty on order',
//					'id'		=> 'avail',
					'field'		=> "max(IN_BAL.ORDER_QTY)",
					'width'		=> 200,
					'align'		=> 'right',
					'format'	=> 'INTEGER',
					'filtertype'	=> 'numberbox',
					'filterprec'	=> 0,
					'filterpost'	=> true,	// Filter after the 'group by' statement
					'leftjoin'	=> " left join IN_BAL on (IN_BAL.ITEM = i.ITEM_CODE)",
					'group'		=> 'Inventory levels',
				)
			);

			$this->column['fwd'] = new dataLookupColumn(
				array(
					'title'		=> 'Fwd order qty',
//					'id'		=> 'avail',
					'field'		=> "max(IN_BAL.FWD_QTY)",
					'width'		=> 200,
					'align'		=> 'right',
					'format'	=> 'INTEGER',
					'filtertype'	=> 'numberbox',
					'filterprec'	=> 0,
					'filterpost'	=> true,	// Filter after the 'group by' statement
					'leftjoin'	=> " left join IN_BAL on (IN_BAL.ITEM = i.ITEM_CODE)",
					'group'		=> 'Inventory levels',
				)
			);

			$this->column['homebin'] = new dataLookupColumn(
				array(
					'title' => 'Home bin',
//					'id' => 'listprice',
					'field' => "substring(i.HOME_BINS, 6)",
					'width'		=> 100,
					'align'	=>	'center',
					'hidden'	=> true,
					'format'	=> 'default',
//					'filtertype'	=> 'numberbox',
//					'leftjoin'	=> " left join temp_wip on (temp_wip.LABEL = i.ITEM_CODE)",
					'selectable'	=> false,		// This column is not available in to the columnSelectCombo method
					'group'		=> 'Inventory levels',
				)
			);

			$this->column['CURRENT_STATUS_DATE'] = new dataLookupColumn(
				array(
					'title'		=> 'Last WIP status date',
//					'id'		=> 'partname',
					'field'		=> 'max(temp_wip.CURRENT_STATUS_DATE)',
					'width'		=> 200,
					'align'		=> 'center',
					'format'	=> 'DATE',
					'filtertype'	=> 'datebox',
					'filterpost'	=> true,
					'hidden'	=> true,
					'group'		=> 'Work in progress',
				)
			);

			$this->column['showQtyWIPStatus'] = new dataLookupColumn(
				array(
					'title' => 'Current WIP status',
//					'id' => 'listprice',
					'field' => "group_concat(temp_wip.WIP_STATUS separator '<br>\n')",
					'width'		=> 400,
//					'align'	=>	'right',
					'hidden'	=> true,
//					'format'	=> 'MIXEDCASE',
//					'filtertype'	=> 'numberbox',
					'leftjoin'	=> " left join temp_wip on (temp_wip.LABEL = i.ITEM_CODE)",
					'selectable'	=> false,		// This column is not available in to the columnSelectCombo method
					'group'		=> 'Work in progress',
				)
			);

			$this->column['showQtyWIPSchedule'] = new dataLookupColumn(
				array(
					'title' => 'Scheduled WIP stage',
//					'id' => 'listprice',
					'field' => "group_concat(temp_wip.WIP_SCHEDULE separator '<br>\n')",
					'width'		=> 400,
//					'align'	=>	'right',
					'hidden'	=> true,
					'format'	=> 'MIXEDCASE',
//					'filtertype'	=> 'numberbox',
					'leftjoin'	=> " left join temp_wip on (temp_wip.LABEL = i.ITEM_CODE)",
					'selectable'	=> false,		// This column is not available in to the columnSelectCombo method
					'group'		=> 'Work in progress',
				)
			);

			$this->column['wip'] = new dataLookupColumn(
				array(
					'title' => 'WIP quantity',
					'field' => "sum(temp_wip.WIP)",
//					'field' => "sum(if (temp_wip.WIP, temp_wip.WIP, ZN_WIP_BAL.QTY))",
					'width'		=> 250,
					'align'	=>	'right',
					'format'	=> 'INTEGER',
					'filtertype'	=> 'numberbox',
					'leftjoin'	=> array(
								" left join temp_wip on (temp_wip.LABEL = i.ITEM_CODE)",
//								" left join ZN_WIP_BAL on (ZN_WIP_BAL.CODE = i.ITEM_CODE)",
							),
					'filterpost'	=> true,	// Filter after the 'group by' statement
					'group'		=> 'Work in progress',
				)
			);

			$this->column['showQtyProjected'] = new dataLookupColumn(
				array(
					'title' => 'Projected qty',
//					'id' => 'listprice',
//					'field' => "sum((IN_BAL.BAL_QTY - IN_BAL.BORDER_QTY - IN_BAL.RESERVED_QTY) + if (temp_wip.WIP, temp_wip.WIP, 0))",
					'field' => "ifnull(IN_BAL.BAL_QTY - IN_BAL.BORDER_QTY - IN_BAL.RESERVED_QTY, 0) + if (sum(temp_wip.WIP), temp_wip.WIP, 0)",
					'width'		=> 250,
					'align'	=>	'right',
					'hidden'	=> true,
					'format'	=> 'INTEGER',
					'filtertype'	=> 'numberbox',
					'leftjoin'	=> array(" left join temp_wip on (temp_wip.LABEL = i.ITEM_CODE)", " left join IN_BAL on (IN_BAL.ITEM = i.ITEM_CODE)"),
					'filterpost'	=> true,	// Filter after the 'group by' statement
					'group'		=> 'Work in progress',
				)
			);

			$this->column['lastreceipt'] = new dataLookupColumn(
				array(
					'title'		=> 'Last receipt',
//					'id'		=> 'partname',
					'field'		=> 'NW_INVENTORY.LAST_RECEIPT',
					'width'		=> 200,
					'align'		=> 'center',
					'format'	=> 'DATE',
					'filtertype'	=> 'datebox',
					'filterpost'	=> true,
					'leftjoin'	=> " left join NW_INVENTORY on (i.ITEM_CODE = NW_INVENTORY.ITEM_CODE)",
					'hidden'	=> true,
					'group'		=> 'Recent activity',
				)
			);

			$this->column['lastissue'] = new dataLookupColumn(
				array(
					'title'		=> 'Last issue',
//					'id'		=> 'partname',
					'field'		=> 'NW_INVENTORY.LASTISSUE',
					'width'		=> 200,
					'align'		=> 'center',
					'format'	=> 'DATE',
					'filtertype'	=> 'datebox',
					'filterpost'	=> true,
					'leftjoin'	=> " left join NW_INVENTORY on (i.ITEM_CODE = NW_INVENTORY.ITEM_CODE)",
					'hidden'	=> true,
					'group'		=> 'Recent activity',
				)
			);

			$this->column['movement'] = new dataLookupColumn(
				array(
					'title'		=> 'Issues/month',
//					'id'		=> 'partname',
					'field'		=> '30.4 * (NW_INVENTORY.ISSUEQTY / datediff(curdate(), NW_INVENTORY.LAST_RECEIPT))',
					'width'		=> 200,
					'align'		=> 'right',
					'format'	=> 'INTEGER',
					'leftjoin'	=> " left join NW_INVENTORY on (i.ITEM_CODE = NW_INVENTORY.ITEM_CODE)",
					'group'		=> 'Recent activity',
				)
			);

			$this->column['issues12mtd'] = new dataLookupColumn(
				array(
					'title'		=> 'Issues 12 MTD',
//					'id'		=> 'partname',
					'field'		=> 'NW_INVENTORY.ISSUES12MTD',
					'width'		=> 200,
					'align'		=> 'right',
					'format'	=> 'INTEGER',
					'leftjoin'	=> " left join NW_INVENTORY on (i.ITEM_CODE = NW_INVENTORY.ITEM_CODE)",
					'group'		=> 'Recent activity',
				)
			);

			$this->column['sales12mtd'] = new dataLookupColumn(
				array(
					'title'		=> '12 month sales',
//					'id'		=> 'partname',
					'field'		=> 'NW_BROWSER.SALES12MTD',
					'width'		=> 200,
					'align'		=> 'right',
					'format'	=> 'INTEGER',
					'hidden'	=> true,
					'leftjoin'	=> " left join NW_BROWSER on (i.ITEM_CODE = NW_BROWSER.PKEY) and (NW_BROWSER.TYPE = 'S')",
					'group'		=> 'Recent activity',
				)
			);

			$this->column['publishedgroup'] = new dataLookupColumn(
				array(
					'title'		=> 'Group name',
//					'id'		=> 'partname',
					'field'		=> "if(ROYALTY_RANGE.range_name > '', ROYALTY_RANGE.range_name, '-- Unclassified --')",
					'width'		=> 200,
					'leftjoin'	=> array(
								"left join ROYALTY_RANGE_ITEM on (ROYALTY_RANGE_ITEM.item_code = i.ITEM_CODE)",
								"left join ROYALTY_RANGE on (ROYALTY_RANGE.id = ROYALTY_RANGE_ITEM.range_id)",
							),
					'group'		=> 'Tagpic Online publishing',
					'hidden'	=> true,
				)
			);

			$this->column['publishedowner'] = new dataLookupColumn(
				array(
					'title'		=> 'Owner name',
//					'id'		=> 'partname',
					'field'		=> 'ROYALTY_RANGE.owner_name',
					'width'		=> 200,
					'leftjoin'	=> array(
								"left join ROYALTY_RANGE_ITEM on (ROYALTY_RANGE_ITEM.item_code = i.ITEM_CODE)",
								"left join ROYALTY_RANGE on (ROYALTY_RANGE.id = ROYALTY_RANGE_ITEM.range_id)",
							),
					'group'		=> 'Tagpic Online publishing',
					'hidden'	=> true,
				)
			);

			$this->column['published'] = new dataLookupColumn(
				array(
					'title'		=> 'Published?',
//					'id'		=> 'partname',
					'field'		=> 'ROYALTY_RANGE_ITEM.published',
					'width'		=> 200,
					'align'		=> 'center',
					'leftjoin'	=> array(
								"left join ROYALTY_RANGE_ITEM on (ROYALTY_RANGE_ITEM.item_code = i.ITEM_CODE)",
							),
					'group'		=> 'Tagpic Online publishing',
					'hidden'	=> true,
				)
			);

			$showColumnsSelection = $_SESSION[$classfile]['showColumnsSelection'];
			parent::__construct($classFile, $this->customSettings);
			
			if ($showColumnsSelection != $this->showColumnsSelection) {
//				$this->columnSelection = array();
				$this->columnSelection[] = 'partno';
				$this->columnSelection[] = 'partname';
				switch ($this->showColumnsSelection) {
				case findDescription:
					$this->columnSelection[] = 'qoh';
					$this->columnSelection[] = 'avail';
					$this->columnSelection[] = 'wip';
					break;
				case findStockTurnover:
					$this->columnSelection[] = 'qoh';
					$this->columnSelection[] = 'lastreceipt';
					$this->columnSelection[] = 'lastissue';
					$this->columnSelection[] = 'movement';
					$this->columnSelection[] = 'issues12mtd';
					break;
				case findWorkInProgress:
					$this->columnSelection[] = 'CURRENT_STATUS_DATE';
					$this->columnSelection[] = 'showQtyWIPStatus';
					$this->columnSelection[] = 'showQtyWIPSchedule';
					break;
				case findProductLine:
					$this->columnSelection[] = 'productline';
					$this->columnSelection[] = 'commonname';
					break;
				case findHomeBin:
					$this->columnSelection[] = 'obsolete';
					$this->columnSelection[] = 'homebin';
					break;
				case findSafetyLevels:
					$this->columnSelection[] = 'q1';
					$this->columnSelection[] = 'q2';
					$this->columnSelection[] = 'q3';
					$this->columnSelection[] = 'q4';
					$this->columnSelection[] = 'method';
					break;
				case findRoyaltyPrice:
					$this->columnSelection[] = 'cost';
					$this->columnSelection[] = 'royalty';
					$this->columnSelection[] = 'sell';
					$this->columnSelection[] = 'uom';
					break;
				}
			}
			

			$this->title = 'Inventory master file';
			$this->tableName = 'IN_MASTER';
			$this->tableAlias = 'i';
			$this->tableSubset = "(1)";
			$this->resultDisplayHandler = 'outputGrid';
			$this->excludeReportOnCategories = 0;
			$this->tableSubset = '1';
			$this->groupByClause = " group by i.ITEM_CODE";
			
			$this->additionalFields = array(
				'PKEY' => 'i.ITEM_CODE',
			);

			$this->pagerButtons[] = "<a href='#' onclick='javascript:dl();' class='easyui-linkbutton' title='Download these results to Excel' data-options=\"iconCls:'".iconSave."',plain:true\"></a>\n".$pagerButtons;

			if (count($GLOBALS['user']['royalty_owner']) && count($GLOBALS['user']['custno'])) {
				if (!$this->ownerList) {
					$this->ownerList = '(';
					$comma = '';
					foreach ($GLOBALS['user']['royalty_owner'] as $c) {
						$this->ownerList .= $comma."'".$c."'";
						$comma = ',';
					}
					$this->ownerList .= ')';
				}
				$this->querydb("drop table if exists temp0");
				$this->querydb("create temporary table temp0
					select IN_MASTER.PKEY, IN_MASTER.ITEM_CODE, IN_MASTER.C_PRICE_GRP
					 from  IN_MASTER
					 left join ZN_PRICE_ROYALTY on (ZN_PRICE_ROYALTY.PKEY = IN_MASTER.C_PRICE_GRP)
					 where (ZN_PRICE_ROYALTY.ROYALTY_PAID_TO in ".$this->ownerList.")
				");
				$this->tableSubset .= ' and (r.C_ROYALTY_PAID_TO in '.$this->ownerList.')';
				$this->column['partno']->leftjoin = array(" left join ZN_AVAIL as a on (i.C_RESTRICTED_TO = a.PKEY) and (a.Z_ASSOC_ROW = 1)", " left join IN_MASTER_ROYALTY as r on (r.ITEM_CODE = i.ITEM_CODE) and (r.C_OWNER = 'R')");
			}
			else if ($this->royaltyOwner > '') {
				$this->tableSubset .= ' and (r.C_ROYALTY_PAID_TO = '.$this->royaltyOwner.')';
				$this->column['partno']->leftjoin = array(" left join ZN_AVAIL as a on (i.C_RESTRICTED_TO = a.PKEY) and (a.Z_ASSOC_ROW = 1)", " left join IN_MASTER_ROYALTY as r on (r.ITEM_CODE = i.ITEM_CODE) and (r.C_OWNER = 'R')");
			}
			if (!$this->includeZeroStock) {
				$this->leftjoin[] = " left join IN_BAL on (IN_BAL.ITEM = i.ITEM_CODE)";
				$this->tableSubset .= ' and (IN_BAL.BAL_QTY or IN_BAL.BORDER_QTY)';
			}

			if (!$this->includeObsoleteStock)
				$this->tableSubset .= " and (i.OBSOLETE_ITEM <> 'Y')";

			if (!$this->includeDescriptive)
				$this->tableSubset .= " and (i.C_STOCK_CATEGORY <> '002') and (i.INVEN_CATEG <> '002')";

			if ($this->favouritesListSelection > '') {
				$this->tableSubset .= ' and (i.ITEM_CODE in (';
				$comma = '';
				foreach ($_SESSION['user']['favouritesLists']['label'][$this->favouritesListSelection] as $k => $v) {
					$this->tableSubset .= $comma."'".$k."'";
					$comma = ', ';
				}
				$this->tableSubset .= '))';
			}

			if (is_array($this->settings['columnSelection']) && in_array('location', $this->settings['columnSelection']))
				$this->groupByClause = " group by c.CODE, c.LOC";
/*
			if (in_array($_REQUEST['cmd'], array('outputData', 'downloadQuery', 'loadFilterCombo', 'runReport'))) {
				$this->querydb("drop table if exists temp_safety_method");
				$this->querydb("create temporary table temp_safety_method type=heap
						select 'A' as CODE, 'Automatic' as NAME
					union all
						select 'M' as CODE, 'Manual' as NAME
					union all
						select 'O' as CODE, 'Override' as NAME
				");
				$this->querydb("alter table temp_safety_method add index (CODE)");

				$this->querydb("drop table if exists temp_obsolete");
				$this->querydb("create temporary table temp_obsolete type=heap
						select 'Y' as CODE, 'Yes' as NAME
					union all
						select 'N' as CODE, 'No' as NAME
				");
				$this->querydb("alter table temp_obsolete add index (CODE)");
				if (($this->showColumnsSelection == findRoyaltyPrice) || ($this->showColumnsSelection == findResponsibility) || ($this->showColumnsSelection == findTurnover)) {
					if ($this->ownerList) {
						$sql = "select temp0.PKEY, ZN_PRICE.UOM
							, sum(if(ZN_ROYALTY_OWNER.HIDDEN = 'Y' and (ZN_PRICE_ROYALTY.ROYALTY_PAID_TO not in ".$this->ownerList."), (ZN_PRICE.LIST_COST) + (ZN_PRICE_ROYALTY.AMOUNT_PER_UOM/if (ZN_PRICE.UOM = 'EA',1,ZN_PRICE.UOM))*10, 0)) as cost
							, sum(if(ZN_ROYALTY_OWNER.HIDDEN = 'N' or (ZN_PRICE_ROYALTY.ROYALTY_PAID_TO in ".$this->ownerList."), (ZN_PRICE_ROYALTY.AMOUNT_PER_UOM/if (ZN_PRICE.UOM = 'EA',1,ZN_PRICE.UOM))*10, 0)) royalty 
							, (ZN_PRICE.LIST_PRICE/if (ZN_PRICE.UOM = 'EA',1,ZN_PRICE.UOM))*1000 as sell
							, ZN_PRICE.MIN_PRINT_QTY
							from temp0
							left join ZN_PRICE_ROYALTY on (ZN_PRICE_ROYALTY.PKEY = temp0.C_PRICE_GRP)
							left join ZN_ROYALTY_OWNER on (ZN_ROYALTY_OWNER.PKEY = ZN_PRICE_ROYALTY.ROYALTY_PAID_TO)
							left join ZN_PRICE on (ZN_PRICE.PRICE_GRP = temp0.C_PRICE_GRP)
							group by temp0.PKEY, ZN_PRICE_ROYALTY.PKEY
						";
					}
					else {
						$sql = "select IN_MASTER.PKEY, ZN_PRICE.UOM
							, ZN_PRICE.LIST_COST as cost
							, ZN_PRICE.C_AMOUNT_PER_UOM/if (ZN_PRICE.UOM = 'EA',1,ZN_PRICE.UOM)*10 as royalty
							, ZN_PRICE.LIST_PRICE as sell
							, ZN_PRICE.MIN_PRINT_QTY
							 from IN_MASTER 
							 left join ZN_PRICE on (ZN_PRICE.PRICE_GRP = IN_MASTER.C_PRICE_GRP)
						";
					}
					$this->querydb("drop table if exists temp_price");
					$this->querydb("create temporary table temp_price type=heap $sql");
					$this->querydb("alter table temp_price add index (PKEY)");
					$this->querydb("update temp_price set cost = (sell - royalty) where (cost = 0)");
				}
				if ($_REQUEST['cmd'] != 'loadFilerCombo') {
					$comma = $set = '';
					$sql = "select distinct SETNO from ZN_PRODUCTION_SUMMARY where PRINTED = 0 and CUT = 0 and SECTION";
					if ($r = $this->querydb($sql)) {
						while ($rec = mysql_fetch_assoc($r)) {
							$set .= $comma."'".$rec['SETNO']."'";
							$comma = ', ';
						}
					}

					$this->querydb("drop table if exists temp_level");
					$sql = "select distinct LABEL
						, concat(SETNUM, '.', SECTION) as SETSECT
						, 0 as QTY
						, if ((SETNUM > '') and QTY_UNCUT , concat('Set ', SETNUM, '-', SECTION, ': ', CURRENT_STATUS_DESC) , '' ) as WIP_STATUS
						, if ((SETNUM > '') and QTY_UNCUT , if (SCHEDULED_PRINT_DATE , concat('Print on ', date_format(SCHEDULED_PRINT_DATE, '%D %b')) , if (SCHEDULED_CUT_DATE , concat('Die cut on ', date_format(SCHEDULED_CUT_DATE, '%D %b')) , '' ) ) , '' ) as WIP_SCHEDULE
						 from ZN_STOCK_LEVEL as s where (SETNUM <> '31777') and SETNUM > '' order by LABEL
					";
					$sql = "select distinct LABEL
						, concat(SETNUM, '.', SECTION) as SETSECT
						, 0 as QTY
						, if ((SETNUM > '') and (CURRENT_STATUS_DESC > '') and (CURRENT_STATUS_DESC <> 'STRIPPING COMPLETE') , concat('Set ', SETNUM, '-', SECTION, ': ', CURRENT_STATUS_DESC) , '' ) as WIP_STATUS
						, if ((SETNUM > '') and QTY_UNCUT , if (SCHEDULED_PRINT_DATE , concat('Print on ', date_format(SCHEDULED_PRINT_DATE, '%D %b')) , if (SCHEDULED_CUT_DATE , concat('Die cut on ', date_format(SCHEDULED_CUT_DATE, '%D %b')) , '' ) ) , '' ) as WIP_SCHEDULE
						 from ZN_STOCK_LEVEL as s where (SETNUM <> '31777') and SETNUM > '' order by LABEL
					";
					$this->querydb("create temporary table temp_level ".$sql);
					$this->querydb("insert into temp_level select CODE , SET_SECT , sum(QTY) as QTY, null as WIP_STATUS, null as WIP_SCHEDULE from ZN_WIP_BAL group by CODE , SET_SECT");
					if ($set > '')
						$this->querydb("insert into temp_level select ITEMCODE , concat(SETNO, '.', SECTION) as SET_SECT , max(RUNS) * max(NO_UP) as QTY, null as WIP_STATUS, null as WIP_SCHEDULE
								 from ZN_PRODUCTION_DETAIL where SETNO in ($set) group by ITEMCODE, concat(SETNO, '.', SECTION)");
					$this->querydb("drop table if exists temp_wip");

					$sql = "select w.LABEL
							, max(if (w.QTY || (w.WIP_STATUS > ''), o.C_PRINT_QTY, 0)) as WIP 
							, w.WIP_STATUS
							, concat(o.C_PRINT_QTY, ' ', w.WIP_SCHEDULE) as WIP_SCHEDULE
							from temp_level as w
							left join IN_MASTER_ONSET as o on (o.ITEM_CODE = w.LABEL) and (w.WIP_STATUS like concat('%',o.C_PRINT_PLATE,'%'))
							where (w.SETSECT not like '31777.%') and (w.SETSECT not like '8278.%') 
							group by w.LABEL, o.C_PRINT_PLATE
							having WIP
					";
					$sql = "select w.LABEL
							, max(if (w.QTY || (w.WIP_STATUS > ''), o.C_PRINT_QTY, 0)) as WIP 
							, w.WIP_STATUS
							, if (w.WIP_SCHEDULE > '', concat(o.C_PRINT_QTY, ' ', w.WIP_SCHEDULE), '') as WIP_SCHEDULE
							from temp_level as w
							left join IN_MASTER_ONSET as o on (o.ITEM_CODE = w.LABEL) and (w.WIP_STATUS like concat('%',o.C_PRINT_PLATE,'%'))
							where (w.SETSECT not like '31777.%') and (w.SETSECT not like '8278.%') 
							group by w.LABEL, o.C_PRINT_PLATE
							having WIP
					";
					$this->querydb("create temporary table temp_wip ".$sql);

					$this->querydb("alter table temp_wip add index (LABEL)");

				}
			}
*/
		}

		function createOptimisedData() {
			$this->querydb("drop table if exists temp_safety_method");
			$this->querydb("create temporary table temp_safety_method type=heap
					select 'A' as CODE, 'Automatic' as NAME
				union all
					select 'M' as CODE, 'Manual' as NAME
				union all
					select 'O' as CODE, 'Override' as NAME
			");
			$this->querydb("alter table temp_safety_method add index (CODE)");

			$this->querydb("drop table if exists temp_obsolete");
			$this->querydb("create temporary table temp_obsolete type=heap
					select 'Y' as CODE, 'Yes' as NAME
				union all
					select 'N' as CODE, 'No' as NAME
			");
			if ($_REQUEST['cmd'] == 'loadFilterCombo')
				return;

			$this->querydb("alter table temp_obsolete add index (CODE)");
//			if (($this->showColumnsSelection == findRoyaltyPrice) || ($this->showColumnsSelection == findResponsibility) || ($this->showColumnsSelection == findTurnover)) {
			if (in_array('cost', $this->columnSelection) || in_array('royalty', $this->columnSelection) ||in_array('sell', $this->columnSelection) ||in_array('uom', $this->columnSelection)) {
				if ($this->ownerList) {
					$sql = "select temp0.PKEY, ZN_PRICE.UOM
						, sum(if(ZN_ROYALTY_OWNER.HIDDEN = 'Y' and (ZN_PRICE_ROYALTY.ROYALTY_PAID_TO not in ".$this->ownerList."), (ZN_PRICE.LIST_COST) + (ZN_PRICE_ROYALTY.AMOUNT_PER_UOM/if (ZN_PRICE.UOM = 'EA',1,ZN_PRICE.UOM))*10, 0)) as cost
						, sum(if(ZN_ROYALTY_OWNER.HIDDEN = 'N' or (ZN_PRICE_ROYALTY.ROYALTY_PAID_TO in ".$this->ownerList."), (ZN_PRICE_ROYALTY.AMOUNT_PER_UOM/if (ZN_PRICE.UOM = 'EA',1,ZN_PRICE.UOM))*10, 0)) royalty 
						, (ZN_PRICE.LIST_PRICE/if (ZN_PRICE.UOM = 'EA',1,ZN_PRICE.UOM))*1000 as sell
						, ZN_PRICE.MIN_PRINT_QTY
						from temp0
						left join ZN_PRICE_ROYALTY on (ZN_PRICE_ROYALTY.PKEY = temp0.C_PRICE_GRP)
						left join ZN_ROYALTY_OWNER on (ZN_ROYALTY_OWNER.PKEY = ZN_PRICE_ROYALTY.ROYALTY_PAID_TO)
						left join ZN_PRICE on (ZN_PRICE.PRICE_GRP = temp0.C_PRICE_GRP)
						group by temp0.PKEY, ZN_PRICE_ROYALTY.PKEY
					";
				}
				else {
					$sql = "select IN_MASTER.PKEY, ZN_PRICE.UOM
						, ZN_PRICE.LIST_COST as cost
						, ZN_PRICE.C_AMOUNT_PER_UOM/if (ZN_PRICE.UOM = 'EA',1,ZN_PRICE.UOM)*10 as royalty
						, ZN_PRICE.LIST_PRICE as sell
						, ZN_PRICE.MIN_PRINT_QTY
						 from IN_MASTER 
						 left join ZN_PRICE on (ZN_PRICE.PRICE_GRP = IN_MASTER.C_PRICE_GRP)
					";
				}
				$this->querydb("drop table if exists temp_price");
				$this->querydb("create temporary table temp_price type=heap $sql");
				$this->querydb("alter table temp_price add index (PKEY)");
				$this->querydb("update temp_price set cost = (sell - royalty) where (cost = 0)");
			}
			$comma = $set = '';
			$sql = "select distinct SETNO from ZN_PRODUCTION_SUMMARY where PRINTED = 0 and CUT = 0 and SECTION";
			if ($r = $this->querydb($sql)) {
				while ($rec = mysql_fetch_assoc($r)) {
					$set .= $comma."'".$rec['SETNO']."'";
					$comma = ', ';
				}
			}

			$this->querydb("drop table if exists temp_level");
			$sql = "select distinct LABEL
				, concat(SETNUM, '.', SECTION) as SETSECT
				, 0 as QTY
				, if ((SETNUM > '') and (CURRENT_STATUS_DESC > '') and (CURRENT_STATUS_DESC <> 'STRIPPING COMPLETE') , concat('Set ', SETNUM, '-', SECTION, ': ', CURRENT_STATUS_DESC) , '' ) as WIP_STATUS
				, if ((SETNUM > '') and QTY_UNCUT , if (SCHEDULED_PRINT_DATE , concat('Print on ', date_format(SCHEDULED_PRINT_DATE, '%D %b')) , if (SCHEDULED_CUT_DATE , concat('Die cut on ', date_format(SCHEDULED_CUT_DATE, '%D %b')) , '' ) ) , '' ) as WIP_SCHEDULE
				, s.CURRENT_STATUS_DATE
				 from ZN_STOCK_LEVEL as s
				 where (SETNUM <> '31777') and (SETNUM > '')
			";
			$this->querydb("create temporary table temp_level ".$sql);
			$this->querydb("insert into temp_level select CODE , SET_SECT , sum(QTY) as QTY, null as WIP_STATUS, null as WIP_SCHEDULE, null as CURRENT_STATUS_DATE from ZN_WIP_BAL group by CODE , SET_SECT");
			if ($set > '')
				$this->querydb("insert into temp_level
					select ITEMCODE , concat(SETNO, '.', SECTION) as SET_SECT , max(RUNS) * max(NO_UP) as QTY, null as WIP_STATUS, null as WIP_SCHEDULE, null as CURRENT_STATUS_DATE
					from ZN_PRODUCTION_DETAIL
					where SETNO in ($set)
					group by ITEMCODE, concat(SETNO, '.', SECTION)
				");
			$this->querydb("drop table if exists temp_wip");

			$sql = "select w.LABEL
				, max(if (w.QTY || (w.WIP_STATUS > ''), o.C_PRINT_QTY, 0)) as WIP 
				, w.WIP_STATUS
				, if (w.WIP_SCHEDULE > '', concat(o.C_PRINT_QTY, ' ', w.WIP_SCHEDULE), '') as WIP_SCHEDULE
				, max(w.CURRENT_STATUS_DATE) as CURRENT_STATUS_DATE
				from temp_level as w
				left join IN_MASTER_ONSET as o on (o.ITEM_CODE = w.LABEL) and (w.WIP_STATUS like concat('%',o.C_PRINT_PLATE,'%'))
				where (w.SETSECT not like '31777.%') and (w.SETSECT not like '8278.%') 
				group by w.LABEL, o.C_PRINT_PLATE
				having WIP
			";
			$this->querydb("create temporary table temp_wip ".$sql);
			$this->querydb("alter table temp_wip add index (LABEL)");

			$this->querydb("drop table if exists temp1");
			$this->querydb("create temporary table temp1
				select CODE as LABEL, sum(QTY) as WIP, concat('Set ', group_concat(SET_SECT)) as WIP_STATUS, '' as WIP_SCHEDULE, null as CURRENT_STATUS_DATE
				from ZN_WIP_BAL 
				where CODE not in (select distinct LABEL from temp_wip)
				group by CODE
			");
			$this->querydb("insert into temp_wip select * from temp1");
		}

		function Query() {
			$this->createOptimisedData();
			return(parent::Query());
		}

		function loadFilterCombo($returnResult=false) {
			$this->createOptimisedData();
			$data = parent::loadFilterCombo(true);
			if ($returnResult)
				return($data);
			die(json_encode($data));
		}

		function excelFormat(&$workbook, $rec, $field, $row, $col) {
			if (($field == 'showQtyWIPStatus') || ($field == 'showQtyWIPSchedule'))
				$rec[$field] = str_replace('<br>', '', $rec[$field]);
			$ret = parent::excelFormat($workbook, $rec, $field, $row, $col);
		}

		function fieldFormat($field, $rec, $htmlmode=true) {
			$column= $this->column[$field];
			switch($column->format) {
			case 'ITEMNO':
				$parm = $rec[$field];
				$ret = "<a href=\"#\" onclick='javascript:showDetailCommonEntry(event,\"label:$parm\");'>$parm</a>";
				break;
			case 'OBSOLETE':
				if ($htmlmode) {
					if ($rec[$field] == 'Y')
						$ret = '&nbsp;<img src="/realtime/images/no.png">';
					if ($rec[$field] == 'N')
						$ret = '&nbsp;<img src="/realtime/images/ok.png">';
				}
				else if ($rec[$field] != NULL)
					$ret = (($rec[$field] == 'Y')?'Yes':'No');
				break;
			case 'ACCURACY':
				if ($htmlmode) {
					$val = number_format($rec[$field]);
					if ($rec[$field] < 0.01) {
						if ($rec[$field] <> null)
							$ret = $val.'%&nbsp;<img src="/realtime/images/light_stop.png">';
						else
							$ret = '';
					}
					else if ($rec[$field] < 0.99)
						$ret = $val.'%&nbsp;<img src="/realtime/images/light_warn.png">';
					else
						$ret = '+'.$val.'%&nbsp;<img src="/realtime/images/light_go.png">';
				}
				else if ($rec[$field] != NULL)
					$ret = number_format($rec[$field],1).'%';
				break;
			case "DATE":
				if ($rec[$field] && strtotime($rec[$field]))
					$ret = strftime('%e %h %Y', strtotime($rec[$field]));
				break;
			default:
				$ret = parent::fieldFormat($field, $rec, $htmlmode);
				break;
			}
			return($ret);
		}

		function outputData() {
			$data = parent::generateOutputData();
//			$data['debug'] = print_r($_SESSION[$this->realtimeDataClassFile], true);
			print (json_encode($data));
		}

		function loadOwnerFilter() {
			$q = $_REQUEST['q'];
			$maxRows = 50;
			$data = array();

			if ($this->royaltyOwner > '') {
				$sql = "select PKEY as id, NAME as text from ZN_ROYALTY_OWNER where (PKEY = '".$this->royaltyOwner."')";
				if (($r = $this->querydb($sql)) && ($rec = mysql_fetch_assoc($r))) {
					$rec['text'] = ucwords(strtolower($rec['text']));
					$data[] = $rec;
				}
			}

			$sql = sprintf("select distinct PKEY as id, NAME as text
				 from ZN_ROYALTY_OWNER as j
				 where (OWNER_TYPE = 'R') and (HIDDEN = 'N')
				 and ((PKEY like '%%%s%%') or (NAME like '%%%s%%'))
			", $q, $q);
			$sql .= " order by 2 limit $maxRows";
			$r = $this->querydb($sql);
			while ($rec = mysql_fetch_assoc($r)) {
				if ($rec['id'] != $this->royaltyOwner) {
					foreach($rec as $k => $v)
						if ($k == 'text')
							$v = ucwords(strtolower($v));
						$rec[$k] = htmlspecialchars($v);
					$data[] = $rec;
				}
			}
			$data[0]['debug'] = $this->debugData;
			die(json_encode($data));
		}

		function menuBasic() {
			$updateFunction = "\$('#mainLayout').layout('panel', 'center').panel('refresh','".$this->realtimeDataClassFile."?cmd=".$this->resultDisplayHandler."')";
			$updateFunction .= "; var p = \$('#settings').accordion('getPanel', 0); p.panel('refresh')";

			if (count($_SESSION['user']['royalty_owner']))
				$data = array(
					array('id' => findDescription, 'text' => "Stock levels"),
					array('id' => findProductLine, 'text' => "Product line, common name"),
					array('id' => findSafetyLevels, 'text' => "Safety levels"),
					array('id' => findRoyaltyPrice, 'text' => "List price and royalty"),
					array('id' => findResponsibility, 'text' => "Responsibility"),
					array('id' => findTurnover, 'text' => "Turnover and value"),
					array('id' => findWorkInProgress, 'text' => "WIP status and schedule"),
				);
			else
				$data = array(
					array('id' => findDescription, 'text' => "Stock levels"),
					array('id' => findStockTurnover, 'text' => "Turnover and age"),
					array('id' => findProductLine, 'text' => "Product line, common name"),
					array('id' => findHomeBin, 'text' => "Home bin, obsolete"),
					array('id' => findSafetyLevels, 'text' => "Safety levels"),
					array('id' => findRoyaltyPrice, 'text' => "List price and royalty"),
					array('id' => findWorkInProgress, 'text' => "WIP status and schedule"),
				);
			$this->menuCombo('Show:', 'showColumnsSelection', $data, $updateFunction);
			$updateFunction = "\$('#mainLayout').layout('panel', 'center').panel('refresh','".$this->realtimeDataClassFile."?cmd=".$this->resultDisplayHandler."')";
			$this->columnSelectCombo($updateFunction);

			if (count($GLOBALS['user']['royalty_owner']) && count($GLOBALS['user']['custno']))
				;
			else {
				$name = 'royaltyOwner';
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
						param.cmd = 'loadOwnerFilter';
					}",
					'icons'		=> "[{
								iconCls:'fa fa-times rd-clear rd-clear-narrow'
								, handler: function(e) {
									\$(e.data.target).combogrid('clear');
									updateSessionData('".$this->realtimeDataClassFile."', {".$name.": ''}, function(){ $updateFunction; });
								}
							}]",
					'onUnselect'	=> "function(index, rec) {
						updateSessionData('', {".$name.": rec.id}, function(){ $updateFunction; });
					}",
					'onSelect'	=> "function(index, rec) {
						updateSessionData('', {".$name.": rec.id}, function(){ $updateFunction; });
					}",
					'onOpen'	=> "function() {
							$('#".$name."').combogrid('setValue', '".$this->$name."');
					}",
				);
				$cell = $this->htmlComboBox(null, $this->$name, $name, $options, 'easyui-combogrid');
				print $this->htmlTableCell(1, "Owner:");
				print $this->htmlTableCell(1, $cell, "align='right'");
				print $this->htmlTableRowClose();
			}
			$options = array(
				'multiSort' => array('Multi-column sorting', 'Allow multiple column sorting, otherwise sort on one column only'),
			);
//			$this->menuIncudeOptions($options, $updateFunction, null);
		}

		function menuOptions() {
			$updateFunction = "\$('#mainLayout').layout('panel', 'center').panel('refresh','".$this->realtimeDataClassFile."?cmd=".$this->resultDisplayHandler."')";

			$options = array(
				'includeZeroStock' => array('Zero stock', 'Include items with zero qunatity on hand'),
				'includeObsoleteStock' => array('Obsolete stock', 'Include obsolete stock items'),
				'includeDescriptive' => array('Descriptive and plain labels', 'Include items in the descriptive and plain label stock category'),
			);
			$this->menuIncudeOptions($options, $updateFunction);
			$data = array();
			if (count($_SESSION['user']['favouritesLists']['label'])) {
				$data[] = array('id' => '', 'text' => '-- Select --');
				foreach ($_SESSION['user']['favouritesLists']['label'] as $k => $v)
					$data[] = array('id' => $k,'text' => $k);
			}
			else
				$data[] = array('id' => '', 'text' => '-- None available --');
			$this->menuCombo('Favourites list:', 'favouritesListSelection', $data, $updateFunction);
		}
		
		function menuAdvanced() {
			$this->advancedSettingsMenu();
		}
		
		function outputGrid() {
			$data = parent::outputGrid(array('multiSort' =>  $this->multiSort));
		}

		function outputDetailWindow() {
			include_once('include/norwoodLabelDetail.class.php');
			$code = strip_tags($this->lookupDetailData['PKEY']);
			$win = new norwoodLabelDetail('/norwood/online/include/norwoodLabelDetail.class.php', 'lookupDetailWindow', $code, ($_SESSION['user']['secure_pdf'] == 'on'?1:0));
		}
	}
?>