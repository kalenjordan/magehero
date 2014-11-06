<?php

namespace Controller;

class Logout
{
    public function get()
    {
        session_destroy();
        header("location: /");
    }
}