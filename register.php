<?php /** @noinspection PhpUndefinedMethodInspection */
//login.php
//manage login with PDO
//manage sessions
require_once("./lib/DB.php");

//PDO singleton - Postgres implementation/dialect
use PDOSingleton\Postgres as DB;

//open up the session
session_start();
//home page
const LOGIN_SUCCESS_ROUTE = "login.php";
//default message
$message = "";
try { //user is already logged in - send em' home
    if (isset($_SESSION["username"])) {
        header("location: " . LOGIN_SUCCESS_ROUTE);
    } else { //user isn't logged in - proceed
        if (isset($_POST["register"])) {//process the form request
            if (empty($_POST["username"]) || empty($_POST["password"])) {//check fields for user info
                $message = "<label style='color:red'>All fields are required</label>";
            } else {//send request for credentials to database
                $sql = "INSERT INTO admin.users(username, password)VALUES(?, ?)";
                $stmt = DB::prepare($sql);
                $username = $_POST["username"];
                $password_hash = password_hash($_POST["password"], PASSWORD_DEFAULT);
                $stmt->execute([$username, $password_hash]);
                $verifyCount = DB::lastInsertId();
                if ($verifyCount > 0) {//got a valid database hit
                    $_SESSION["username"] = $username;//store the username for display
                    header("location: " . LOGIN_SUCCESS_ROUTE);//send to log in (which should send them home)
                } else {//display if there is an error with the database request
                    $message = "<label style='color:red'>REGISTRATION FAILURE -- CONTACT ADMINISTRATOR</label>";
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
    <title>Register</title>
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
    <h3 align="">My Website Sign Up</h3><br/>
    <form method="post">
        <label>Username</label>
        <input type="text" name="username" class="form-control"/>
        <br/>
        <label>Password</label>
        <input type="password" name="password" class="form-control"/>
        <br/>
        <input type="submit" name="register" class="btn btn-info" value="Register"/>
    </form>
</div>
<br/>
</body>
</html>