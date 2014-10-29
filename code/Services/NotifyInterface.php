<?php
interface Services_NotifyInterface {
    /**
     * Send Message to User
     * @param  Array $to Up voted user.
     * @param  Array $from Up voted by.
     * @param  String $message 
     * @return boolean 
     */

    public function send($to, $from, $message);
}