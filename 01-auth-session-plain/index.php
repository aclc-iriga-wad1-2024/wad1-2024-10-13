<?php
/**
 * Auth on Session using Plain Password on Database [Example 1]
 * index: Home page
 */

// initialize session
session_start();


// set database connection
require_once 'config/database.php';
if(!isset($conn)) exit();

// initialize global data
$view          = 'home';
$title         = 'Home';
$user_mode     = '';
$selected_user = 0;
$inputted_user = '';
$message       = '';
$error         = [
    'shoutout' => ''
];

// helpers
require_once 'helpers/get-user-data.php';
if(!isset($user))
    exit();
require_once 'helpers/get-all-users.php';
if(!isset($users))
    exit();


// process new shout-out submission
if(isset($_POST['shout-out']) && isset($_SESSION['example1_user_id']))
{
    // get submitted shout-out
    $user_mode     = $_POST['user_mode'];
    $selected_user = isset($_POST['user_selected']) ? $_POST['user_selected'] : null;
    $inputted_user = trim($_POST['user_inputted']);
    $message       = trim($_POST['message']);

    // validate selected user
    if($user_mode == 'selected' && $selected_user == null) {
        $error['shoutout'] = 'Invalid selected user.';
    }
    // validate inputted user
    else if($user_mode == 'inputted' && $inputted_user == '') {
        $error['shoutout'] = 'Invalid inputted user.';
    }
    // validate message
    else if($message == '') {
        $error['shoutout'] = 'Empty shout-out message.';
    }

    // if there's no $error, insert new shout-out
    if($error['shoutout'] == '') {
        $stmt = $conn->prepare("INSERT INTO `shoutouts`(`user_id`, `user_mode`, `selected_user`, `inputted_user`, `message`) VALUES(?, ?, ?, ?, ?)");
        $stmt->bind_param("isiss", $user['id'], $user_mode, $selected_user, $inputted_user, $message);
        $stmt->execute();
        // if insert is successful, redirect again to homepage to avoid resubmission on refresh
        if($stmt->affected_rows > 0) {
            header('location: index.php');
        }
    }
}

// process new shout-out deletion
if(isset($_POST['delete-shout-out']) && isset($_SESSION['example1_user_id']))
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
        // if delete is successful, redirect again to homepage to avoid resubmission on refresh
        if($stmt->affected_rows > 0) {
            header('location: index.php');
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
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="m-0"><?= $title ?></h2>
    </div>

    <!-- shout-out form -->
    <?php if(isset($_SESSION['example1_user_id'])) { ?>
        <div class="card mb-3">
            <div class="card-body">
                <form method="POST" action="index.php">
                    <div class="row">
                        <!-- form header -->
                        <div class="col-12">
                            <p class="mb-1"><i class="fas fa-fw fa-megaphone"></i> Write a shout-out to:</p>
                        </div>

                        <!-- user select control -->
                        <div class="col-md-3 mb-2">
                            <div class="d-flex align-items-center gap-1">
                                <input type="radio" name="user_mode" id="user_selected" value="selected" checked>
                                <label for="user_selected" class="form-label m-0">Select user:</label>
                            </div>
                            <select name="user_selected" class="form-control">
                                <option value="" disabled selected></option>
                                <?php
                                foreach ($users as $u) {
                                    if($u['id'] != $_SESSION['example1_user_id']) { // except the session owner
                                ?>
                                        <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['firstname']) ?> <?= htmlspecialchars($u['lastname']) ?></option>
                                <?php
                                    }
                                }
                                ?>
                            </select>
                        </div>

                        <!-- user text control -->
                        <div class="col-md-3 mb-2">
                            <div class="d-flex align-items-center gap-1">
                                <input type="radio" name="user_mode" id="user_inputted" value="inputted">
                                <label for="user_inputted" class="form-label m-0">Or input user:</label>
                            </div>
                            <input type="text" name="user_inputted" class="form-control">
                        </div>

                        <!-- message and submit button -->
                        <div class="col-md-6 mb-2 pt-2 pt-md-0">
                            <div class="d-none d-md-flex align-items-center gap-1">
                                <label for="message">&nbsp;</label>
                            </div>
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
        </div>
    <?php } ?>

    <!-- shout-out list -->
    <div>
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
                                        if($shout_out['user_id'] == $user['id'] && isset($_SESSION['example1_user_id'])) {
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
                                            if($shout_out['selected_user'] == $user['id'] && isset($_SESSION['example1_user_id'])) {
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
                            <form method="POST" action="index.php" onsubmit="return confirm('Are you sure you want to delete this shout-out?');">
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
</main>


<!-- html bottom -->
<?php require_once 'partials/html-2-bot.php'; ?>