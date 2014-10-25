<?php
/**
 * The MIT License (MIT)
 *
 * Copyright (c) 2014 Marco Muths
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace PhpDA\Parser\Visitor\Required;

use phpDocumentor\Reflection\DocBlock\Tag\MethodTag;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlock\Tag\ReturnTag;
use PhpParser\Node;
use PhpParser\NodeVisitor\NameResolver as PhpParserNameResolver;

class NameResolver extends PhpParserNameResolver
{
    const TAG_NAMES_ATTRIBUTE = '__tagNames';

    private $ignoredMethodArgumentTypes = array(
        'bool', 'boolean',
        'int', 'integer',
        'string', 'binary',
        'object', 'resource',
        'mixed', 'null',
        'float', 'double',
        'this', 'self', 'parent', 'static',
        'true', 'false',
        '=', '|'
    );

    public function enterNode(Node $node)
    {
        parent::enterNode($node);

        if ($doc = $node->getDocComment()) {
            $docBlock = new DocBlock($doc->getText());
            $tags = $docBlock->getTags();
            $tagNames = array();
            foreach ($tags as $tag) {
                if ($tag instanceof ReturnTag) {
                    $types = $tag->getTypes();
                    if ($tag instanceof MethodTag) {
                        $args = $tag->getArguments();
                        foreach ($args as $strings) {
                            if (count($strings) > 1) {
                                foreach ($strings as $string) {
                                    $string = trim($string);
                                    if (strpos($string, '$') === false
                                        && !in_array(strtolower($string), $this->ignoredMethodArgumentTypes)
                                    ) {
                                        $types[] = '\\' . trim($string, '\\');
                                    }
                                }
                            }
                        }
                    }
                    foreach ($types as $type) {
                        if (strpos($type, '\\') === 0) {
                            $type = rtrim($type, '[]');
                            $tagName = new Node\Name(trim($type, '\\'));
                            if (strpos($tag->getContent(), $type) === false) {
                                $tagName = $this->resolveClassName($tagName);
                            }
                            $nameString = $tagName->toString();
                            $tagNames[$nameString] = $tagName->toString();
                        }
                    }
                }
            }
            if ($tagNames) {
                $node->setAttribute(self::TAG_NAMES_ATTRIBUTE, $tagNames);
            }
        }
    }
}
