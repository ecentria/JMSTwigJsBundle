<?php

namespace JMS\TwigJsBundle\Tests\TwigJs\Compiler;

use PHPUnit\Framework\TestCase;
use Twig\Environment;
use Twig\Loader\ArrayLoader;
use Twig\Node\ModuleNode;
use Twig\Source;
use TwigJs\JsCompiler;

abstract class BaseTestCase extends TestCase
{
    protected $env;
    protected $compiler;

    protected function setUp(): void
    {
        $this->env = $env = new Environment(new ArrayLoader([]));
        $env->setCompiler($this->compiler = new JsCompiler($env));
    }

    protected function compile($source, $name): string
    {
        return $this->env->compileSource(new Source($source, $name));
    }

    protected function getNodes($source, $name = null): ModuleNode
    {
        return $this->env->parse($this->env->tokenize($source, $name));
    }
}