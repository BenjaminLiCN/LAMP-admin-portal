<?php
    @session_id($_GET['PHPSESSIONID']);

    @session_start();
    $classFile =  $_SERVER['PHP_SELF'];
    $sid = @session_id();


    include "commonService.php";


    class salesReport extends commonService {
        public function __construct($classFile=null) {

            $this->addToProperty(array(
                'columnMask' => 'showReport',
                'codeTitle' => 'AGENT',
                'nameTitle' => 'Agent name',
                'nameStyle' => 'MIXEDCASE',
                'comboLookup' => "select AGENT from REPORT"
            ));

            $this->addToProperty(array(
                'columnMask' => 'showReport',
                'codeTitle' => 'DATE',
                'nameTitle' => 'Report date',
                'nameStyle' => 'MIXEDCASE',
                'comboLookup' => "select DATE from REPORT)"
            ));

            $this->addToProperty(array(
                'columnMask' => 'showReport',
                'codeTitle' => 'CONTENT',
                'nameTitle' => 'Report content',
                'nameStyle' => 'MIXEDCASE',
                'comboLookup' => "select CONTENT from REPORT"
            ));

            $this->addToProperty(array(
                'columnMask' => 'showReport',
                'codeTitle' => 'IS_RECENT',
                'nameTitle' => 'Is created recently?',
                'nameStyle' => 'MIXEDCASE',
                'comboLookup' => "select IS_RECENT from REPORT"
            ));

            $db = $this->openDB();
            $this->loadData($db);




        }

        function addToProperty($propArray) {
            array_push($this->gridOpts,$propArray);
        }

        function loadData($db) {
            $sql = "select * from REPORT";
            $result = $this->queryDB($sql,$db);
            while ($row = mysqli_fetch_assoc($result))
            {
                $row['IS_RECENT'] = $row['IS_RECENT'] == 1 ? "YES" : "NO";
                array_push($this->gridData,$row);
            }
            $result->close();
        }

    }


    $page = new salesReport($classFile);

    if ($cmd = $_REQUEST['cmd']) {
        if (method_exists($page, $_REQUEST['cmd'])) {
            $page->$cmd();
        }else{
            die("no such method");
        }
        die();
    }
    include "header.html";
    $page->drawMainFrame();


    if(isset($_SESSION['uid'])){
    //        echo 'You are Logged as '.$_SESSION['username'].'<br/>';
    //        echo '<a href="login.php"> Log Out('.$_SESSION['username'].')</a>';
    } else {
        $home_url = 'login.php';
        header('Location: '.$home_url);

    }
