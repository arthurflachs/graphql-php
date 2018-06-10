<?php

namespace GraphQL\Extension;

use GraphQL\Type\Definition\ResolveInfo;

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

class TracingExtension implements ExtensionInterface
{
    protected $result;
    protected $resolverCalls = [];
    protected $fieldResolvingStart = [];
    protected $start;
    protected $microStart;
    protected $end;

    public function requestDidStart()
    {
        $t = microtime(true);
        $micro = sprintf("%06d",($t - floor($t)) * 1000000);
        $this->start = new \DateTime(date('Y-m-d H:i:s.' . $micro, $t));
        $this->monotonicStart = new Timer;
    }

    public function parsingDidStart()
    {
    }

    public function parsingDidEnd()
    {

    }

    public function validationDidStart()
    {

    }

    public function validationDidEnd()
    {

    }

    public function executionDidStart()
    {

    }

    public function willResolveField($source, $args, $context, ResolveInfo $info)
    {
        $timer = new Timer();

        return function($source, $args, $context, ResolveInfo $info) use ($timer) {
            $this->resolverCalls[] = [
                "path" => $info->path,
                "parentType" => $info->parentType->name,
                "fieldName" => $info->fieldName,
                "returnType" => $info->returnType->name,
                "startOffset" => $this->monotonicStart->getEllapsedTime(),
                "duration" => $timer->getEllapsedTime(),
            ];
        };
    }

    public function executionDidEnd()
    {

    }

    public function requestDidEnd()
    {
        $t = microtime(true);

        $micro = sprintf("%06d",($t - floor($t)) * 1000000);
        $end = new \DateTime(date('Y-m-d H:i:s.' . $micro, $t));

        $this->result = [
            "tracing" => [
                "version" => 1,
                "startTime" => $this->start->format(\DateTime::RFC3339_EXTENDED),
                "endTime" => $end->format(\DateTime::RFC3339_EXTENDED),
                "duration" => $this->monotonicStart->getEllapsedTime(),
                "execution" => [
                    "resolvers" => $this->resolverCalls,
                ]
            ]
        ];
    }

    public function format()
    {
        return $this->result;
    }
}
