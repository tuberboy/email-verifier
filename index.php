<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="robots" content="index, follow">
    <meta name="author" content="Tuber Boy">
    <title>Email Checker</title>
    <style>
		body {
		  margin: 0;
		  padding: 0;
		  font-family: Arial, sans-serif;
		  display: flex;
		  justify-content: center;
		  align-items: center;
		  min-height: 100vh;
		  background: linear-gradient(to bottom, #3498db, #1abc9c);
		}

		.container {
		  text-align: center;
		  background-color: rgba(255, 255, 255, 0.8);
		  padding: 20px;
		  border-radius: 10px;
		  box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.3);
		  width: 500px;
		  margin-left: 10px;
		}
		.blur {
		  filter: blur(5px); 
		  pointer-events: none; 
		  transition: filter 0.3s ease-in-out;
		}
		h1 {
		  margin-bottom: 20px;
		  color: #333;
		}

		.email-input {
		  width: 80%;
		  padding: 10px;
		  border: none;
		  border-radius: 5px;
		  margin-bottom: 10px;
		  box-shadow: 0px 0px 5px rgba(0, 0, 0, 0.2);
		}

		.submit-btn {
		  background-color: #16a085;
		  color: #fff;
		  border: none;
		  padding: 10px 20px;
		  border-radius: 5px;
		  cursor: pointer;
		  transition: background-color 0.3s ease-in-out;
		}

		.submit-btn:hover {
		  background-color: #1abc9c;
		}
	</style>
</head>
<body>
<?php
set_time_limit(0);

/**
 * Email Checker: Created or Exists Or Active
 * Author: Tuber Boy (Masud Rana)
 * Date-Time: 7:12 PM Tuesday, August 27, 2024 (GMT+6)
 **/

function checkEmailCreated($email) {
    list($user, $domain) = explode('@', $email);
    if (!checkdnsrr($domain, 'MX')) {
        return false;
    }
    $mxHosts = [];
    getmxrr($domain, $mxHosts);
    foreach ($mxHosts as $mxHost) {
        $timeout = 15;
        $smtpConnection = fsockopen($mxHost, 25, $errno, $errstr, $timeout);
        if ($smtpConnection) {
            $response = fgets($smtpConnection);
            if (strpos($response, '220') === 0) {
                fputs($smtpConnection, "HELO " . gethostname() . "\r\n");
                $response = fgets($smtpConnection);
                fputs($smtpConnection, "MAIL FROM: <test@example.com>\r\n");
                $response = fgets($smtpConnection);
                fputs($smtpConnection, "RCPT TO: <$email>\r\n");
                $response = fgets($smtpConnection);
                if (strpos($response, '250') === 0) {
                    fclose($smtpConnection);
                    return true;
                } elseif (strpos($response, '550') !== false) {
                    if (strpos($response, '5.7.1') !== false || strpos($response, 'Service unavailable') !== false) {
                        fclose($smtpConnection);
                        return true;
                    }
                }
            }
            fclose($smtpConnection);
        } else {
			return false;
        }
    }
    return false;
}

if (isset($_POST['email'])) {
	$email = $_POST['email'];
	if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
		if (checkEmailCreated($email)) {
			echo '<div class="container"><h1><font color="blue">The email address `'.$email.'` is available!</font></h1></div>';
		} else {
			echo '<div class="container"><h1><font color="red">The email address `'.$email.'` is not created!</font></h1></div>';
		}
	} else {
		echo '<div class="container"><h1><font color="warning">The email address `'.$email.'` is not valid!</font></h1></div>';
	}
}
?>
  <div class="container">
    <h1>Email Checker</h1>
    <form method="POST">
      <input type="email" name="email" class="email-input" placeholder="Enter your email *" required>
      <button type="submit" class="submit-btn">CHECK</button>
    </form>
  </div>
</body>
</html>
