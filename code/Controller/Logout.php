<?php

class Controller_Logout
{
    public function get()
    {
        session_destroy();
        header("location: /magedevs/");
    }
}