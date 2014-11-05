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
    "/"                        => "Controller_Index",
    "/available"               => "Controller_Available",
    "/login"                   => "Controller_Login",
    "/logout"                  => "Controller_Logout",
    "/posts"                   => "Controller_PostList",
    "/posts/new"               => "Controller_PostNew",
    "/posts/:number"           => "Controller_Post",
    "/posts/:number/edit"      => "Controller_PostEdit",
    "/profile"                 => "Controller_Profile",
    "/user/:number/upvote"     => "Controller_UserUpvote",
    "/:string/posts"           => "Controller_UserPosts",
    "/(.*)"                    => "Controller_UserProfile",
    "/map"                     => "Controller_Map",
    "/map/users"               => "Controller_MapUsers",
    "/feed"                    => "Controller_Feed",
));
