<?php


class commonService {

    private static $LIVE_HOST = "129.211.79.168";
    private static $DEV_HOST = "localhost";
    private static $USERNAME = "root";
    private static $PASSWORD = "Aaa951202_";
    private static $DBNAME = "NW_REPORT";
    //recursive call
    function drawMainMenu(){
        //print out

        print "
            <div id=\"mm\" class=\"easyui-menu\" style=\"width:120px;\">
                <div>New</div>
                <div>
                    <span>Open</span>
                    <div style=\"width:150px;\">
                        <div><b>Word</b></div>
                        <div>Excel</div>
                        <div>PowerPoint</div>
                    </div>
                </div>
                <div data-options=\"iconCls:'icon-save'\">Save</div>
                <div class=\"menu-sep\"></div>
                <div>Exit</div>
            </div>
        ";
    }

    function openDB(){
        $mysql_hostname = self::$HOST;
        $mysql_user = self::$USERNAME;
        $mysql_password = self::$PASSWORD;
        $mysql_database = self::$DBNAME;
        $bd = mysqli_connect($mysql_hostname, $mysql_user, $mysql_password,$mysql_database) or die("Could not connect database");
    }
}