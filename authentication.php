<?php
//get the login file for connection to db
require_once 'login.php';
$connection = new mysqli($hn, $un, $pw, $db);
//check for username and password
if(isset($_POST['username']) && isset($_POST['password']))
{
    if($_POST['username']!="" && $_POST['password']!="")
    {
        $_SERVER['PHP_AUTH_USER'] = $_POST['username'];
        $_SERVER['PHP_AUTH_PW'] = $_POST['password'];
    }
}
//if connection error while connecting to database
// kill the connection with die
if ($connection->connect_error) die($connection->connect_error);
if (isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW']))
{
    $un_temp = mysql_entities_fix_string($connection, $_SERVER['PHP_AUTH_USER']);
    $pw_temp = mysql_entities_fix_string($connection, $_SERVER['PHP_AUTH_PW']);
    $q = "SELECT * FROM users WHERE username='$un_temp'";
    $res = $connection->query($q);
    if (!$res) die($connection->error);
    elseif(isset($_POST['sign']))
    {
      //if here from signuppage enter the following loop else go to the next one
        if($_POST['sign'] == "signup" && $res->num_rows === 0 && isset($_POST['email']))
        {
            if($_POST['email']!="")
            {
                $email = mysql_entities_fix_string($connection, $_POST['email']);
                $salt1 = "qm&h*";
                $salt2 = "pg!@";
                $token = hash('ripemd128', "$salt1$pw_temp$salt2");
                $query = "INSERT INTO users (username, password, email) VALUES ('$un_temp','$token', '$email')";
                $connection->query($query);
                die ("Your profile has been created! To login <a href=loginpage.php>click here</a>");
            }
        }
        // if redirected from login page enter the following loop
        elseif($res->num_rows && $_POST['sign'] == "signin")
        {
            $row = $res->fetch_array(MYSQLI_NUM);
            $res->close();
            $salt1 = "qm&h*";
            $salt2 = "pg!@";
            $token = hash('ripemd128', "$salt1$pw_temp$salt2");
            if ($token == $row[1])
            {
                session_start();
                $_SESSION['username'] = $un_temp;
                $_SESSION['email'] = $row[2];
                echo "Welcome $row[0]! you are now logged in";
                die (header('location: useroperations.php'));
            }
        }
    }
}
// if credentials do not match
// output to user about wrong credentials
// and display about the signup and login
echo "Invalid credentials.<br>
      To login: <a href='loginpage.php'> Login </a> <br>
      New user SignUp here: <a href='signuppage.php'> SignUp</a>";
$connection -> close();

function mysql_entities_fix_string($connection, $string)
{
    return htmlentities(mysql_fix_string($connection, $string));
}
 function mysql_fix_string($connection, $string)
 {
     if (get_magic_quotes_gpc()) {
         $string = stripslashes($string);
     }
     return $connection->real_escape_string($string);
 }
?>
