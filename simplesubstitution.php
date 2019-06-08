<?php
require_once 'login.php';
$conn = new mysqli($hn, $un, $pw, $db);
if(isset($_FILES['filename']) && isset($_POST["selectPart"]) && $_POST["selectPart"] == "encrypt" && $_FILES['filename']['type'] == "text/plain")
{
    $myfile = fopen($_FILES['filename']['tmp_name'], "r") or die("Unable to open file!");
    $file = fread($myfile,filesize($_FILES['filename']['tmp_name']));
    $file = strtolower($file);
    if(isset($_POST['input']))
        $str = $_POST['input'];
    else
        $str = "";
    $str = strtolower(mysql_entities_fix_string($conn, $str));
    $letters = getKey($str);
    $query = "SELECT * from cipher where userN = '$un' and cipherU = 'simplesubstitution'";
    $result = $conn->query($query);
    $salt1 = "qm&h*";
    $salt2 = "pg!@";
    $token = hash('ripemd128', "$salt1$str$salt2");
    if($result->num_rows == 0)
    {
        $queryInsert = "INSERT INTO cipher (userN, cipherU, keyVal, start) VALUES ('$un', 'simplesubstitution', '$letters', '$token')";
        $conn -> query($queryInsert);
    }
    else if($str!="")
    {
        $queryUpdate = "UPDATE cipher SET keyVal = '$letters', start = '$token' WHERE userN = '$un' AND cipherU = 'simplesubstitution'";
        $conn -> query($queryUpdate);
        $conn->commit();
        echo "Key Changed<br>";
    }
    else
    {
        for($i = 0; $i < $result->num_rows; ++$i)
        {
            $result -> data_seek($i);
            $r = $result->fetch_array(MYSQLI_ASSOC);
            $letters = $r['keyVal'];
        }
    }
    myCrypt($file, $letters);
    destroyArrays();
}
else if(isset($_FILES['filename']) && isset($_POST["selectPart"]) && $_POST["selectPart"] == "decrypt")
{
    $myfile = fopen($_FILES['filename']['tmp_name'], "r") or die("Unable to open file!");
    $file = fread($myfile,filesize($_FILES['filename']['tmp_name']));
    $file = strtolower($file);
    $file = preg_replace('/[^a-z]+/i', ' ', $file);

    $query = "Select keyVal, start from cipher where userN = '$un' AND cipherU = 'substitutionCipher'";
    $result = $conn -> query($query);
    $rows = $result->num_rows;
    for($i = 0; $i < $result->num_rows; ++$i)
    {
        $result -> data_seek($i);
        $r = $result->fetch_array(MYSQLI_ASSOC);
        $letters = $r['keyVal'];
        $start = $r['start'];
    }
    if(isset($_POST['input']))
        $str = $_POST['input'];
    else
        $str = "";
    $str = strtolower(mysql_entities_fix_string($conn, $str));
    $salt1 = "qm&h*";
    $salt2 = "pg!@";
    $token = hash('ripemd128', "$salt1$str$salt2");
    if($token == $start)
        myDecrypt($file, $letters);
    else
        echo "<script type='text/javascript'>alert('Keys don't match');</script>";
    destroyArrays();
}
else if(isset($_FILES['filename']) || isset($_POST["selectPart"]) || isset($_POST['input']))
{
    echo <<<_END
        Error: File submitted is not a text file or the key has not been created
_END;
}
function getKey($str)
{
    $letters = "abcdefghijklmnopqrstuvwxyz";
    $result = implode(array_unique(str_split($str)),'');
    foreach(str_split($result) as $v)
    {
        $letters = str_replace($v,'',$letters);
    }
    while(strlen($letters) > 0)
    {
        $num = mt_rand(0, strlen($letters)-1);
        $result .= $letters{$num};
        $letters = str_replace($letters{$num},'',$letters);
    }
    return $result;
}
function myDecrypt($file, $key)
{
    $res = "";
    echo "Original Text:<br><strong>$file</strong><br><br>Decrypted Text:";
    for($i = 0; $i < strlen($file); $i++)
    {
        if($file{$i}!=' ')
        {
            $ch = strpos($key, $file{$i});
            $res .= chr($ch+97);
        }
        else
            $res .= ' ';
    }
    echo "<br /><strong>$res</strong><br><br>";
}
function myCrypt($file, $result)
{
    $file = preg_replace('/[^a-z]+/i', ' ', $file);
    echo "Original file:<br>".$file."<br><br>";
    $res = "";
    for($i = 0; $i < strlen($file); $i++)
    {
        //$file = str_replace($result{$i-97}, chr($i), $file);
        if($file{$i}!=' ')
        {
            $ch = ord($file{$i})-97;
            $res .= $result{$ch};
        }
        else
            $res .= ' ';
    }
    echo "Encrypted text:<br/>$res<br><br>";
}
function destroyArrays()
{
    $_POST = array();
    $_FILES = array();
}
function mysql_entities_fix_string($conn, $string)
{
    return htmlentities(mysql_fix_string($conn, $string));
}
function mysql_fix_string($conn, $string)
{
    if (get_magic_quotes_gpc()) {
        $string = stripslashes($string);
    }
    return mysqli_real_escape_string($conn, $string);
}
echo <<<_END
<br/><br/>
        <html>
            <head>
                <title>Decryptoid</title>
            </head>
            <body background="bg.png">
            <table border="0" cellpadding="2" cellspacing="5" bgcolor="#eeeeee">
            <th colspan="2" align="center"> Simple Substitution </th>
                <form method = "post" action ="simplesubstitution.php" enctype='multipart/form-data'>
                <tr>
                    <td>Key String:</td>
                    <td><input type = "text" name = "input" text = null></td>
                    <br><br>
                </tr>
                <tr>
                    <td>Actions:</td>
                    <td><select name = "selectPart">
                        <option value="encrypt">Encrypt</option>
                        <option value="decrypt">Decrypt</option>
                    </select></td>
                    <br><br>
                </tr>
                <tr>
                    <td>Select File  :</td>
                    <td><input type='file' name='filename' size='10'></td>
                    <br><br>
                </tr>
                <input type = "hidden" value = "signin" name="sign" >
                <tr><td colspan="2" align="center"><input type = "submit" value="Submit"></td></tr>
                </form>
            </body>
    </html>
_END;
?>
