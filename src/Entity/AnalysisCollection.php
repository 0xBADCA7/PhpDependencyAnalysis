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

namespace PhpDA\Entity;

use Fhaculty\Graph\Graph;
use Fhaculty\Graph\Vertex;
use PhpParser\Error;
use PhpParser\Node\Name;

class AnalysisCollection
{
    /** @var Graph */
    private $graph;

    /** @var Error[] */
    private $analysisFailures = array();

    /**
     * @param Graph $graph
     */
    public function __construct(Graph $graph)
    {
        $this->graph = $graph;
    }

    /**
     * @return Graph
     */
    public function getGraph()
    {
        return $this->graph;
    }

    /**
     * @return bool
     */
    public function hasAnalysisFailures()
    {
        return !empty($this->analysisFailures);
    }

    /**
     * @return Error[]
     */
    public function getAnalysisFailures()
    {
        return $this->analysisFailures;
    }

    /**
     * @param Analysis $analysis
     * @return void
     */
    public function attach(Analysis $analysis)
    {
        if ($analysis->hasParseError()) {
            $this->addAnalysisFailure($analysis);
        } else {
            foreach ($analysis->getAdts() as $adt) {
                $this->attachAdt($adt);
            }
        }
    }

    /**
     * @param Analysis $analysis
     * @return void
     */
    private function addAnalysisFailure(Analysis $analysis)
    {
        $this->analysisFailures[$analysis->getFile()->getRealPath()] = $analysis->getParseError();
    }

    /**
     * @param Adt $adt
     * @return void
     */
    private function attachAdt(Adt $adt)
    {
        $declaredNamespace = $this->createVertexBy($adt->getDeclaredNamespace());
        $this->createEdgesFor($adt->getUsedNamespaces(), $declaredNamespace);
        $this->createEdgesFor($adt->getUnsupportedStmts(), $declaredNamespace);
        $this->createEdgesFor($adt->getNamespacedStrings(), $declaredNamespace);
    }

    /**
     * @param Name $name
     * @return Vertex
     */
    private function createVertexBy(Name $name)
    {
        return $this->graph->createVertex($name->toString(), true);
    }

    /**
     * @param Name[] $dependencies
     * @param Vertex $root
     * @return void
     */
    private function createEdgesFor(array $dependencies, Vertex $root)
    {
        foreach ($dependencies as $edge) {
            $vertex = $this->createVertexBy($edge);
            if (!$root->hasEdgeTo($vertex)) {
                $root->createEdgeTo($vertex);
            }
        }
    }
}
