<?php
require_once "login.php";
$conn = new mysqli($hn, $un, $pw, $db);
if(isset($_FILES['filename']) && isset($_POST['input']) && isset($_POST['input2']) && isset($_POST["selectPart"]) && $_FILES['filename']['type'] == "text/plain")
{
    $action = $_POST["selectPart"];
    $userFile = fopen($_FILES['filename']['tmp_name'], "r") or die("Unable to open file!");
    $myFile = fread($userFile,filesize($_FILES['filename']['tmp_name']));
    if($action === "encrypt")
        myCrypt($myFile, $conn, $un);
    else if($action === "decrypt" && (trim($_POST['input'])!="") || (trim($_POST['input2'])!=""))
        myDecrypt($myFile, $conn, $un);
    else
        echo "Enter the keys";
    destoryArraysEver();
}
function myCrypt($myFile, $conn, $un)
{
    $key = strtolower(mysql_entities_fix_string($conn, $_POST['input']));
    $key = str_replace(' ','',$key);
    $key2 = strtolower(mysql_entities_fix_string($conn, $_POST['input2']));
    $key2 = str_replace(' ','',$key2);
    $salt1 = "qm&h*";
    $salt2 = "pg!@";
    $token = hash('ripemd128', "$salt1$key$key2$salt2");
    $query = "SELECT * from cipher where userN = '$un' and cipherU = 'doubletransposition'";
    $result = $conn->query($query);
    if($result->num_rows == 0)
    {
        $query_Insert = "INSERT INTO cipher (userN, cipherU, keyVal, start) VALUES ('$un', 'doubletransposition', '$key $key2', '$token')";
        $conn -> query($query_Insert);
    }
    else if($key!="" && $key2!="")
    {
        $query_Update = "UPDATE cipher
                    SET keyVal = '$key $key2', start = '$token' WHERE userN = '$un' AND cipherU = 'doubletransposition'";
        $conn -> query($query_Update);
        $conn->commit();
        echo "Keys Changed<br><br>";
    }
    else
    {
        for($i = 0; $i < $result->num_rows; ++$i)
        {
            $result -> data_seek($i);
            $r = $result->fetch_array(MYSQLI_ASSOC);
            $letters = explode(' ',$r['keyVal']);
        }
        $key = $letters[0];
        $key2 = $letters[1];
    }
    echo "Plain Text:<br>".$myFile;
    $myFile = str_replace(' ', '!', $myFile);
    $array = putInArray($myFile, $key);
    $transpose_one = myTranspose($array, $key);
    $v = "";
    foreach ( $transpose_one as $var ) {
        foreach ($var as $x)
        {
            $v.=$x;
        }
    }
    $array = putInArray($v, $key2);
    $transpose_two = myTranspose($array, $key2);
    $finalStr = "";
    echo "<br>";
    foreach ( $transpose_two as $var ) {
        foreach ($var as $x){
            $finalStr .= $x;
        }
    }
    echo "<br>Encrypted text:<br>".$finalStr."<br><br>";
}
function destoryArraysEver()
{
    $_POST = array();
    $_FILES = array();
}
function myTranspose($inputArr, $str)
{
    $string = $str;
    $stringParts = str_split($string);
    sort($stringParts);
    $rows = count($inputArr);
    $transpose_one = array ();
    for($i = 0; $i < $rows; $i++)
        array_push($transpose_one, array());
    for($i = 0; $i < count($stringParts); $i++)
    {
        $posi = strpos($str,$stringParts[$i]);
        $from = '/'.preg_quote($stringParts[$i], '/').'/';
        $str = preg_replace($from,"#", $str, 1);
        for($j = 0; $j < $rows; $j++)
        {
            $transpose_one[$j][$i] = $inputArr[$j][$posi];
        }
    }
    return $transpose_one;
}
function myDecrypt($myFile, $conn, $un)
{
    $query = "SELECT start from cipher where userN = '$un' and cipherU = 'doubletransposition'";
    $result = $conn->query($query);
    for($i = 0; $i < $result->num_rows; ++$i)
    {
        $result -> data_seek($i);
        $r = $result->fetch_array(MYSQLI_ASSOC);
        $compare = $r['start'];
    }
    $salt1 = "qm&h*";
    $salt2 = "pg!@";
    $key = strtolower(mysql_entities_fix_string($conn, $_POST['input']));
    $key2 = strtolower(mysql_entities_fix_string($conn, $_POST['input2']));
    $token = hash('ripemd128', "$salt1$key$key2$salt2");
    if($token !== $compare)
    {
        echo "Keys don't match. Retry. <br><br>";
        return;
    }
    echo "The cipherText is: <strong>$myFile</strong>";
    $array = putInArray($myFile, $key2);
    $array = transposeBack($array, $key2);
    $v = "";
    foreach ($array as $var) {
        foreach ($var as $x)
        {
           $v.=$x;
        }
    }
    $array = putInArray($v, $key);
    echo "<br>";
    $array = transposeBack($array, $key);
    echo "<br>Original Text<br>";
    $line = "";
    foreach ( $array as $var ) {
        foreach ($var as $x)
        {
           $line.= $x;
        }
    }
    $line = str_replace('!', ' ', $line);
    $line = str_replace('-', ' ', $line);
    echo "<strong>$line</strong>";
    echo "<br><br>";
}
function transposeBack($inputArr, $str)
{
    $stringParts = str_split($str);
    sort($stringParts);
    $string = implode("",$stringParts);
    $rows = count($inputArr);
    $solvedArr = array();
    for($i = 0; $i < count($inputArr); $i++)
        array_push($solvedArr, array());
    for($i = 0; $i < strlen($str); $i++)
    {
        $ch = $str{$i};
        $findMe = strpos($string, $ch);
        $from = '/'.preg_quote($ch, '/').'/';
        $string = preg_replace($from,"#", $string, 1);
        for($v = 0; $v < count($inputArr); $v++)
            $solvedArr[$v][$i]=$inputArr[$v][$findMe];
    }
    return $solvedArr;
}
function putInArray($str, $key)
{
    $inputArr = array(
        array()
    );
    $row = 0;
    for($i = 0; $i < strlen($str); $i++)
    {
        array_push($inputArr[$row], $str{$i});
        if($i%strlen($key)==(strlen($key)-1) && $i!=0
          && (filesize($_FILES['filename']['tmp_name'])-1)!=$i)
        {
            array_push($inputArr, array());
            $row++;
        }
    }
    while(count($inputArr[count($inputArr)-1]) < strlen($key))
    {
        array_push($inputArr[$row], "-");
    }
    return $inputArr;
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
        <html>
            <head>
                <title>Decryptoid</title>
            </head>
            <body background="bg.png">
            <table border="0" cellpadding="2" cellspacing="5" bgcolor="#eeeeee">
            <th colspan="2" align="center"> Double Transformation </th>
                <form method = "post" action ="doubletransposition.php" enctype='multipart/form-data'>
                <tr>
                    <td>First Key:</td>
                    <td><input type = "text" name = "input" text = null></td>
                    <br><br>
                </tr>
                <tr>
                    <td>Second Key:</td>
                    <td><input type = "text" name = "input2" text = null></td>
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
                    <td>Select File:</td>
                    <td><input type='file' name='filename' size='10'></td>
                    <br><br>
                    <input type = "hidden" value = "signin" name="sign" >
            <tr><td colspan="2" align="center"><input type = "submit" value="Submit"></td></tr>
                </form>
            </body>
        </html>
_END;
?>
