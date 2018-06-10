<?php

namespace GraphQL\Extension;

use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Extension\TracingExtension\Timer;

class TracingExtension extends AbstractExtension
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
