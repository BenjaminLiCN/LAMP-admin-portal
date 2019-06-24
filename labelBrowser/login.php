

<?php
include "header.html";
include "session.php";
online_session_start(true);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login Page</title>
</head>
<body>
<span id="user" style="display:none"></span>
<span id="admin" style="display:none">false</span>
<div class="container">
    <div class="d-flex justify-content-center h-100">
        <div data-role="popup" id="loginPopup" data-overlay-theme="b" data-theme="b" data-dismissible="false" data-position-to="window">
        <div class="card">
            <input onclick="window.location = 'Welcomepage.html' " class="btn" value="Go back">
            <div class="card-header">
                Welcome Back
            </div>
            <div class="card-body">
                <form>
                    <div class="input-group form-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                        </div>
                        <input type="text" class="form-control" id="username" placeholder="Enter user name">
                    </div>
                    <div class="input-group form-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-key"></i></span>
                        </div>
                        <input type="password" class="form-control" id="password" placeholder="password">
                    </div>
                    <div class="row align-items-center remember">
                        <input type="checkbox">Remember Me
                    </div>
                    <div class="form-group">
                        <input type="button" onclick="doLogin()" class="btn float-right login_btn">
                    </div>
                </form>
            </div>
            <div class="card-footer">
                <div class="d-flex justify-content-center links">
                    Don't have an account?<a href="Register.html">Sign Up</a>
                </div>
                <div class="d-flex justify-content-center">
                    <a href="#">Forgot your password?</a>
                </div>
            </div>
        </div>
    </div>
</div>
<input id="PHPSESSID" name="PHPSESSID" type="hidden" value="<?php print session_id(); ?>">
</body>
<script>
    function doLogin() {
        var password = $("#password").val();
        var username = $("#username").val();
        $.ajax({
            dataType: 'json',
            method: 'post',
            data: {
                username: username,
                password: password,
                cmd: 'Login'
            },
            error: function(data) {
                alert('Unexpected login failure');
                return(false);
            },
            success: function(data) {
                if (data.result == 'success') {
                    window.location.assign('');
                    window.location.assign(data.url);
                }
                else {
                    $("#loginPopup").html('\
						<div data-role="header" data-theme="a">\
							<h1 style="color:#FF4;">\
							&nbsp;<i class="fa fa-exclamation-triangle"></i>\
							Login failed</h1>\
						</div>\
						<div role="main" class="ui-content">\
							'+data.message+'\
							<p/>\
							<div>\
							<a href="#" class="ui-btn ui-corner-all ui-shadow ui-btn-inline ui-btn-b" data-rel="back">Try again</a>\
						</div>\
					')
                    $("#loginPopup").popup("open");
                    $('#loginPopup').popup('reposition', 'positionTo: window');
                }
                return(true);
            }
        });
    }

</script>
</html>

