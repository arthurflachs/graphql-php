<?php

namespace GraphQL\Extension;

use GraphQL\Type\Definition\ResolveInfo;

interface ExtensionInterface
{
    public function requestDidStart();
    public function parsingDidStart();
    public function parsingDidEnd();
    public function validationDidStart();
    public function validationDidEnd();
    public function executionDidStart();
    public function willResolveField($id, $source, $args, $context, ResolveInfo $info);
    public function didResolveField($id, $source, $args, $context, ResolveInfo $info);
    public function executionDidEnd();
    public function requestDidEnd();
    public function format();
}
