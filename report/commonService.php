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
            <div class=\"easyui-panel\" title=\"Nested Panel\" style=\"width:100%;height:100%;padding:5px;\">
        <div class=\"easyui-layout\" data-options=\"fit:true\">
            <div data-options=\"region:'west',split:true\" style=\"width:200px;padding:10px\">
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
            <div data-options=\"region:'east'\" style=\"width:200px;padding:10px\">
                Right Content
            </div>
            <div data-options=\"region:'center'\" style=\"padding:10px\">
                <table id=\"dg\" title=\"Custom DataGrid Pager\" style=\"width:100%;height:100%\"
                       data-options=\"rownumbers:true,singleSelect:true,pagination:true,url:'datagrid.json',method:'get'\">
                    <thead>
                    <tr>
                        <th data-options=\"field:'itemid',width:80\">Item ID</th>
                        <th data-options=\"field:'productid',width:100\">Product</th>
                        <th data-options=\"field:'listprice',width:80,align:'right'\">List Price</th>
                        <th data-options=\"field:'unitcost',width:80,align:'right'\">Unit Cost</th>
                        <th data-options=\"field:'attr1',width:240\">Attribute</th>
                        <th data-options=\"field:'status',width:60,align:'center'\">Status</th>
                    </tr>
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
                    })
                </script>
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
        ";
    }

    function addToProperty($propArray) {

    }

    function openDB() {
        $mysql_hostname = self::$DEV_HOST;
        $mysql_user = self::$USERNAME;
        $mysql_password = self::$PASSWORD;
        $mysql_database = self::$DBNAME;
        $bd = mysqli_connect($mysql_hostname, $mysql_user, $mysql_password,$mysql_database) or die("Could not connect database");
    }
}