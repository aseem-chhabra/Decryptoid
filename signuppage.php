<?php
echo <<<_END
        <html>
            <head>
                <title>Decryptoid</title>
                <script>
                  function validate(form) {
                  	fail = validateUsername(form.username.value)
                  	fail += validatePassword(form.password.value)
                  	fail += validateEmail(form.email.value)

                  	if (fail == "") return true
                  	else { alert(fail); return false }
                  }
                  function validateUsername(field)
                  {
                      val = "";
                      if (field == "")
                          val += "No Username was entered.\n"
                      if (field.length < 3)
                          val += "Usernames must be at least 3 characters.\n"
                      if (/[^a-zA-Z0-9_-]/.test(field))
                          val += "Only a-z, A-Z, 0-9, - and _ allowed in Usernames.\n"
                      return val
                  }

                  function validatePassword(field)
                  {
                      val=""
                      if (field == "")
                          val += "No Password was entered.\n"
                      if (field.length < 3)
                          val += "Passwords must be at least 3 characters.\n"
                      if (!/[a-z]/.test(field) || ! /[A-Z]/.test(field) ||!/[0-9]/.test(field))
                          val += "Passwords require one each of a-z, A-Z and 0-9.\n"
                      return val
                  }
                  function validateEmail(field)
                  {
                      val = ""
                      if (field == "")
                          val += "No Email was entered.\n"
                      if (!((field.indexOf(".") > 0) && (field.indexOf("@") > 0)) || /[^a-zA-Z0-9.@_-]/.test(field))
                          val += "The Email address is invalid.\n"
                      return val
                  }
            </script>
            </head>
            <body background="bg.png">
            <h1> New User Signup </h1>
                <table border="0" cellpadding="2" cellspacing="5" bgcolor="#eeeeee">
                <th colspan="2" align="center">Signup Form</th>
                <form method = "post" action ="authentication.php" onsubmit="return validate(this)">
                    <tr>
                    <td>Username </td>
                    <td><input type = "text" maxlength="16" name = "username"></td>
                    </tr>
                    <tr>
                    <td>Password </td>
                    <td><input type = "password" maxlength="12" name = "password"></td>
                    </tr>
                    <tr>
                    <td>Email Address</td>
                    <td><input type = "text" maxlength="64" name = "email"></td>
                    </tr>
                    <input type = "hidden" value = "signup" name="sign" >
                    <tr><td colspan="2" align="center"><input type = "submit" value="Signup"></td></tr>
                </form>
                </table>
            </body>
        </html>
_END;

?>
