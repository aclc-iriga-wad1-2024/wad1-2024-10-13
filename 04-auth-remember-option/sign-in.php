<?php
/**
 * Auth with Remember Option using Hashed Password on Database [Example 4]
 * sign-in: Sign in page
 */

// initialize session
session_start();

// remember data
require_once __DIR__ . '/helpers/remember_data.php';
remember_data();

// check for session and redirect to homepage if 'example4_username' and 'example4_password' session already exists
if(isset($_SESSION['example4_username']) && isset($_SESSION['example4_password']))
{
    header('location: index.php');
    exit();
}
// otherwise, continue with the rest of this page:


// set database connection
require_once __DIR__ . '/config/database.php';
if(!isset($conn)) exit();

// initialize global data
$view     = 'sign-in';
$title    = 'Sign in';
$username = '';
$error    = '';

// process sign-in request when the form is submitted
if(isset($_POST['sign-in']))
{
    // get sign-in credentials
    $username = $_POST['username'];
    $password = $_POST['password'];
    $remember = isset($_POST['remember']);

    // query for the user with the requested $username which could be an email or a username
    $stmt = $conn->prepare("SELECT * FROM `users` WHERE `email` = ? OR `username` = ?");
    $stmt->bind_param("ss", $username, $username);
    $stmt->execute();
    $result = $stmt->get_result();

    // if no user is found, set the error message
    if($result->num_rows <= 0) {
        $error = 'Invalid credentials.';
    }
    // if a user is found, verify the entered password against the stored hashed password
    else {
        $row = $result->fetch_assoc();

        // if verification is successful, store the username and hashed password in session and redirect to homepage
        if(password_verify($password, $row['password'])) {
            $_SESSION['example4_username'] = $row['username'];
            $_SESSION['example4_password'] = $row['password'];

            // if user wants to keep signed in, also store the username and hashed password in as cookies
            if($remember) {
                $cookie_expiration = time() + (7 * 24 * 60 * 60); // 7 days
                setcookie('example4_username', $row['username'], $cookie_expiration);
                setcookie('example4_password', $row['password'], $cookie_expiration);
            }
            // otherwise, remove any remembered data if still available
            else if(isset($_COOKIE['example4_username']) || isset($_COOKIE['example4_password'])) {
                $cookie_expiration = time() - 3600; // 1 hour ago
                setcookie('example4_username', '', $cookie_expiration);
                setcookie('example4_password', '', $cookie_expiration);
            }

            header('location: index.php');
            exit();
        }

        // otherwise, set the error message
        else {
            $error = 'Invalid credentials.';
        }
    }
}
?>

<!-- html top -->
<?php require_once __DIR__ . '/partials/html-1-top.php'; ?>
<!-- navbar -->
<?php require_once __DIR__ . '/partials/navbar.php'; ?>


<!-- main content -->
<main class="container pt-3">
    <!-- content header -->
    <div class="d-flex justify-content-between align-items-center">
        <h2 class="m-0"><?= $title ?></h2>
    </div>
    <p>
        Join the community of positive vibes!
        Sign in to post your shout-outs and recognize the amazing people.
    </p>

    <!-- sign-in form -->
    <div class="row pt-3">
        <div class="col-md-6 offset-md-3">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="sign-in.php">
                        <!-- email or username -->
                        <div class="mb-3">
                            <label for="username" class="form-label">Email address or Username</label>
                            <input type="text" class="form-control" id="username" name="username" value="<?= htmlspecialchars($username) ?>" required>
                        </div>

                        <!-- password -->
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>

                        <!-- remember me -->
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" name="remember" id="remember">
                            <label class="form-check-label" for="remember">Remember me <span data-bs-toggle="tooltip" data-bs-title="Keep me signed in on this device." data-bs-placement="right"><i class="fas fa-fw fa-question-circle opacity-25"></i></span></label>
                        </div>

                        <!-- error message (if there's any) -->
                        <?php if($error != '') { ?>
                            <p class="text-danger"><i class="fas fa-fw fa-exclamation-circle"></i> <?= $error ?></p>
                        <?php } ?>

                        <!-- sign-in button -->
                        <button type="submit" name="sign-in" class="btn btn-dark">Sign in</button>

                        <!-- sign-up link -->
                        <div class="mt-2 d-flex justify-content-end">
                            <small>No account yet? <a href="sign-up.php" class="text-decoration-none">Sign up</a></small>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>


<!-- html bottom -->
<?php require_once __DIR__ . '/partials/html-2-bot.php'; ?>