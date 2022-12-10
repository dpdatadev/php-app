<?php
//login.php
//manage login with PDO
//manage sessions
require_once("./lib/DB.php");

//PDO singleton - Postgres implementation/dialect
use PDOSingleton\Postgres as DB;

//open up the session
session_start();
//home page
const LOGIN_SUCCESS_ROUTE = "index.php";
//default message
$message = "";
try { //user is already logged in - send em' home
    if (isset($_SESSION["username"])) {
        header("location: " . LOGIN_SUCCESS_ROUTE);
    } else { //user isn't logged in - proceed
        if (isset($_POST["login"])) {//process the form request
            if (empty($_POST["username"]) || empty($_POST["password"])) {//check fields for user info
                $message = "<label style='color:red'>All fields are required</label>";
            } else {//send request for credentials to database
                $formUsername = $_POST["username"];
                $formPassword = $_POST["password"];
                $sql = "SELECT * FROM admin.users WHERE username = :username";
                $query = DB::prepare($sql);
                $query->execute(array(':username' => $formUsername));
                $userFetch = $query->fetch();
                if ($query->rowCount() > 0) {
                    $dbUserName = $userFetch["username"];
                    $dbPassword = $userFetch["password"];
                    if ($formUsername == $dbUserName) {
                        if (password_verify($formPassword, $dbPassword)) {
                            $_SESSION["username"] = $dbUserName;//store the username for display
                            header("location: " . LOGIN_SUCCESS_ROUTE);//send home
                        } else {
                            $message = "<label style='color:red'>LOGIN FAILURE -- INVALID PASSWORD</label>";
                        }
                    } else {
                        $message = "<label style='color:red'>LOGIN FAILURE -- INVALID USERNAME</label>";
                    }
                }
            }
        }
    }
} catch (PDOException $e) { //debug - show generic message but log the PDOException
    $message = $e->getMessage();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.0/jquery.min.js"></script>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css"/>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
</head>
<body>
<br/>
<div class="container" style="width:500px;">
    <?php
    if (isset($message)) {
        echo '<label class="text-danger">' . $message . '</label>';
    }
    ?>
    <h3 align="">My Website Log In</h3><br/>
    <form method="post">
        <label>Username</label>
        <input type="text" name="username" class="form-control"/>
        <br/>
        <label>Password</label>
        <input type="password" name="password" class="form-control"/>
        <br/>
        <input type="submit" name="login" class="btn btn-info" value="Login"/>
    </form>
</div>
<br/>
</body>
</html>