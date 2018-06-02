<?php
namespace GraphQL\Executor;

use GraphQL\Error\Error;
use GraphQL\Language\AST\FragmentDefinitionNode;
use GraphQL\Language\AST\OperationDefinitionNode;
use GraphQL\Type\Schema;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Extension\ExtensionInterface;

/**
 * Data that must be available at all points during query execution.
 *
 * Namely, schema of the type system that is currently executing,
 * and the fragments defined in the query document
 *
 * @internal
 */
class ExecutionContext
{
    /**
     * @var Schema
     */
    public $schema;

    /**
     * @var FragmentDefinitionNode[]
     */
    public $fragments;

    /**
     * @var mixed
     */
    public $rootValue;

    /**
     * @var mixed
     */
    public $contextValue;

    /**
     * @var OperationDefinitionNode
     */
    public $operation;

    /**
     * @var array
     */
    public $variableValues;

    /**
     * @var callable
     */
    public $fieldResolver;

    /**
     * @var array
     */
    public $errors;

    /**
     * @var array
     */
    public $extensionsResult;

    /**
     * @var ExtensionInterface[]
     */
    public $extensions = [];

    public function __construct(
        $schema,
        $fragments,
        $root,
        $contextValue,
        $operation,
        $variables,
        $errors,
        $fieldResolver,
        $promiseAdapter,
        $extensions = []
    )
    {
        $this->schema = $schema;
        $this->fragments = $fragments;
        $this->rootValue = $root;
        $this->contextValue = $contextValue;
        $this->operation = $operation;
        $this->variableValues = $variables;
        $this->errors = $errors ?: [];
        $this->fieldResolver = $fieldResolver;
        $this->promises = $promiseAdapter;
        $this->extensions = $extensions;
    }

    public function addError(Error $error)
    {
        $this->errors[] = $error;
        return $this;
    }

    public function requestDidStart()
    {
        foreach ($this->extensions as $extension) {
            $extension->requestDidStart();
        }
    }

    public function parsingDidStart()
    {
        foreach ($this->extensions as $extension) {
            $extension->parsingDidStart();
        }
    }

    public function parsingDidEnd()
    {
        foreach ($this->extensions as $extension) {
            $extension->parsingDidEnd();
        }
    }

    public function validationDidStart()
    {
        foreach ($this->extensions as $extension) {
            $extension->validationDidStart();
        }
    }

    public function validationDidEnd()
    {
        foreach ($this->extensions as $extension) {
            $extension->validationDidEnd();
        }
    }

    public function executionDidStart()
    {
        foreach ($this->extensions as $extension) {
            $extension->executionDidStart();
        }
    }

    public function willResolveField($id, $source, $args, $context, ResolveInfo $info = null)
    {
        foreach ($this->extensions as $extension) {
            $extension->willResolveField($id, $source, $args, $context, $info);
        }
    }

    public function didResolveField($id, $source, $args, $context, ResolveInfo $info)
    {
        foreach ($this->extensions as $extension) {
            $extension->didResolveField($id, $source, $args, $context, $info);
        }
    }

    public function executionDidEnd()
    {
        foreach ($this->extensions as $extension) {
            $extension->executionDidEnd();
        }
    }

    public function requestDidEnd()
    {
        foreach ($this->extensions as $extension) {
            $extension->requestDidEnd();
        }

        $this->extensionsResult = [];
        foreach ($this->extensions as $extension) {
            $this->extensionsResult = array_merge($this->extensions, $extension->format());
        }
    }
}
