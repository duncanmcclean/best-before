<?php

namespace DuncanMcClean\BestBefore;

use PhpParser\Node;
use PhpParser\Node\Attribute;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\NodeVisitorAbstract;

final class BestBeforeVisitor extends NodeVisitorAbstract
{
    /** @var array<string> */
    private array $classNames = [];

    /** @var array<ExpiringCode> */
    private array $expiringCode = [];

    public function __construct(private readonly string $file) {}

    public function enterNode(Node $node): null
    {
        if ($node instanceof ClassLike) {
            $this->classNames[] = $node->namespacedName?->toString() ?? 'class@anonymous';

            $this->collect($node, end($this->classNames));
        }

        if ($node instanceof ClassMethod) {
            $this->collect($node, end($this->classNames).'::'.$node->name->toString().'()');
        }

        return null;
    }

    private function collect(ClassLike|ClassMethod $node, string $name): void
    {
        foreach ($node->attrGroups as $attributeGroup) {
            foreach ($attributeGroup->attrs as $attribute) {
                if ($attribute->name->toString() !== BestBefore::class) {
                    continue;
                }

                $this->expiringCode[] = new ExpiringCode(
                    name: $name,
                    file: $this->file,
                    line: $attribute->getStartLine(),
                    date: $this->argument($attribute, 'date', position: 0),
                    description: $this->argument($attribute, 'description', position: 1),
                );
            }
        }
    }

    private function argument(Attribute $attribute, string $name, int $position): ?string
    {
        foreach ($attribute->args as $index => $argument) {
            $matchesName = $argument->name?->toString() === $name;
            $matchesPosition = $argument->name === null && $index === $position;

            if ($matchesName || $matchesPosition) {
                return $argument->value instanceof String_ ? $argument->value->value : null;
            }
        }

        return null;
    }

    public function leaveNode(Node $node): null
    {
        if ($node instanceof ClassLike) {
            array_pop($this->classNames);
        }

        return null;
    }

    /**
     * @return array<ExpiringCode>
     */
    public function expiringCode(): array
    {
        return $this->expiringCode;
    }
}
