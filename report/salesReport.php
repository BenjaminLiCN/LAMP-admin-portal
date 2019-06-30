<?php
    $classFile =  $_SERVER['PHP_SELF'];

    //die($classFile);
    include "commonService.php";
    include "header.html";

    class salesReport extends commonService {
        public function __construct() {
            $this->drawMainMenu();

            $this->addToProperty(array(
                'columnMask' => 'showReport',
                'codeTitle' => 'Agent',
                'nameTitle' => 'Agent name',
                'nameStyle' => 'MIXEDCASE',
                'comboLookup' => "select AGENT from REPORT"
            ));

            $this->addToProperty(array(
                'columnMask' => 'showReport',
                'codeTitle' => 'Date',
                'nameTitle' => 'Report date',
                'nameStyle' => 'MIXEDCASE',
                'comboLookup' => "select DATE from REPORT)"
            ));

            $this->addToProperty(array(
                'columnMask' => 'showReport',
                'codeTitle' => 'Content',
                'nameTitle' => 'Report content',
                'nameStyle' => 'MIXEDCASE',
                'comboLookup' => "select CONTENT from REPORT"
            ));

            $this->addToProperty(array(
                'columnMask' => 'showReport',
                'codeTitle' => 'Recent',
                'nameTitle' => 'Is created recently?',
                'nameStyle' => 'MIXEDCASE',
                'comboLookup' => "select IS_RECENT from REPORT"
            ));

        }

    }
?>


<?php
    $classFile = $_SERVER['PHP_SELF'];
    $page = new salesReport($classFile);
    if ($cmd = $_REQUEST['cmd']) {
        if (method_exists($page, $_REQUEST['cmd'])) {
            $page->$cmd();
        }else{
            die("no such method");
        }
        die();
    }
