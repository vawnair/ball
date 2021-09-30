<?php
require 'protected/connection.php';
require 'protected/functions.php';
error_reporting(0);
$conn = get_connection();


session_start();

if (isset($_POST['username']) && isset($_POST['password'])) {
    $logincall = login($conn, $_POST['username'], $_POST['password']);

    switch ($logincall) {

        case 'noexistaccount':
            $loginresponse = ("This account does not exist!");
            break;
        case 'missingfields':
            $loginresponse = ("Some fields are missing!");
            break;
        case 'invalidpassword':
            $loginresponse = ("Invalid password!");
            break;
        case 'success':
            $loginresponse = ("Success! Redirecting...");
            header('Location: /panel/index.php');
            break;
    }
}

function login(mysqli_wrapper $conn, $username, $password)
{
    $account_query = $conn->query('SELECT * FROM users WHERE username=?', [$username]);
    $account_data = $account_query->fetch_assoc();

    if (empty($username) || empty($password)) {
        return 'missingfields';
    }

    if ($account_query->num_rows == 0) {
        return 'noexistaccount';
    }

    if (!password_verify($password, $account_data["password"])) {
        return 'invalidpassword';
    }

    if (password_verify($password, $account_data["password"])) {

        $_SESSION['username'] = $account_data['username'];
        $_SESSION['invitedBy'] = $account_data['invitedBy'];
        $_SESSION['uploadKey'] = $account_data['uploadKey'];
        $_SESSION['ip'] = $account_data['ip'];
        $_SESSION['isAdmin'] = $account_data['isAdmin'];
        $_SESSION['isBanned'] = $account_data['isBanned'];
        $_SESSION['banReason'] = $account_data['banReason'];

        return 'success';
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>Login</title>
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/fonts/ionicons.min.css">
    <link rel="stylesheet" href="assets/css/Login-Form-Clean.css">
    <link rel="stylesheet" href="assets/css/styles.css">
</head>

<body style="background: rgb(25,25,25);">

    <section class="login-clean" style="background: rgb(25,25,25);">
        <?php if ($loginresponse) { ?>
            <div class="container">
                <div class="row">
                    <div class="col-md-12 text-center"><button class="btn btn-primary" type="button" style="background: rgb(25,25,25);"><?php echo $loginresponse; ?></button></div>
                </div>
            </div>
        <?php } else {
        } ?>
        <br>
        <form method="POST" style="background: rgb(30,30,30);">
            <h2 class="visually-hidden">Login Form</h2>
            <div class="illustration"><i class="icon ion-log-in" style="border-color: rgb(15,111,255);color: rgb(71,99,244);"></i></div>
            <div class="mb-3"><input class="form-control" type="username" name="username" placeholder="username" style="filter: contrast(100%);"></div>
            <div class="mb-3"><input class="form-control" type="password" name="password" placeholder="Password"></div>

            <div class="mb-3"><button class="btn btn-primary d-block w-100" type="submit" style="color: rgb(0,0,0);background: rgb(71,99,244);">Log In</button>
        </form>
        <br>
        <p>Need an account? Register <a href="register.php">here</a>
    </section>
    <script src="assets/bootstrap/js/bootstrap.min.js"></script>
</body>

</html>