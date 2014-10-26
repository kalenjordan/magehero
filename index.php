<?php

require_once(dirname(__FILE__) . '/vendor/autoload.php');
session_start();

spl_autoload_register(function($class) {
    $parts = explode("_", $class);
    $classSlashSeparated = implode('/', $parts);
    $pathToFile = "code/$classSlashSeparated.php";
    $fullPathToFile = dirname(__FILE__) . '/' . $pathToFile;

    if (file_exists($fullPathToFile)) {
        include $fullPathToFile;
    }
});

Toro::serve(array(
     "/magedevs"                        => "Controller_Index",
     "/magedevs/login"                  => "Controller_Login",
     "/magedevs/logout"                 => "Controller_Logout",
     "/magedevs/profile"                => "Controller_Profile",
     "/magedevs/user/:number/upvote"    => "Controller_UserUpvote",
));
