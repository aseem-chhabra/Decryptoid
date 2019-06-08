<?php
require_once "login.php";
$conn = new mysqli($hn, $un, $pw, $db);
if(isset($_FILES['filename']) && isset($_POST['input']) && isset($_POST["selectPart"]) && $_FILES['filename']['type'] == "text/plain")
{
    $action = $_POST["selectPart"];
    $userFile = fopen($_FILES['filename']['tmp_name'], "r") or die("Unable to open file!");
    $myFile = fread($userFile,filesize($_FILES['filename']['tmp_name']));
    $key = strtolower(mysql_entities_fix_string($conn, $_POST['input']));
    if($action === "encrypt"){
        $obj = new RC();
        $cipher_data = $obj->rc_f($key, $myFile);
        echo $cipher_data;
        echo "<br><br>";
    }
    else if($action === "decrypt" && (trim($_POST['input'])!="")){
      $obj = new RC();
      $cipher_data = $obj->rc_f($key, $myFile);
      //echo $cipher_data;
      echo "<br><br>";
    }
    else
        echo "Enter the key";
    destoryArraysEver();
}

class RC {
  public function rc_f($key, $plainText) {
      $s = array();
      for ($i = 0; $i < 256; $i++)
          $s[$i] = $i;

      $t = array();
      for ($i = 0; $i < 256; $i++)
          $t[$i] = ord($key[$i % strlen($key)]);

      $j = 0;
      for ($i = 0; $i < 256; $i++) {
          $j = ($j + $s[$i] + $t[$i]) % 256;
          $temp = $s[$i];
          $s[$i] = $s[$j];
          $s[$j] = $temp;
      }

      $i = 0;
      $j = 0;
      $cipherText = '';
      for ($y = 0; $y < strlen($plainText); $y++) {
          $i = ($i + 1) % 256;
          $j = ($j + $s[$i]) % 256;
          $x = $s[$i];
          $s[$i] = $s[$j];
          $s[$j] = $x;
          $cipherText .= $plainText[$y] ^ chr($s[($s[$i] + $s[$j]) % 256]);
      }
      return $cipherText;
  }
}
function destoryArraysEver()
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
        <html>
            <head>
                <title>RC4</title>
            </head>
            <body background="bg.png">
            <table border="0" cellpadding="2" cellspacing="5" bgcolor="#eeeeee">
            <th colspan="2" align="center">RC4</th>
            <form method = "post" action ="rc4.php" enctype='multipart/form-data'>
              <tr>
                    <td>Key:</td>
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
