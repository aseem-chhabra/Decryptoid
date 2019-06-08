<?php
require_once "login.php";
if(isset($_POST["selectPart"]) && $_POST["selectPart"] == "ss")
{
  	header('location: simplesubstitution.php');
}
else if(isset($_POST["selectPart"]) && $_POST["selectPart"] == "dt")
{
  	header('location: doubletransposition.php');
}
else if(isset($_POST["selectPart"]) && $_POST["selectPart"] == "rc")
{
  	header('location: rc4.php');
}
echo"<br />";
echo <<<_END
<body background="bg.png">
Welcome to Decryptoid!<br>
Select an Algorithm that you want to use:
<br><br>
<form action="useroperations.php" method="POST">
<select name = "selectPart">
    <option>---Select One---</option>
    <option value="ss">Simple Substitution</option>
    <option value="dt">Double Transposition</option>
    <option value="rc">RC4</option>
</select>
<input type = "submit">
</form>
_END;
createDatabases($hn, $un, $pw, $db);
function createDatabases($hn, $un, $pw, $db)
{
    $conn = new mysqli($hn, $un, $pw, $db);
    if ($conn->connect_error)
            die($conn->connect_error);
    $result = $conn->query("SHOW TABLES LIKE 'cipher'");
    if($result->num_rows === 0){
         $cipher = "CREATE TABLE cipher (
                id int AUTO_INCREMENT KEY,
                userN char(64),
                cipherU varchar(64),
                time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                keyVal varchar(64),
                start varchar(64)
            )";
        $conn->query($cipher);
    }
}
?>
