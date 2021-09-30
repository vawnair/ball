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

                <?php
                error_reporting(0);
                require 'protected/connection.php';
                require 'protected/functions.php';

                $conn = get_connection();



                if (isset($_POST['username']) && isset($_POST['password']) && isset($_POST['invite'])) {
                    $register_call = register($conn, $_POST['username'], $_POST['password'], $_POST['invite']);


                    switch ($register_call) {

                        case 'password_short':
                ?>

                            <div class="container">
                                <div class="row">
                                    <div class="col-md-12 text-center"><button class="btn btn-primary" type="button" style="background: rgb(25,25,25);"><?php echo "Password is too short!"; ?></button></div>
                                </div>
                            </div>

                        <?php
                            break;
                        case 'username_short':
                        ?>

                            <div class="container">
                                <div class="row">
                                    <div class="col-md-12 text-center"><button class="btn btn-primary" type="button" style="background: rgb(25,25,25);"><?php echo "Username is too short!"; ?></button></div>
                                </div>
                            </div>

                        <?php
                            break;
                        case 'missing_username':
                        ?>

                            <div class="container">
                                <div class="row">
                                    <div class="col-md-12 text-center"><button class="btn btn-primary" type="button" style="background: rgb(25,25,25);"><?php echo "Username is empty!"; ?></button></div>
                                </div>
                            </div>

                        <?php
                            break;

                        case 'user_exists':
                        ?>

                            <div class="container">
                                <div class="row">
                                    <div class="col-md-12 text-center"><button class="btn btn-primary" type="button" style="background: rgb(25,25,25);"><?php echo "Username is already taken!"; ?></button></div>
                                </div>
                            </div>

                            <?php
                            break;

                            ?>



                        <?php
                        case 'forbidden_username':


                        ?>

                            <div class="container">
                                <div class="row">
                                    <div class="col-md-12 text-center"><button class="btn btn-primary" type="button" style="background: rgb(25,25,25);"><?php echo "This username is disallowed!"; ?></button></div>
                                </div>
                            </div>

                        <?php
                            break;

                        case 'invite_invalid':
                        ?>

                            <div class="container">
                                <div class="row">
                                    <div class="col-md-12 text-center"><button class="btn btn-primary" type="button" style="background: rgb(25,25,25);"><?php echo "Invite is unknown!"; ?></button></div>
                                </div>
                            </div>

                        <?php
                            break;

                        case 'invite_used':
                        ?>

                            <div class="container">
                                <div class="row">
                                    <div class="col-md-12 text-center"><button class="btn btn-primary" type="button" style="background: rgb(25,25,25);"><?php echo "Invite is already used!"; ?></button></div>
                                </div>
                            </div>

                        <?php
                            break;

                        case 'missing_invite':
                        ?>

                            <div class="container">
                                <div class="row">
                                    <div class="col-md-12 text-center"><button class="btn btn-primary" type="button" style="background: rgb(25,25,25);"><?php echo "Invite is empty!"; ?></button></div>
                                </div>
                            </div>

                        <?php
                            break;

                        case 'missing_password':
                        ?>

                            <div class="container">
                                <div class="row">
                                    <div class="col-md-12 text-center"><button class="btn btn-primary" type="button" style="background: rgb(25,25,25);"><?php echo "Password is empty!"; ?></button></div>
                                </div>
                            </div>

                        <?php
                            break;

                        case 'success':
                        ?>

                            <div class="container">
                                <div class="row">
                                    <div class="col-md-12 text-center"><button class="btn btn-primary" type="button" style="background: rgb(25,25,25);"><?php echo "Success! Redirecting..."; ?></button></div>
                                </div>
                            </div>

                        <?php

                            header('refresh:2; url=login.php');

                            break;

                        default:
                        ?>



                <?php
                            break;
                    }
                }

                function register($conn, $username, $password, $invite)
                {
                    $account_query = $conn->query('SELECT * FROM users WHERE username=?', [$username]);
                    $invite_query = $conn->query('SELECT * FROM invites WHERE invite=?', [$invite]);
                    $account_data = mysqli_fetch_assoc($account_query);
                    $invite_data = mysqli_fetch_assoc($invite_query);

                    if (empty($username)) {
                        return 'missing_username';
                    }
                    if (empty($password)) {
                        return 'missing_password';
                    }
                    if (empty($invite)) {
                        return 'missing_invite';
                    }

                    if ($username == "API") {
                        return 'forbidden_username';
                    }

                    if (strlen($password) < 4) {
                        return 'password_short';
                    }

                    if (strlen($username) < 4) {
                        return 'username_short';
                    }

                    if (mysqli_num_rows($account_query) != 0) {
                        return 'user_exists';
                    }

                    if (mysqli_num_rows($invite_query) == 0) {
                        return 'invite_invalid';
                    }

                    if ($invite_data['isUsed'] == '1') {
                        return 'invite_used';
                    }
                    if (!isset($_SESSION)) {
                        session_start();
                    }

                    $account_query = $conn->query('SELECT * FROM users WHERE username=?', [$username]);
                    $conn->query("UPDATE `invites` SET `isUsed` = '1' WHERE `invites`.`invite` = '". $invite . "'");
                    $conn->query("INSERT INTO `users`(`username`,`invitedBy`,`password`,`ip`,`uploadKey`)VALUES('" . $username . "', '" . $invite_data['owner'] . "', '" . password_hash($password, PASSWORD_BCRYPT) . "', '" . getip() . "', '" . genstring(15)  . "')");
                    $conn->query("INSERT INTO `embeds`(`username`)VALUES('" . $username . "')");
                    $conn->query("INSERT INTO `domainselector`(`username`) VALUES ('" . $username . "')");

                    return 'success';
                    
                }
                ?>

                <br>
                <form method="POST" style="background: rgb(30,30,30);">
                    <h2 class="visually-hidden">Login Form</h2>
                    <div class="illustration"><i class="icon ion-log-in" style="border-color: rgb(15,111,255);color: rgb(71,99,244);"></i></div>
                    <div class="mb-3"><input class="form-control" type="username" name="username" placeholder="username" style="filter: contrast(100%);"></div>
                    <div class="mb-3"><input class="form-control" type="password" name="password" placeholder="Password"></div>
                    <div class="mb-3"><input class="form-control" type="text" name="invite" placeholder="invite"></div>
                    <div class="mb-3"><button class="btn btn-primary d-block w-100" type="submit" style="color: rgb(0,0,0);background: rgb(71,99,244);">Register</button>
                </form>
                <br>
                <p>Already have an account? <a href="login.php">Login</a>
            </section>
            <script src="assets/bootstrap/js/bootstrap.min.js"></script>
        </body>

        </html>