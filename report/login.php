<?php
@session_start();
include "header.html";
include "commonService.class.php";

$username = $_REQUEST['username'];


$error_msg = "";
$sid = @session_id();

$service = new commonService();

//if session doesn't exist, validate username and password by querying the database
if(!isset($_SESSION['uid'])){
    if(isset($_POST['submit'])){//user posted a request
        $dbc = mysqli_connect($service->DEV_HOST,$service->DB_USER,$service->DEV_PASSWORD,$service->DB_NAME);
        $user_username = mysqli_real_escape_string($dbc,trim($_POST['username']));
        $user_password = mysqli_real_escape_string($dbc,trim($_POST['password']));
        if(!empty($user_username)&&!empty($user_password)){
            //one-way encryption
            //PASSWORD = SHA('".$user_password."')
            $sql = "select UID,USERNAME from USER where USERNAME = '".$user_username."' and "."PASSWORD = SHA('".$user_password."');";
            $db = $service->openDB();
            $data = $service->queryDB($db,$sql);

            //there's exactly one row matches
            if(mysqli_num_rows($data)==1){
                $row = mysqli_fetch_array($data);
                $_SESSION['uid']=$row['UID'];
                $_SESSION['username']=$row['USERNAME'];
                $home_url = 'salesReport.php';
                print "<script>
                    location.href = '".$home_url."?PHPSESSIONID=".$sid."&uid=".$_SESSION['uid']."';
                </script>";
            }else{//wrong password
                $error_msg = 'Sorry, you must enter a valid username and password to log in.';
            }
        }else{
            var_dump("4");
            $error_msg = 'Sorry, you must enter a valid username and password to log in.';
        }
    }
}else{
    $home_url = 'salesReport.php';
    print "<script>
                    location.href = '".$home_url."?PHPSESSIONID=".$sid."';
                </script>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login Page</title>
</head>
<body id="loginBody">
<div class="container" style="margin-top: 70px">
    <div class="d-flex justify-content-center h-100">
        <div data-role="popup" id="loginPopup" data-overlay-theme="b" data-theme="b" data-dismissible="false" data-position-to="window">
            <div class="card">
                <div class="card-header">
                    <a href="#" title="Username:ben, password:951202." style="color:white;text-decoration:none" class="easyui-tooltip">Password show</a>
                </div>
                <div class="card-body" style="margin-top: 20px">
                    <?php
                    if(!isset($_SESSION['user_id'])){
                        echo '<p class="error" style="color:#ffffff">'.$error_msg.'</p>';
                        ?>
                        <form method = "post" action="<?php echo $_SERVER['PHP_SELF'];?>">
                            <fieldset>
                                <label for="username" style="color:white">Username:</label>
                                <!-- Display user name if already entered before -->
                                <input type="text" id="username" placeholder="ben" name="username"  style="margin-bottom: 10px"
                                       value="<?php if(!empty($user_username)) echo $user_username; ?>" />
                                <label for="password" style="color:white">Password:</label>
                                <input style="margin-left:3px" placeholder="951202" type="password" id="password" name="password"/>

                            </fieldset>
                            <input type="submit" value="Log In" style="margin-top: 50px" name="submit"/>
                        </form>
                        <?php
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>
</body>

</html>

