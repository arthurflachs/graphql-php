<?php

namespace GraphQL\Extension\TracingExtension;

class Timer
{
    protected $startSeconds;
    protected $startNano;

    public function __construct()
    {
        $time = explode(' ', microtime());
        $this->startSeconds = $time[1];
        $this->startNano += round($time[0] * 1000000000);
    }

    public function getEllapsedTime()
    {
        $time = explode(' ', microtime());

        $ellapsedSeconds = $time[1] - $this->startSeconds;
        $ellapsedNano = $time[0] * 1000000000 - $this->startNano;

        return $ellapsedSeconds * 1000000000 + $ellapsedNano;
    }
}
