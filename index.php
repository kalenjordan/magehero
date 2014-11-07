<?php

// ini_set('display_errors',1);
// ini_set('display_startup_errors',1);
// error_reporting(-1);

date_default_timezone_set('UTC');

require_once(dirname(__FILE__) . '/vendor/autoload.php');
session_start();

try {
    Toro::serve(array(
        "/"                                 => "\Controller\Index",
        "/available"                        => "\Controller\Available",
        "/login"                            => "\Controller\Login",
        "/logout"                           => "\Controller\Logout",
        "/posts"                            => "\Controller\PostList",
        "/posts/new"                        => "\Controller\PostNew",
        "/posts/:number"                    => "\Controller\Post",
        "/posts/:number/edit"               => "\Controller\PostEdit",
        "/posts/:number/notify-comment"     => "\Controller\PostCommentNotify",
        "/profile"                          => "\Controller\Profile",
        "/user/:number/upvote"              => "\Controller\UserUpvote",
        "/:string/posts"                    => "\Controller\UserPosts",
        "/(.*)"                             => "\Controller\UserProfile",
        "/map"                              => "\Controller\Map",
        "/map/users"                        => "\Controller\MapUsers",
        "/feed"                             => "\Controller\Feed"
    ));
} catch (Exception $e) {
    mail("kalen@magemail.co", "MageHero Error", $e->getTraceAsString());
    die("Uh-oh.  Something's not right.  Heroes have been deployed to fix it.");
}