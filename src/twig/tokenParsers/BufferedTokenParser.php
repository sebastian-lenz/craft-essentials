<?php

namespace lenz\craft\essentials\twig\tokenParsers;

use lenz\craft\essentials\twig\nodes\BufferedNode;
use Twig\Token;
use Twig\TokenParser\AbstractTokenParser;

/**
 * Class BufferedTokenParser
 */
class BufferedTokenParser extends AbstractTokenParser
{
  /**
   * @inheritdoc
   */
  public function parse(Token $token): BufferedNode {
    $lineno = $token->getLine();
    $stream = $this->parser->getStream();
    $stream->expect(Token::BLOCK_END_TYPE);

    $values = $this->parser->subparse([$this, 'decideBlockEnd'], true);
    $stream->expect(Token::BLOCK_END_TYPE);

    return new BufferedNode($values, $lineno);
  }

  /**
   * @param Token $token
   * @return bool
   */
  public function decideBlockEnd(Token $token): bool {
    return $token->test('endbuffered');
  }

  /**
   * @inheritDoc
   */
  public function getTag(): string {
    return 'buffered';
  }
}
