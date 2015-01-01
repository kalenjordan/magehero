<?php

require_once(dirname(__FILE__) . '/vendor/autoload.php');
session_start();

try {
    Toro::serve(array(
        "/"                                 => "Controller_PostList",
        "/available"                        => "Controller_Available",
        "/login"                            => "Controller_Login",
        "/logout"                           => "Controller_Logout",
        "/posts"                            => "Controller_PostList",
        "/posts/new"                        => "Controller_PostNew",
        "/posts/:number/edit"               => "Controller_PostEdit",
        "/posts/:number/notify-comment"     => "Controller_PostCommentNotify",
        "/posts/:number"                    => "Controller_Post",
        "/posts/:number/:alpha"             => "Controller_Post",
        "/profile"                          => "Controller_Profile",
        "/users"                            => "Controller_UserList",
        "/user/:number/upvote"              => "Controller_UserUpvote",
        "/:string/posts"                    => "Controller_UserPosts",
        "/(.*)"                             => "Controller_UserProfile",
        "/map"                              => "Controller_Map",
        "/map/users"                        => "Controller_MapUsers",
        "/feed"                             => "Controller_Feed",
    ));
} catch (Exception $e) {
    mail("kalen@magemail.co", "MageHero Error", $e->getTraceAsString());
    die("Uh-oh.  Something's not right.  Heroes have been deployed to fix it.");
}
