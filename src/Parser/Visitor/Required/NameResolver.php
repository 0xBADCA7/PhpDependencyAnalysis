<?php
/**
 * The MIT License (MIT)
 *
 * Copyright (c) 2017 Marco Muths
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

use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlockFactory;
use phpDocumentor\Reflection\Types\Compound;
use phpDocumentor\Reflection\Types\Context;
use phpDocumentor\Reflection\Types\Object_;
use PhpParser\Error;
use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt;
use PhpParser\NodeVisitor\NameResolver as PhpParserNameResolver;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Finder\SplFileInfo;

/**
 * @SuppressWarnings("PMD.CouplingBetweenObjects")
 */
class NameResolver extends PhpParserNameResolver implements LoggerAwareInterface
{
    const TAG_NAMES_ATTRIBUTE = '__tagNames';

    /** @var LoggerInterface */
    private $logger;

    /** @var SplFileInfo */
    private $file;

    /** @var array */
    private $namespaceAliases;

    /** @var array */
    private $validTags = [
        'method', 'param',
        'return', 'property-read',
        'property', 'property-write',
        'throws', 'var',
    ];

    /** @var array */
    private $invalidTypes = [
        'bool', 'boolean', 'void',
        'int', 'integer', 'scalar',
        'string', 'binary', 'array',
        'object', 'resource', 'callable',
        'mixed', 'null',
        'float', 'double', '$this',
        'this', 'self', 'parent', 'static',
        'true', 'false', 'object',
    ];

    public function __construct()
    {
        $this->logger = new NullLogger;
        $this->docBlockFactory = DocBlockFactory::createInstance();
    }

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param SplFileInfo $file
     */
    public function setFile(SplFileInfo $file)
    {
        $this->file = $file;
    }

    protected function addAlias(Stmt\UseUse $use, $type, Name $prefix = null)
    {
        parent::addAlias($use, $type, $prefix);

        $aliasName = $use->alias;
        $type |= $use->type;

        if (isset($this->aliases[$type][$aliasName])) {
            $this->namespaceAliases[$aliasName] = (string) $this->aliases[$type][$aliasName];
        } elseif (isset($this->aliases[$type][strtolower($aliasName)])) {
            $this->namespaceAliases[$aliasName] = (string) $this->aliases[$type][strtolower($aliasName)];
        }
    }

    public function enterNode(Node $node)
    {
        parent::enterNode($node);

        try {
            if ($doc = $node->getDocComment()) {
                $docBlock = $this->docBlockFactory->create(
                    str_replace('[]', '', $doc->getText()),
                    new Context((string) $this->namespace, (array) $this->namespaceAliases)
                );
                if ($tagNames = $this->collectTagNamesBy($docBlock->getTags())) {
                    $node->setAttribute(self::TAG_NAMES_ATTRIBUTE, $tagNames);
                }
            }
        } catch (\Exception $e) {
            $parseError = new Error($e->getMessage(), $node->getLine());
            $this->logger->warning($parseError->getMessage(), [$this->file]);
        }
    }

    /**
     * @param DocBlock\Tag[] $docTags
     * @return array
     */
    private function collectTagNamesBy(array $docTags)
    {
        $tagNames = [];

        foreach ($docTags as $tag) {
            if (in_array($tag->getName(), $this->validTags)) {
                $types = [];
                if ($tag instanceof DocBlock\Tags\Method) {
                    $types[] = $tag->getReturnType();
                    foreach ($tag->getArguments() as $arg) {
                        $types[] = $arg['type'];
                    }
                } elseif (is_callable([$tag, 'getType'])) {
                    /** @var DocBlock\Tags\Param $tag */
                    $types[] = $tag->getType();
                }

                foreach ($types as $type) {
                    if ($type instanceof Object_) {
                        $tagNames = $this->align($type, $tagNames);
                    } elseif ($type instanceof Compound) {
                        $tries = 0;
                        while ($tries < 100) {
                            if ($singleType = $type->get($tries)) {
                                $tagNames = $this->align($singleType, $tagNames);
                            } else {
                                break;
                            }
                            $tries++;
                        }
                    }
                }
            }
        }

        return $tagNames;
    }

    /**
     * @param string $type
     * @param array  $tagNames
     * @return array
     */
    private function align($type, array $tagNames)
    {
        $type = trim($type, '\\');
        if (!in_array($type, $this->invalidTypes)) {
            $tagNames[$type] = $type;
        }

        return $tagNames;
    }
}
