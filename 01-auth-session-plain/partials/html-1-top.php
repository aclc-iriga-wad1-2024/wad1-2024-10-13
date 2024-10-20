<?php if(!isset($view) && !isset($title)) exit(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="dist/bootstrap-5.2.3/css/bootstrap.min.css">
    <link rel="stylesheet" href="dist/font-awesome-pro-6.2.1/css/all.min.css">
    <title><?= $title ?> - Shout-out</title>
    <style>
        .user-avatar {
            display: inline-flex;
            justify-content: center;
            align-items: center;
            border-radius: 50%;
            overflow: hidden;
            width: 48px;
            height: 48px;
            background-color: #fff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }
        .user-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .user-avatar-lg {
            width: 64px;
            height: 64px;
        }
        .user-avatar-xl {
            width: 96px;
            height: 96px;
        }
        .user-avatar-sm {
            width: 32px;
            height: 32px;
        }
        .user-avatar-xs {
            width: 24px;
            height: 24px;
        }
    </style>
</head>
<body class="bg-light pt-5 pb-3">
    <div class="pt-2">