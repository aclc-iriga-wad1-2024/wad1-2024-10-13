<?php if(!isset($view)) exit(); ?>
<nav class="navbar fixed-top navbar-expand-lg navbar-dark bg-dark text-white">
    <div class="container">
        <a class="navbar-brand text-warning" href="index.php">Shout-out</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse justify-content-between" id="navbarNav">
            <ul class="navbar-nav">
                <!-- menu links when user is signed-in -->
                <?php if(isset($_SESSION['example1_user_id'])) { ?>
                    <li class="nav-item">
                        <a class="nav-link<?= $view === 'home' ? ' active' : '' ?>" href="index.php">
                            <i class="fas fa-fw fa-megaphone"></i>
                            Home
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link<?= ($view === 'profile' && isset($profile) && $profile['id'] == $_SESSION['example1_user_id']) ? ' active' : '' ?>" href="profile.php">
                            <i class="fas fa-fw fa-user-circle"></i>
                            Profile
                        </a>
                    </li>
                <?php } ?>
            </ul>
            <ul class="navbar-nav">
                <?php if(!isset($_SESSION['example1_user_id'])) { ?>
                    <!-- sign-in link when a user is not signed-in -->
                    <li class="nav-item">
                        <a class="nav-link<?= $view === 'sign-in' ? ' active' : '' ?>" href="sign-in.php">Sign in</a>
                    </li>
                    <!-- sign-up link when a user is not signed-in -->
                    <li class="nav-item">
                        <a class="nav-link<?= $view === 'sign-up' ? ' active' : '' ?>" href="sign-up.php">Sign up</a>
                    </li>
                <?php } else if(isset($user)) { ?>
                    <!-- user dropdown when a user is signed-in -->
                    <li class="nav-item">
                        <div class="btn-group">
                            <button type="button" class="btn btn-link bg-dark text-light opacity-75 text-decoration-none dropdown-toggle" data-bs-toggle="dropdown">
                            <?php 
                                $stmt = $conn->prepare("SELECT `id`, `firstname`, `profile_picture` FROM `users` WHERE `id` = ?");
                                $stmt->bind_param("i", $_SESSION['example1_user_id']);
                                $stmt->execute();
                                $result = $stmt->get_result();
                                $profile = $result->fetch_assoc();
                                $profilePicture = $profile['profile_picture'];?>
                            <?php if (isset($profilePicture) && !empty($profilePicture)) { ?> 
                                <img src="<?= $profilePicture ?>" id="profile-picture" alt="" size="32" height="32" width="32" data-view-component="true" class="avatar circle" style="border-radius: 50%; overflow: hidden; object-fit: cover; width: 32px; height: 32px;">
                            <?php } else { ?>
                                <i class="fas fa-fw fa-user-circle opacity-75"></i>
                            <?php } ?>
                            <?= htmlspecialchars($profile['firstname']) ?>
                            </button>
                            <ul class="dropdown-menu">
                                <li>
                                    <!-- sign out button -->
                                    <form method="POST" action="sign-out.php" onsubmit="return confirm('Are you sure you want to sign out?');">
                                        <button type="submit" name="sign-out" class="btn btn-link btn-sm text-danger dropdown-item"><i class="fas fa-fw fa-power-off"></i> Sign out</button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </li>
                <?php } ?>
            </ul>
        </div>
    </div>
</nav>