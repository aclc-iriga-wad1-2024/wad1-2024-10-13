<?php
/**
 * Auth on cookie using Hashed Password on Database [Example 3]
 * sign-up: Sign up page
 */

// check for cookie and redirect to homepage if 'example3_username' and 'example3_password' cookie already exists
if(isset($_COOKIE['example3_username']) && isset($_COOKIE['example3_password']))
{
    header('location: index.php');
    exit();
}
// otherwise, continue with the rest of this page:


// initialize database connection
require_once 'config/database.php';
if(!isset($conn)) exit();

// initialize global data
$view      = 'sign-up';
$title     = 'Sign up';
$email     = '';
$username  = '';
$firstname = '';
$lastname  = '';
$error     = '';

// process sign-up request when the form is submitted
if(isset($_POST['sign-up']))
{
    // get sign-up data
    $email     = trim($_POST['email']);     // trim() strips beginning and end whitespaces
    $username  = trim($_POST['username']);
    $firstname = trim($_POST['firstname']);
    $lastname  = trim($_POST['lastname']);
    $password1 = $_POST['password1'];
    $password2 = $_POST['password2'];

    // validate sign-up data: no fields should be empty
    if(empty($email) || empty($username) || empty($firstname) || empty($lastname) || empty($password1)) {
        $error = 'All fields are required.';
    }
    // validate password: should be at least 6 characters
    else if(strlen($password1) < 6) {
        $error = 'Password should be at least 6 characters.';
    }
    // validate password: $password1 and $password2 should match
    else if($password1 != $password2) {
        $error = 'Passwords should match.';
    }
    // validate email
    else if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email address.';
    }
    // validate email and username: should be unique (query database)
    else {
        // validate email
        $stmt = $conn->prepare("SELECT * FROM `users` WHERE `email` = ? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        if($result->num_rows > 0) {
            $error = 'Email address is already taken.';
        }
        else {
            // validate username
            $stmt = $conn->prepare("SELECT * FROM `users` WHERE `username` = ? LIMIT 1");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            if($result->num_rows > 0) {
                $error = 'Username is already taken.';
            }
        }
    }

    // if there's no $error, insert new user
    if($error == '') {
        $password_hashed = password_hash($password1, PASSWORD_DEFAULT); // hash the entered password
        $stmt = $conn->prepare("INSERT INTO `users`(`email`, `username`, `firstname`, `lastname`, `password`) VALUES(?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $email, $username, $firstname, $lastname, $password_hashed);
        $stmt->execute();
        // if insert is successful, sign-in the user using username and hashed password and redirect to homepage
        if($stmt->affected_rows > 0) {
            $cookie_expiration = time() + (7 * 24 * 60 * 60); // 7 days
            setcookie('example3_username', $username       , $cookie_expiration);
            setcookie('example3_password', $password_hashed, $cookie_expiration);
            header('location: index.php');
        }
        // otherwise, set unknown error
        else {
            $error = 'Something went wrong. Please try again.';
        }
    }
}
?>

<!-- html top -->
<?php require_once 'partials/html-1-top.php'; ?>
<!-- navbar -->
<?php require_once 'partials/navbar.php'; ?>


<!-- main content -->
<main class="container pt-3">
    <!-- content header -->
    <div class="d-flex justify-content-between align-items-center">
        <h2 class="m-0"><?= $title ?></h2>
    </div>
    <p>
        Become part of our uplifting community!
        Sign up to share your shout-outs and celebrate remarkable individuals.
    </p>

    <!-- sign-up form -->
    <div class="row pt-3">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="sign-up.php">
                        <div class="mb-3">
                            <div class="row">
                                <!-- email -->
                                <div class="col-md-6">
                                    <label for="email" class="form-label">Email address</label>
                                    <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($email) ?>" required>
                                </div>

                                <!-- username -->
                                <div class="col-md-6">
                                    <label for="username" class="form-label">Username</label>
                                    <input type="text" class="form-control" id="username" name="username" value="<?= htmlspecialchars($username) ?>" required>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="row">
                                <!-- firstname -->
                                <div class="col-md-6">
                                    <label for="firstname" class="form-label">First name</label>
                                    <input type="text" class="form-control" id="firstname" name="firstname" value="<?= htmlspecialchars($firstname) ?>" required>
                                </div>

                                <!-- lastname -->
                                <div class="col-md-6">
                                    <label for="lastname" class="form-label">Last name</label>
                                    <input type="text" class="form-control" id="lastname" name="lastname" value="<?= htmlspecialchars($lastname) ?>" required>
                                </div>
                            </div>
                        </div>

                        <!-- password -->
                        <div class="mb-3">
                            <label for="password1" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password1" name="password1" required>
                        </div>

                        <!-- confirm password -->
                        <div class="mb-3">
                            <label for="password2" class="form-label">Confirm Password</label>
                            <input type="password" class="form-control" id="password2" name="password2" required>
                        </div>

                        <!-- error message (if there's any) -->
                        <?php if($error != '') { ?>
                            <p class="text-danger"><i class="fas fa-fw fa-exclamation-circle"></i> <?= $error ?></p>
                        <?php } ?>

                        <!-- sign-up button -->
                        <button type="submit" name="sign-up" class="btn btn-dark">Sign up</button>

                        <!-- sign-in link -->
                        <div class="mt-2 d-flex justify-content-end">
                            <small>Already have an account? <a href="sign-in.php" class="text-decoration-none">Sign in</a></small>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>


<!-- html bottom -->
<?php require_once 'partials/html-2-bot.php'; ?>