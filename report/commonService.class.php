<?php
class commonService {

    public $LIVE_HOST = "129.211.79.168";
    public $DEV_HOST = "127.0.0.1";
    public $DB_USER = "root";
    public $DEV_PASSWORD = "951202";
    public $DB_NAME = "NW_REPORT";
    var $gridOpts = array();
    var $gridData = array();

    function __construct($classFile=null) {

    }

    //recursive call
    function drawMainFrame() {
        //print out
        $centerHtml = "";
        $centerHtml = $this->outputGrid($this->gridOpts);

        $frame= "<div class=\"easyui-panel\" title=\"Sales Report\" style=\"width:100%;height:100%;padding:5px;\">
        <div class=\"easyui-layout\" data-options=\"fit:true\">
            <div data-options=\"region:'west',split:true\" style=\"width:15%;padding:10px\">
                <div class=\"easyui-accordion\" data-options=\"fit:true,border:false\">
                    <div title=\"Basic setting\" style=\"padding:10px;\">
                        content1
                    </div>
                    <div title=\"Filter\" data-options=\"selected:true\" style=\"padding:10px;\">
                        <table>
                            <tr><td>Recent records:</td></tr>
                            <tr>
                                
                                <td><select id='recentSelect' class='easyui-combobox'  data-options='panelHeight:\"auto\",width:150'>
                                        <option value='all'>All records</option>
                                        <option value='recent'>Old records</option>
                                        <option value='old'>Recent records</option>
                                    </select>
                                </td>
                            </tr>
                            <tr><td>Name filtering:</td></tr>
                            <tr>
                                
                                <td><input id='nameFilter' class='easyui-searchbox' data-options=\"prompt:'Enter name'\" style='width:140px;margin-top: 20px'/>
                                </td>
                            </tr>
                        </table>    
                       
                    </div>
                    <div title=\"Advanced setting\" style=\"padding:10px\">
                        content3
                    </div>
                </div>
            </div>
            <div data-options=\"region:'east'\" style=\"width:30%;padding:10px\">
                <div class=\"easyui-panel\" title=\"Send report\" style=\"width:100%;padding:30px 60px;\">
                    <form id='reportForm' class='easyui-form'>
                        <div style=\"margin-bottom:20px\">
                            <input class=\"easyui-textbox\" id='email' label=\"Receiver email:\" labelPosition=\"top\" data-options=\"prompt:'Enter email address...',validType:'email',required:true,validateOnCreate:false\" style=\"width:100%;\">
                        </div>
                        <div style=\"margin-bottom:20px\">
                            <input class=\"easyui-textbox\" id='name' label=\"Sender:\" labelPosition=\"top\" data-options=\"prompt:'Sender name...',required:true,validateOnCreate:false\" style=\"width:100%;\">
                        </div>
                        <div style=\"margin-bottom:20px\">
                            <input class=\"easyui-datebox\" id='date'  label=\"Schedule date:\" labelPosition=\"top\" data-options=\"prompt:'Choose a date...',required:true,validateOnCreate:false\" style=\"width:100%;\">
                        </div>
                        <div style=\"margin-bottom:20px\">
                            <input class=\"easyui-textbox\" id='company' label=\"Organisation:\" labelPosition=\"top\" data-options=\"prompt:'Organisation name...',required:true,validateOnCreate:false\" style=\"width:100%;\">
                        </div>
                        <div id='window' class=\"easyui-window\" title=\"Report summary\" data-options=\"iconCls:'icon-save',modal:true,closed:true\" style=\"width:600px;height:350px;padding:5px;\">
                            <div class=\"easyui-layout\" data-options=\"fit:true\">
                                <div data-options=\"region:'east',split:true\" style=\"width:320px;padding: 10px;\">
                                    Debriefing:
                                    <table style='border:1px solid #F00;'>
                                        <tr>
                                            <td>Agent</td>
                                            <td id='agentCell'></td>
                                        </tr>
                                        <tr>
                                            <td>Date</td>
                                            <td id='dateCell'></td>
                                        </tr>
                                        <tr>
                                            <td>Content</td>
                                            <td id='contentCell'></td>
                                        </tr>
                                        <tr>
                                            <td>Recent</td>
                                            <td id='recentCell'></td>
                                        </tr>
                                    </table>
                                </div>
                                <div data-options=\"region:'center'\" style=\"padding:10px;\">
                                    Receiver detail:
                                    <table style='border:1px solid #F00'>
                                        <tr>
                                            <td></td>
                                            <td id='emailCell'></td>
                                        </tr>
                                        <tr>
                                            <td>To</td>
                                            <td id='senderCell'></td>
                                        </tr>
                                        <tr>
                                            <td></td>
                                            <td id='organCell'></td>
                                        </tr>
                                    </table>
                                </div>
                                <div data-options=\"region:'south',border:false\" style=\"text-align:right;padding:5px 0 0;\">
                                    <a class=\"easyui-linkbutton\" data-options=\"iconCls:'icon-ok'\" href=\"javascript:void(0)\" onclick=\"\" style=\"width:80px\">Ok</a>
                                    <a class=\"easyui-linkbutton\" data-options=\"iconCls:'icon-cancel'\" href=\"javascript:void(0)\" onclick=\"javascript:$('#window').window('close');\" style=\"width:80px\">Cancel</a>
                                </div>
                            </div>
                        </div>
                        <div>
                            <a href=\"#\" class=\"easyui-linkbutton\" iconCls=\"icon-ok\" style=\"width:100%;height:32px\" onclick=\"javascript:generateReport();\">Send</a>
                            <a href=\"#\" class=\"easyui-linkbutton\" iconCls=\"icon-edit\" onclick='sampleFill();' style=\"width:100%;height:32px;margin-top: 5px\">Sample</a>
                        </div>
                    </form>
                </div>
            </div>
            <div data-options=\"region:'center'\" style=\"padding:10px;width:55%\">
                ".$centerHtml."
            </div>
            <div data-options=\"region:'north'\" style=\"padding:10px\">
                <a href=\"login.php\" class=\"easyui-linkbutton\" data-options=\"plain:true\">Log out (".$_SESSION['username'].")</a>
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
        
        
        $('#nameFilter').searchbox({
            onChange:function(newValue,oldValue){
                filterDataGrid(newValue,'name');
            } 
        });
        $('#recentSelect').combobox({
            onChange:function(newValue,oldValue){
                filterDataGrid(newValue,'recent');
            }
        });
        
        function sampleFill() {
            $('#email').textbox('setText','simon.cui@norwood.com.au');
            $('#name').textbox('setText','Benjamin Li');
            $('#date').textbox('setText','06/07/2019');
            $('#company').textbox('setText','Norwood Industries');
        }
        function filterDataGrid(pattern,type) {
            $('#dg').datagrid('load',{
                pattern: pattern,
                type: type
            });
        }
         
        
        function generateReport() {
           $('#reportForm').form('submit',{
				onSubmit:function(){
					$(this).form('enableValidation').form('validate');
					var isValid = $(this).form('validate');
					console.log(isValid);
					return isValid;
				},
				success:function(){	
				    var row = $('#dg').datagrid('getSelected');
                    console.log(row);
                    
                    if(row != null) {
                        var agent = row.AGENT;
                        var date = row.DATE;
                        var content = row.CONTENT;
                        var is_recent = row.IS_RECENT;
                        $('#agentCell').html(agent);
                        $('#dateCell').html(date);
                        $('#contentCell').html(content);
                        $('#recentCell').html(is_recent);
                        
                        var sender = $('#name').textbox('getText');
                        var email = $('#email').textbox('getText');
                        var company = $('#company').textbox('getText');
                        $('#emailCell').html(sender);
                        $('#senderCell').html(email);
                        $('#organCell').html(company);
                        
                        $('#window').window('open')
                    } else {
                         $.messager.alert('Info','No record selected!');
                    }
				}
			});
            
            
            
            
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
        $gridFrame = "<table id=\"dg\" title=\"Data dynamically loaded from database\" style=\"width:100%;height:100%\"
                       data-options=\"rownumbers:true,singleSelect:true,pagination:true,url:'".$classFile."?cmd=getGridData'
                       \">
                    <thead>
                    ".$columns."
                    </thead>
                </table>
                <script type=\"text/javascript\">
                    $(function(){
                        var dg = $('#dg').datagrid();
                       
                        dg.datagrid('loadData',[]);
                        dg.datagrid({pagePosition:'top'});
                        dg.datagrid('getPager').pagination({
                            layout:['list','sep','first','prev','sep',$('#p-style').val(),'sep','next','last','sep','refresh','info']
                        });
                        
                        var pager = dg.datagrid('getPager');    // get the pager of datagrid
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
        $mysql_hostname = $this->DEV_HOST;
        $mysql_user = $this->DB_USER;
        $mysql_password = $this->DEV_PASSWORD;
        $mysql_database = $this->DB_NAME;
        $db = mysqli_connect($mysql_hostname, $mysql_user, $mysql_password,$mysql_database) or die("Could not connect database");
        return $db;
    }

    //return false if the query is not successful
    function queryDB($db,$sql) {
        $result = mysqli_query($db,$sql);
        return $result;
    }

    function getGridData() {
        $pattern = $_REQUEST['pattern'];
        $type = $_REQUEST['type'];
        if($pattern==null) {
            echo json_encode($this->gridData);
            die();
        }
        //Php will allocate new memory for the new array by default
        // $a = &$b; is the way to create reference of the old array
        $rawData = $this->gridData;
        if(strcmp($type,'name')==0) {
            foreach ($rawData as $k => $v) {
                $pos = stristr($v['AGENT'],$pattern);
                if ($pos !== false) {
                    //echo "contains the pattern"."\n";
                } else {
                    //echo "unset array, key = ".$k."\n";
                    unset($rawData[$k]);
                }
            }
            echo json_encode(array_values($rawData));
        } else {
            switch ($pattern) {
                case 'all':
                    echo json_encode($this->gridData);
                    break;
                case 'recent':
                    foreach ($rawData as $k => $v) {
                        if (strcmp($v['IS_RECENT'],'NO'))
                            unset($rawData[$k]);
                    }
                    echo json_encode(array_values($rawData));
                    break;
                case 'old':
                    foreach ($rawData as $k => $v) {
                        if (strcmp($v['IS_RECENT'],'YES'))
                            unset($rawData[$k]);
                    }
                    echo json_encode(array_values($rawData));
                    break;
                default:
                    break;
            }
        }



    }
}