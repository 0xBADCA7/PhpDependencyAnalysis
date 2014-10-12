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

namespace PhpDATest\Parser\Visitor\Required;

use PhpDA\Parser\Visitor\Required\MultiNamespaceDetector;

class MultiNamespaceDetectorTest extends \PHPUnit_Framework_TestCase
{
    /** @var MultiNamespaceDetector */
    protected $fixture;

    protected function setUp()
    {
        $this->fixture = new MultiNamespaceDetector;
    }

    public function testExtendingNodeVisitor()
    {
        $this->assertInstanceOf('PhpParser\NodeVisitorAbstract', $this->fixture);
    }

    public function testSingleNamespace()
    {
        $namespaceNode = \Mockery::mock('PhpParser\Node\Stmt\Namespace_');
        $this->fixture->leaveNode($namespaceNode);

        $node = \Mockery::mock('PhpParser\Node');
        $this->fixture->leaveNode($node);
        $this->fixture->leaveNode($node);
        $this->fixture->leaveNode($node);
    }

    public function testMultiNamespace()
    {
        $this->setExpectedException('PhpParser\Error');
        $namespaceNode = \Mockery::mock('PhpParser\Node\Stmt\Namespace_');
        $this->fixture->leaveNode($namespaceNode);
        $this->fixture->leaveNode($namespaceNode);
    }

    public function testMultiNamespaceWithReset()
    {
        $this->fixture->beforeTraverse(array());
        $namespaceNode = \Mockery::mock('PhpParser\Node\Stmt\Namespace_');
        $this->fixture->leaveNode($namespaceNode);
        $this->fixture->beforeTraverse(array());
        $this->fixture->leaveNode($namespaceNode);
        $this->fixture->beforeTraverse(array());
        $this->fixture->leaveNode($namespaceNode);
    }
}
