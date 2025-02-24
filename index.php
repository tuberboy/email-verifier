<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="robots" content="index, follow">
    <meta name="author" content="Tuber Boy">
    <title>Email Verifier - Check if an Email is Valid or Invalid</title>
    <meta name="msapplication-TileColor" content="#786fff">
    <meta name="theme-color" content="#786fff">
    <link rel="shortcut icon" href="assets/favicon.ico" type="image/x-icon">
    <link rel="icon" href="assets/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="assets/styles.css?v=0.0.1">
</head>
<body>
    <div class="header"><a href="">Verify Email [&#x21A9;</a></div>
<?php
set_time_limit(0);
require __DIR__."/email_verifier.php";

if (isset($_POST["check"])) {
    $validEmail = $_POST["ve"];
    $usePort = $_POST["up"];
    $checkEmails = explode(PHP_EOL, trim($_POST["ce"]));
    $results = [];
    foreach ($checkEmails as $email) {
        $email = trim($email);
        if (!empty($email)) {
            $ve = new EmailVerifier($validEmail, $usePort, $email);
            $results[] = $ve->verify();
        }
    }
    $result = json_encode($results);
    echo '<div class="box"><div class="title">RESULTS</div>'.$result.'</div>';
    /*
    // To Decode JSON to PHP array
    $dataArray = json_decode($result, true);
    foreach ($dataArray as $data) {
        echo $data['email']."<br>"; // email, format_valid, mx_found, smtp_check, catch_all, role, disposable
    }
    print_r($dataArray);
    */
}
?>
    <div class="box">
        <div class="title">CHECK EMAIL VALIDATION</div>
        <form method="post">
            <input type="email" name="ve" placeholder="Enter your valid email *" value="<?php if (isset($_POST['check'])) { echo $_POST['ve']; } ?>" autocomplete="off" required>
            <input type="number" name="up" placeholder="Enter port to check - default: 25 *" value="<?php if (isset($_POST['check'])) { echo $_POST['up']; } else { echo 25; } ?>" autocomplete="off" required>
            <textarea type="text" name="ce" placeholder="Enter email (bulk supports) to check *" autocomplete="off" required></textarea>
            <input type="checkbox" class="vanish-checkbox" id="vanishCheckbox">
            <div class="vanish-button-container">
                <button name="check"><label for="vanishCheckbox">VERIFY</label></button>
		        <div class="please-wait">Please wait, checking...<span><i></i><i></i><i></i></span></div>
            </div>
        </form>
    </div>
    <div class="box">
        <div class="title">Information</div>
        <b>email</b>: The email address being verified.<br>
        <b>format_valid</b>: Checks if the email format is valid.<br>
        <b>mx_found</b>: Determines if the domain has valid mail exchange (MX) records.<br>
        <b>smtp_check</b>: Verifies if the email server accepts messages.<br>
        <b>catch_all</b>: Indicates if the domain accepts all emails, even invalid ones.<br>
        <b>role</b>: Used for company-wide purposes, managed by multiple people. Common examples: billing@, contact@, info@, support@, ceo@, admin@<br>
        <b>disposable</b>: Identifies if the email comes from a temporary or disposable email provider.
    </div>
    <div class="footer">&copy; Copyright <?php echo date("Y"); ?> - <a href="">Email Verifier</a></div>
</body>
</html>
