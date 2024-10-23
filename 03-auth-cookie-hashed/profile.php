<?php
/**
 * Auth on Cookie using Hashed Password on Database [Example 3]
 * profile: User profile page
 */

// set database connection
require_once 'config/database.php';
if(!isset($conn)) exit();

// identify profile
$profile = [];
$stmt    = null;
if(isset($_GET['id'])) {
    // passed id param
    $stmt = $conn->prepare("SELECT * FROM `users` WHERE `id` = ?");
    $stmt->bind_param("i", $_GET['id']);
}
else if(isset($_COOKIE['example3_username']) && isset($_COOKIE['example3_password'])) {
    // stored in cookie
    $stmt = $conn->prepare("SELECT * FROM `users` WHERE `username` = ? AND `password` = ?");
    $stmt->bind_param("ss", $_COOKIE['example3_username'], $_COOKIE['example3_password']);
}
if($stmt != null) {
    $stmt->execute();
    $result = $stmt->get_result();
    while($row = $result->fetch_assoc()) {
        $profile = [
            'id'        => intval($row['id']),
            'firstname' => $row['firstname'],
            'lastname'  => $row['lastname'],
            'email'     => $row['email'],
            'username'  => $row['username'],
            'password'  => $row['password'],
            'avatar'    => file_exists('uploads/avatars/' . $row['avatar']) ? $row['avatar'] : 'no-avatar.jpg'
        ];
    }
}

// redirect to homepage if $profile is not found
if(empty($profile)) {
    header('location: index.php');
    exit();
}
// otherwise, continue with this page:

// identify passed tab
$tab = 'received';
if(isset($_GET['tab'])) {
    if(in_array($_GET['tab'], ['received', 'sent', 'settings']))
        $tab = $_GET['tab'];
}
// only authenticated user should have access to 'settings' tab
if($tab == 'settings' && (!isset($_COOKIE['example3_username']) || !isset($_COOKIE['example3_password']) || $profile['username'] !== $_COOKIE['example3_username'] || $profile['password'] !== $_COOKIE['example3_password'])) {
    $tab = 'received';
}

// initialize global data
$view    = 'profile';
$title   = htmlspecialchars($profile['firstname']) . ' ' . htmlspecialchars($profile['lastname']) . ' (' . ucfirst($tab) . ')';
$message = '';
$error   = [
    'shoutout' => '',
    'personal' => '',
    'account'  => '',
    'avatar'   => '',
    'password' => ''
];

// helpers
require_once 'helpers/get_user_data.php';
$user = get_user_data();
require_once 'helpers/get_all_users.php';
$users = get_all_users();


// process new shout-out submission only if there's a signed-in user and this profile is for someone else
if(isset($_POST['shout-out']) && isset($_COOKIE['example3_username']) && isset($_COOKIE['example3_password']) && $profile['id'] != $user['id'])
{
    // get submitted shout-out
    $user_mode     = 'selected';
    $selected_user = $profile['id'];
    $inputted_user = '';
    $message       = trim($_POST['message']);

    // validate selected user
    if($message == '') {
        $error['shoutout'] = 'Empty shout-out message.';
    }

    // if there's no $error, insert new shout-out
    if($error['shoutout'] == '') {
        $stmt = $conn->prepare("INSERT INTO `shoutouts`(`user_id`, `user_mode`, `selected_user`, `inputted_user`, `message`) VALUES(?, ?, ?, ?, ?)");
        $stmt->bind_param("isiss", $user['id'], $user_mode, $selected_user, $inputted_user, $message);
        $stmt->execute();
        // if insert is successful, redirect again to them page to avoid resubmission on refresh
        if($stmt->affected_rows > 0) {
            header('location: profile.php?id=' . $profile['id']);
        }
    }
}

// process new shout-out deletion
if(isset($_POST['delete-shout-out']) && isset($_COOKIE['example3_username']) && isset($_COOKIE['example3_password']))
{
    // get submitted shout-out id
    $shout_out_id = $_POST['shout-out-id'];

    // validate shout-out: if really owned by authenticated user
    $stmt = $conn->prepare("SELECT * FROM `shoutouts` WHERE `id` = ? AND `user_id` = ?");
    $stmt->bind_param("ii", $shout_out_id, $user['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    if($result->num_rows > 0) {
        // if yes, proceed with delete
        $stmt = $conn->prepare("DELETE FROM `shoutouts` WHERE `id` = ?");
        $stmt->bind_param("i", $shout_out_id);
        $stmt->execute();
        // if delete is successful, redirect again to profile page to avoid resubmission on refresh
        if($stmt->affected_rows > 0) {
            header('location: profile.php?id=' . $profile['id'] . '&tab=' . $tab);
        }
    }
}

// process update personal information request when the form is submitted
if(isset($_POST['update-personal']))
{
    // get submitted personal data
    $firstname = trim($_POST['firstname']);
    $lastname  = trim($_POST['lastname']);

    // validate personal data: no fields should be empty
    if(empty($firstname) || empty($lastname)) {
        $error['personal'] = 'All fields are required.';
    }

    // if there's no error, update personal information
    else {
        $stmt = $conn->prepare("UPDATE `users` SET `firstname` = ?, `lastname` = ? WHERE `id` = ?");
        $stmt->bind_param("ssi", $firstname, $lastname, $user['id']);
        $stmt->execute();
        // if update is successful, redirect to self (profile.php) so that [get_user_data.php] can query the updated user again
        if($stmt->affected_rows > 0) {
            header('location: profile.php?id=' . $user['id'] . '&tab=settings');
        }
    }
}

// process update avatar request when the form is submitted
if(isset($_POST['update-avatar']))
{
    // validate submitted file
    if(!isset($_FILES['avatar'])) {
        $error['avatar'] = 'No file submitted.';
    }
    else {
        // get the submitted file
        $file = $_FILES['avatar'];

        // check for upload errors
        if($file['error'] != UPLOAD_ERR_OK) {
            $error['avatar'] = "An error occurred during the upload.";
        }

        // no upload errors
        else {
            // validate the MIME type and file extension to ensure the uploaded file is a JPEG or PNG
            $file_mime_type = $file['type'];
            $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if(!in_array($file_mime_type, ['image/jpeg', 'image/jpg', 'image/png']) || !in_array($file_extension, ['jpeg', 'jpg', 'png'])) {
                $error['avatar'] = "Invalid file type: only JPG, JPEG, and PNG files are allowed.";
            }

            // if file is valid, proceed with the upload
            else {
                // create upload directory if it does not exist
                $upload_dir = 'uploads/avatars';
                if(!is_dir($upload_dir)) {
                    mkdir($upload_dir);
                }

                // delete current user avatar if it's not 'no-avatar.jpg'
                if(!empty($user['avatar']) && $user['avatar'] !== 'no-avatar.jpg') {
                    $previous_avatar = $upload_dir . '/' . $user['avatar'];
                    if(file_exists($previous_avatar))
                        unlink($previous_avatar);
                }

                // make new filename unique
                $new_filename = $user['username'] . '-' . time() . '.' . $file_extension;

                // move and save the uploaded file
                move_uploaded_file($file['tmp_name'], $upload_dir . '/' . $new_filename);

                // update user avatar in database
                $stmt = $conn->prepare("UPDATE `users` SET `avatar` = ? WHERE `id` = ?");
                $stmt->bind_param("si", $new_filename, $user['id']);
                $stmt->execute();

                // redirect to self (profile.php) so that [get_user_data.php] can query the updated user again
                header('location: profile.php?id=' . $user['id'] . '&tab=settings');
            }
        }
    }
}

// process update account information request when the form is submitted
if(isset($_POST['update-account']))
{
    // get submitted account data
    $email    = trim($_POST['email']);
    $username = trim($_POST['username']);

    // validate account data: no fields should be empty
    if(empty($email) || empty($username)) {
        $error['account'] = 'All fields are required.';
    }
    // validate email
    else if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error['account'] = 'Invalid email address: <b><i>' . $email . '</i></b>.';
    }
    // validate email and username: should not be taken (query database)
    else {
        // validate email
        $stmt = $conn->prepare("SELECT * FROM `users` WHERE `email` = ? AND `id` != ? LIMIT 1");
        $stmt->bind_param("si", $email, $user['id']);
        $stmt->execute();
        $result = $stmt->get_result();
        if($result->num_rows > 0) {
            $error['account'] = 'Email address <b><i>' . $email . '</i></b> is already taken.';
        }
        else {
            // validate username
            $stmt = $conn->prepare("SELECT * FROM `users` WHERE `username` = ? AND `id` != ? LIMIT 1");
            $stmt->bind_param("si", $username, $user['id']);
            $stmt->execute();
            $result = $stmt->get_result();
            if($result->num_rows > 0) {
                $error['account'] = 'Username <b><i>' . $username . '</i></b> is already taken.';
            }
        }
    }

    // if there's no error, update account information
    if($error['account'] == '') {
        $stmt = $conn->prepare("UPDATE `users` SET `email` = ?, `username` = ? WHERE `id` = ?");
        $stmt->bind_param("ssi", $email, $username, $user['id']);
        $stmt->execute();
        // if update is successful, redirect to self (profile.php) so that [get_user_data.php] can query the updated user again
        if($stmt->affected_rows > 0) {
            // update username in cookie
            $cookie_expiration = time() + (7 * 24 * 60 * 60); // 7 days
            setcookie('example3_username', $username, $cookie_expiration);
            header('location: profile.php?id=' . $user['id'] . '&tab=settings');
        }
    }
}

// process update password request when the form is submitted
if(isset($_POST['update-password']))
{
    // get submitted password data
    $old_password  = $_POST['old_password'];
    $new_password1 = $_POST['new_password1'];
    $new_password2 = $_POST['new_password2'];

    // validate password data: no fields should be empty
    if(empty($old_password) || empty($new_password1)) {
        $error['password'] = 'All fields are required.';
    }
    // validate new password: should be at least 6 characters
    else if(strlen($new_password1) < 6) {
        $error['password'] = 'Password should be at least 6 characters.';
    }
    // validate new password: $new_password1 and $new_password2 should match
    else if($new_password1 != $new_password2) {
        $error['password'] = 'Passwords should match.';
    }
    // validate old password: should be correct
    else if(!password_verify($old_password, $profile['password'])) {
        $error['password'] = 'Incorrect old password.';
    }

    // if there's no error, update password
    if($error['password'] == '') {
        $hashed_password = password_hash($new_password1, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE `users` SET `password` = ? WHERE `id` = ?");
        $stmt->bind_param("si", $hashed_password, $user['id']);
        $stmt->execute();

        // update password in cookie
        setcookie('example3_password', $hashed_password, time() + (7 * 24 * 60 * 60));
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
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-end gap-2">
            <div class="user-avatar">
                <img src="uploads/avatars/<?= $profile['avatar'] ?>">
            </div>
            <h2 class="m-0"><?= htmlspecialchars($profile['firstname']) ?> <?= htmlspecialchars($profile['lastname']) ?></h2>
        </div>
        <a href="index.php" class="btn btn-outline-dark">
            <i class="fas fa-fw fa-arrow-left"></i> Home
        </a>
    </div>

    <!-- tabs -->
    <ul class="nav nav-tabs">
        <li class="nav-item">
            <a class="nav-link<?= ($tab == 'received') ? ' active' : '' ?>" href="profile.php?id=<?= $profile['id'] ?>&tab=received">Received</a>
        </li>
        <li class="nav-item">
            <a class="nav-link<?= ($tab == 'sent')     ? ' active' : '' ?>" href="profile.php?id=<?= $profile['id'] ?>&tab=sent">Sent</a>
        </li>
        <?php if($profile['id'] == $user['id']) { ?>
            <li class="nav-item">
                <a class="nav-link<?= ($tab == 'settings') ? ' active' : '' ?>" href="profile.php?id=<?= $profile['id'] ?>&tab=settings">Settings</a>
            </li>
        <?php } ?>
    </ul>
    <div class="bg-white border-top-0 border-b" style="border: 1px solid #dee2e6">
        <!-- shout-out form -->
        <?php if(isset($_COOKIE['example3_username']) && isset($_COOKIE['example3_password']) && $profile['id'] != $user['id'] && $tab == 'received') { ?>
            <div class="p-3 pb-0">
                <form method="POST" action="profile.php?id=<?= $profile['id'] ?>">
                    <div class="row">
                        <!-- form header -->
                        <div class="col-12">
                            <p class="mb-1"><i class="fas fa-fw fa-megaphone"></i> Write a shout-out to <?= htmlspecialchars($profile['firstname']) ?> <?= htmlspecialchars($profile['lastname']) ?>:</p>
                        </div>

                        <!-- message and submit button -->
                        <div class="col-md-12 mb-2 pt-2 pt-md-0">
                            <div class="input-group">
                                <input type="text" class="form-control" id="message" name="message" placeholder="Message" value="<?= $error['shoutout'] != '' ? $message : '' ?>" required>
                                <button type="submit" class="btn btn-dark" name="shout-out">Shout-out!</button>
                            </div>
                        </div>

                        <!-- error message (if there's any) -->
                        <?php if($error['shoutout'] != '') { ?>
                            <p class="text-danger m-0"><i class="fas fa-fw fa-exclamation-circle"></i> <?= $error['shoutout'] ?></p>
                        <?php } ?>
                    </div>
                </form>
            </div>
        <?php } ?>

        <?php if(in_array($tab, ['received', 'sent'])) { ?>
            <!-- shout-out list -->
            <div class="p-3">
                <?php
                require_once 'helpers/get-all-shout-outs.php';
                if(empty($shout_outs)) {
                ?>
                    <p class="text-danger">No shout-outs found.</p>
                <?php
                }
                else {
                    foreach ($shout_outs as $shout_out) {
                ?>
                        <!-- individual $shout_out -->
                        <?php
                        $is_author = $shout_out['user_id'] == $user['id'];
                        $is_to     = $shout_out['user_mode'] == 'selected' && $shout_out['selected_user'] == $user['id'];
                        ?>
                        <div class="card mb-2<?= $is_author ? ' border-dark' : '' ?><?= $is_to ? ' border-warning bg-warning' : '' ?>">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <!-- author (referenced from the $users global array using key 'u-{user_id of this shout-out}') -->
                                    <h6 class="mb-1 fw-bold">
                                        <a href="profile.php?id=<?= $shout_out['user_id'] ?>" class="text-decoration-none d-flex align-items-end gap-1">
                                            <div class="user-avatar user-avatar-xs">
                                                <img src="uploads/avatars/<?= $users['u-' . $shout_out['user_id']]['avatar'] ?>">
                                            </div>
                                            <div>
                                                <?php
                                                if($shout_out['user_id'] == $user['id'] && isset($_COOKIE['example3_username']) && isset($_COOKIE['example3_password'])) {
                                                    echo "(me) ";
                                                }
                                                ?>
                                                <?= htmlspecialchars($users['u-' . $shout_out['user_id']]['firstname']) ?>
                                                <?= htmlspecialchars($users['u-' . $shout_out['user_id']]['lastname']) ?>
                                            </div>
                                        </a>
                                    </h6>
                                    <!-- created date and time -->
                                    <small class="opacity-50"><?= $shout_out['created_at'] ?></small>
                                </div>
                                <div>
                                    <p class="m-0">
                                        <i class="fas fa-fw fa-megaphone opacity-75"></i>
                                        <i>
                                            <!-- shout-out to -->
                                            to
                                            <b>
                                                <?php
                                                // selected user (referenced from the $users global array using key 'u-{selected_user of this shout-out}') -->
                                                if($shout_out['user_mode'] == 'selected') {
                                                    echo '<a href="profile.php?id=' . $shout_out['selected_user'] . '" class="text-decoration-none">';
                                                    if($shout_out['selected_user'] == $user['id'] && isset($_COOKIE['example3_username']) && isset($_COOKIE['example3_password'])) {
                                                        echo "(me) ";
                                                    }
                                                    echo htmlspecialchars($users['u-' . $shout_out['selected_user']]['firstname']) . '&nbsp;';
                                                    echo htmlspecialchars($users['u-' . $shout_out['selected_user']]['lastname']);
                                                    echo '</a>';
                                                }
                                                // manually inputted user
                                                else if($shout_out['user_mode'] == 'inputted') {
                                                    echo htmlspecialchars($shout_out['inputted_user']);
                                                }
                                                ?>
                                            </b>
                                        </i>:
                                        <!-- shout-out message -->
                                        <?= htmlspecialchars($shout_out['message']) ?>
                                    </p>
                                </div>
                            </div>
                            <!-- delete form -->
                            <?php if($is_author) { ?>
                                <div class="card-footer d-flex justify-content-end">
                                    <form method="POST" action="profile.php?id=<?= $profile['id'] ?>&tab=<?= $tab ?>" onsubmit="return confirm('Are you sure you want to delete this shout-out?');">
                                        <input type="hidden" name="shout-out-id" value="<?= $shout_out['id'] ?>">
                                        <button type="submit" name="delete-shout-out" class="btn btn-sm btn-outline-danger"><i class="fas fa-fw fa-trash"></i></button>
                                    </form>
                                </div>
                            <?php } ?>
                        </div>
                <?php
                    }
                }
                ?>
            </div>
        <?php } else if($tab == 'settings') { ?>
            <!-- settings panel -->
            <div class="p-3 row">
                <!-- personal information -->
                <div class="col-md-6 pb-3">
                    <div class="card mt-3 h-100">
                        <div class="card-header bg-secondary text-light fw-bold">Personal Information</div>
                        <div class="card-body position-relative pb-5">
                            <form method="POST" action="profile.php?id=<?= $user['id'] ?>&tab=settings">
                                <div class="row">
                                    <!-- firstname -->
                                    <div class="col-sm-6 mb-3">
                                        <label for="firstname" class="form-label">First name</label>
                                        <input type="text" class="form-control" id="firstname" name="firstname" value="<?= htmlspecialchars($user['firstname']) ?>" required>
                                    </div>

                                    <!-- lastname -->
                                    <div class="col-sm-6 mb-3">
                                        <label for="lastname" class="form-label">Last name</label>
                                        <input type="text" class="form-control" id="lastname" name="lastname" value="<?= htmlspecialchars($user['lastname']) ?>" required>
                                    </div>
                                </div>

                                <!-- error message (if there's any) -->
                                <?php if($error['personal'] != '') { ?>
                                    <p class="text-danger"><i class="fas fa-fw fa-exclamation-circle"></i> <?= $error['personal'] ?></p>
                                <?php } ?>

                                <!-- update personal information button -->
                                <div class="position-absolute bottom-0 pe-3 pb-3" style="right: 0">
                                    <button type="submit" name="update-personal" class="btn btn-dark">Update</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- avatar -->
                <div class="col-md-6 pb-3">
                    <div class="card mt-3 h-100">
                        <div class="card-header bg-secondary text-light fw-bold">Avatar</div>
                        <div class="card-body position-relative pb-5">
                            <form method="POST" action="profile.php?id=<?= $user['id'] ?>&tab=settings" enctype="multipart/form-data">
                                <!-- current avatar display -->
                                <div class="mb-3 d-flex justify-content-start">
                                    <label for="avatar" class="user-avatar user-avatar-xl" style="cursor: pointer">
                                        <img src="uploads/avatars/<?= $user['avatar'] ?>">
                                    </label>
                                </div>

                                <!-- file input -->
                                <div class="mb-3">
                                    <input class="form-control" type="file" id="avatar" name="avatar" accept=".jpg, .jpeg, .png" required>
                                </div>

                                <!-- error message (if there's any) -->
                                <?php if($error['avatar'] != '') { ?>
                                    <p class="text-danger"><i class="fas fa-fw fa-exclamation-circle"></i> <?= $error['avatar'] ?></p>
                                <?php } ?>

                                <!-- update avatar button -->
                                <div class="position-absolute bottom-0 pe-3 pb-3" style="right: 0">
                                    <button type="submit" name="update-avatar" class="btn btn-dark">Upload</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- account information -->
                <div class="col-md-6 pb-3">
                    <div class="card mt-3 h-100">
                        <div class="card-header bg-secondary text-light fw-bold">Account Information</div>
                        <div class="card-body position-relative pb-5">
                            <form method="POST" action="profile.php?id=<?= $user['id'] ?>&tab=settings">
                                <div class="row">
                                    <!-- email -->
                                    <div class="col-sm-6 mb-3">
                                        <label for="email" class="form-label">Email address</label>
                                        <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                                    </div>

                                    <!-- username -->
                                    <div class="col-sm-6 mb-3">
                                        <label for="username" class="form-label">Username</label>
                                        <input type="text" class="form-control" id="username" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>
                                    </div>
                                </div>

                                <!-- error message (if there's any) -->
                                <?php if($error['account'] != '') { ?>
                                    <p class="text-danger"><i class="fas fa-fw fa-exclamation-circle"></i> <?= $error['account'] ?></p>
                                <?php } ?>

                                <!-- update account information button -->
                                <div class="position-absolute bottom-0 pe-3 pb-3" style="right: 0">
                                    <button type="submit" name="update-account" class="btn btn-dark">Update</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- password -->
                <div class="col-md-6 pb-3">
                    <div class="card mt-3 h-100">
                        <div class="card-header bg-secondary text-light fw-bold">Password</div>
                        <div class="card-body position-relative pb-5">
                            <?php if(isset($_POST['update-password']) && $error['password'] == '') { ?>
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <i class="fas fa-fw fa-check-circle"></i> Password successfully updated!
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            <?php } ?>
                            <form method="POST" action="profile.php?id=<?= $user['id'] ?>&tab=settings">
                                <div class="row">
                                    <!-- old password -->
                                    <div class="col-md-12 mb-3">
                                        <label for="old_password" class="form-label">Old Password</label>
                                        <input type="password" class="form-control" id="old_password" name="old_password" required>
                                    </div>

                                    <!-- new password -->
                                    <div class="col-md-6 mb-3">
                                        <label for="new_password1" class="form-label">New Password</label>
                                        <input type="password" class="form-control" id="new_password1" name="new_password1" required>
                                    </div>

                                    <!-- retype new password -->
                                    <div class="col-md-6 mb-3">
                                        <label for="new_password2" class="form-label">Confirm Password</label>
                                        <input type="password" class="form-control" id="new_password2" name="new_password2" required>
                                    </div>
                                </div>

                                <!-- error message (if there's any) -->
                                <?php if($error['password'] != '') { ?>
                                    <p class="text-danger"><i class="fas fa-fw fa-exclamation-circle"></i> <?= $error['password'] ?></p>
                                <?php } ?>

                                <!-- update password button -->
                                <div class="position-absolute bottom-0 pe-3 pb-3" style="right: 0">
                                    <button type="submit" name="update-password" class="btn btn-dark">Change Password</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php } ?>
    </div>
</main>


<!-- html bottom -->
<?php require_once 'partials/html-2-bot.php'; ?>