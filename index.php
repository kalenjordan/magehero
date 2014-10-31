<?php

require_once(dirname(__FILE__) . '/vendor/autoload.php');
session_start();

Toro::serve(array(
     "/"                        => "Controller_Index",
     "/available"               => "Controller_Available",
     "/login"                   => "Controller_Login",
     "/logout"                  => "Controller_Logout",
     "/profile"                 => "Controller_Profile",
     "/user/:number/upvote"     => "Controller_UserUpvote",
     "/(.*)"                    => "Controller_UserProfile",
));
