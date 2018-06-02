<?php

namespace GraphQL\Extension;

use GraphQL\Type\Definition\ResolveInfo;

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
        $this->microStart = microtime(true);
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

    public function willResolveField($id, $source, $args, $context, ResolveInfo $info)
    {
        $this->fieldResolvingStart[$id] = microtime(true);
    }

    public function didResolveField($id, $source, $args, $context, ResolveInfo $info)
    {
        $start = $this->fieldResolvingStart[$id];

        $this->resolverCalls[] = [
            "path" => $info->path,
            "parentType" => $info->parentType->name,
            "fieldName" => $info->fieldName,
            "returnType" => $info->returnType->name,
            "startOffset" => (microtime(true) - $this->microStart) * 1000000000,
            "duration" => (microtime(true) - $start) * 1000000000,
        ];
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
                "duration" => (microtime(true) - $this->microStart) * 1000000000,
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
