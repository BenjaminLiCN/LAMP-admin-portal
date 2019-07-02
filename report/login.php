<?php
ob_start();
session_start();
include "header.html";
include "database.php";

$username = $_REQUEST['username'];


//TODO: trim the code and connect to database
$error_msg = "";
//if $_SESSION['uid'] is invalid, do the following
if(!isset($_SESSION['uid'])){
    if(isset($_POST['submit'])){//user posted a request
        $dbc = mysqli_connect(DEV_HOST,DB_USER,DEV_PASSWORD,DB_NAME);
        $user_username = mysqli_real_escape_string($dbc,trim($_POST['username']));
        $user_password = mysqli_real_escape_string($dbc,trim($_POST['password']));

        if(!empty($user_username)&&!empty($user_password)){
            //one-way encryption
            //PASSWORD = SHA('".$user_password."')
            $query = "select UID,USERNAME from USER where USERNAME = '".$user_username."' and "."PASSWORD = SHA('".$user_password."')";
            $data = mysqli_query($dbc,$query);
            //there's exactly one row matches
            if(mysqli_num_rows($data)==1){
                $row = mysqli_fetch_array($data);
                $_SESSION['uid']=$row['uid'];
                $_SESSION['username']=$row['username'];
                $home_url = 'salesReport.php';
                header('Location: '.$home_url);
            }else{//wrong password
                $error_msg = 'Sorry, you must enter a valid username and password to log in.';
            }
        }else{
            $error_msg = 'Sorry, you must enter a valid username and password to log in.';
        }
    }
}else{//如果用户已经登录，则直接跳转到已经登录页面
    $home_url = 'salesReport.php';
    header('Location: '.$home_url);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login Page</title>
</head>
<body id="loginBody">
<span id="user" style="display:none"></span>
<span id="admin" style="display:none">false</span>
<div class="container" style="margin-top: 70px">
    <div class="d-flex justify-content-center h-100">
        <div data-role="popup" id="loginPopup" data-overlay-theme="b" data-theme="b" data-dismissible="false" data-position-to="window">
        <div class="card">
            <div class="card-header">
                Welcome Back
            </div>
            <div class="card-body" style="margin-top: 20px">
                <?php
                if(!isset($_SESSION['user_id'])){
                    echo '<p class="error">'.$error_msg.'</p>';
                    ?>
                    <form method = "post" action="<?php echo $_SERVER['PHP_SELF'];?>">
                        <fieldset>
                            <label for="username" style="color:white">Username:</label>
                            <!-- Display user name if already entered before -->
                            <input type="text" id="username" name="username" style="margin-bottom: 10px"
                                   value="<?php if(!empty($user_username)) echo $user_username; ?>" />
                            <label for="password" style="color:white">Password:</label>
                            <input style="margin-left:3px" type="password" id="password" name="password"/>

                        </fieldset>
                        <input type="submit" value="Log In" style="margin-top: 50px" name="submit"/>
                    </form>
                    <?php
                }
                ?>
            </div>
            <div class="card-footer">
<!--                <div class="d-flex justify-content-center links">-->
<!--                    Don't have an account?<a href="Register.html">Sign Up</a>-->
<!--                </div>-->
<!--                <div class="d-flex justify-content-center">-->
<!--                    <a href="#">Forgot your password?</a>-->
<!--                </div>-->
            </div>
        </div>
    </div>
</div>
<input id="PHPSESSID" name="PHPSESSID" type="hidden" value="<?php print session_id(); ?>">
</body>

</html>

