<?php

namespace lenz\craft\essentials\twig\nodes;

use Twig\Compiler;
use Twig\Node\Node;

/**
 * Class BufferedNode
 */
class BufferedNode extends Node
{
  /**
   * @param Node $values
   * @param int $lineno
   */
  public function __construct(Node $values, int $lineno) {
    parent::__construct(['values' => $values], [], $lineno);
  }

  /**
   * @param Compiler $compiler
   * @return void
   */
  public function compile(Compiler $compiler): void {
    $compiler->addDebugInfo($this);

    $compiler->write("ob_start();\n");
    $compiler->subcompile($this->getNode('values'));
    $compiler->write("yield ob_get_clean();\n");
  }
}
