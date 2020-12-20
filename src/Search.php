<?php


namespace App;


use App\Entity\Message;

class Search
{
    /**
     * @var string
     */
    public  $string;

    /**
     * @var Message[]
     */
    public $messages = [];
}