<?php

class commonService {

    private static $LIVE_HOST = "129.211.79.168";
    private static $DEV_HOST = "127.0.0.1";
    private static $USERNAME = "root";
    private static $LIVE_PASSWORD = "Aaa951202_";
    private static $DEV_PASSWORD = "951202";
    private static $DBNAME = "NW_REPORT";
    var $gridOpts = array();
    var $gridData = array();

    function __construct($classFile=null) {

    }

    //recursive call
    function drawMainFrame() {
        //print out
        $centerHtml = "";
        $centerHtml = $this->outputGrid($this->gridOpts);

        $frame= "<div class=\"easyui-panel\" title=\"Nested Panel\" style=\"width:100%;height:100%;padding:5px;\">
        <div class=\"easyui-layout\" data-options=\"fit:true\">
            <div data-options=\"region:'west',split:true\" style=\"width:15%;padding:10px\">
                <div class=\"easyui-accordion\" data-options=\"fit:true,border:false\">
                    <div title=\"Title1\" style=\"padding:10px;\">
                        content1
                    </div>
                    <div title=\"Title2\" data-options=\"selected:true\" style=\"padding:10px;\">
                        content2
                    </div>
                    <div title=\"Title3\" style=\"padding:10px\">
                        content3
                    </div>
                </div>
            </div>
            <div data-options=\"region:'east'\" style=\"width:30%;padding:10px\">
                <div class=\"easyui-panel\" title=\"Send report\" style=\"width:100%;padding:30px 60px;\">
                    <div style=\"margin-bottom:20px\">
                        <input class=\"easyui-textbox\" id='email' label=\"Receiver email:\" labelPosition=\"top\" data-options=\"prompt:'Enter email address...',validType:'email'\" style=\"width:100%;\">
                    </div>
                    <div style=\"margin-bottom:20px\">
                        <input class=\"easyui-textbox\" id='name' label=\"Sender:\" labelPosition=\"top\" data-options=\"prompt:'Sender name...'\" style=\"width:100%;\">
                    </div>
                    <div style=\"margin-bottom:20px\">
                        <input class=\"easyui-datebox\" id='date'  label=\"Schedule date:\" labelPosition=\"top\" data-options=\"prompt:'Choose a date...'\" style=\"width:100%;\">
                    </div>
                    <div style=\"margin-bottom:20px\">
                        <input class=\"easyui-textbox\" id='company' label=\"Organisation:\" labelPosition=\"top\" style=\"width:100%;\">
                    </div>
                    
                    <div>
                        <a href=\"#\" class=\"easyui-linkbutton\" iconCls=\"icon-ok\" style=\"width:100%;height:32px\">Send</a>
                        <a href=\"#\" class=\"easyui-linkbutton\" iconCls=\"icon-edit\" onclick='sampleFill();' style=\"width:100%;height:32px;margin-top: 5px\">Sample</a>
                    </div>
                </div>
            </div>
            <div data-options=\"region:'center'\" style=\"padding:10px;width:55%\">
                ".$centerHtml."
            </div>
            <div data-options=\"region:'north'\" style=\"padding:10px\">
                <a href=\"#\" class=\"easyui-linkbutton\" data-options=\"plain:true\">Home</a>
                <a href=\"#\" class=\"easyui-menubutton\" data-options=\"menu:'#mm1',iconCls:'icon-edit'\">Edit</a>
                <a href=\"#\" class=\"easyui-menubutton\" data-options=\"menu:'#mm2',iconCls:'icon-help'\">Help</a>
                <a href=\"#\" class=\"easyui-menubutton\" data-options=\"menu:'#mm3'\">About</a>
            </div>
            <div id=\"mm1\" style=\"width:150px;\">
                <div data-options=\"iconCls:'icon-undo'\">Undo</div>
                <div data-options=\"iconCls:'icon-redo'\">Redo</div>
                <div class=\"menu-sep\"></div>
                <div>Cut</div>
                <div>Copy</div>
                <div>Paste</div>
                <div class=\"menu-sep\"></div>
                <div>
                    <span>Toolbar</span>
                    <div>
                        <div>Address</div>
                        <div>Link</div>
                        <div>Navigation Toolbar</div>
                        <div>Bookmark Toolbar</div>
                        <div class=\"menu-sep\"></div>
                        <div>New Toolbar...</div>
                    </div>
                </div>
                <div data-options=\"iconCls:'icon-remove'\">Delete</div>
                <div>Select All</div>
            </div>
            <div id=\"mm2\" style=\"width:100px;\">
                <div>Help</div>
                <div>Update</div>
                <div>About</div>
            </div>
            <div id=\"mm3\" class=\"menu-content\" style=\"background:#f0f0f0;padding:10px;text-align:left\">
                <img src=\"http://www.jeasyui.com/images/logo1.png\" style=\"width:150px;height:50px\">
                <p style=\"font-size:14px;color:#444;\">Try jQuery EasyUI to build your modern, interactive, javascript applications.</p>
            </div>
        </div>
    </div>
    <script>
        function sampleFill() {
            $('#email').textbox('setText','simon.cui@norwood.com.au');
            $('#name').textbox('setText','Benjamin Li');
            $('#date').textbox('setText','06/07/2019');
            $('#company').textbox('setText','Norwood Industries');
        }
    </script>
    ";
        print $frame;
    }

    function outputGrid($opts) {
        $columns = "";
        $classFile =  $_SERVER['PHP_SELF'];
        foreach($opts as $k => $v) {
            $columns .= "<th data-options=\"field:'".$v['codeTitle']."'\">".$v['nameTitle']."</th>";
        }
        //".$classFile."?cmd=getGridData
        $gridFrame = "<table id=\"dg\" title=\"Custom DataGrid Pager\" style=\"width:100%;height:100%\"
                       data-options=\"rownumbers:true,singleSelect:true,pagination:true,url:'".$classFile."?cmd=getGridData'
                       \">
                    <thead>
                    ".$columns."
                    </thead>
                </table>
                <script type=\"text/javascript\">
                    $(function(){
                        var pager = $('#dg').datagrid().datagrid('getPager');    // get the pager of datagrid
                        pager.pagination({
                            buttons:[{
                                iconCls:'icon-search',
                                handler:function(){
                                    alert('search');
                                }
                            },{
                                iconCls:'icon-add',
                                handler:function(){
                                    alert('add');
                                }
                            },{
                                iconCls:'icon-edit',
                                handler:function(){
                                    alert('edit');
                                }
                            }]
                        });
                    });
                    
                </script>";
        return $gridFrame;
    }

    function openDB() {
        $mysql_hostname = self::$DEV_HOST;
        $mysql_user = self::$USERNAME;
        $mysql_password = self::$DEV_PASSWORD;
        $mysql_database = self::$DBNAME;
        $db = mysqli_connect($mysql_hostname, $mysql_user, $mysql_password,$mysql_database) or die("Could not connect database");
        return $db;
    }

    function queryDB($sql,$db) {
        $result = mysqli_query($db,$sql);
        return $result;
    }

    function getGridData() {
        echo json_encode($this->gridData);
    }
}