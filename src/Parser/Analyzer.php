<?php

namespace PhpDA\Parser;

use PhpDA\Entity\Analysis;
use PhpDA\Entity\AnalysisAwareInterface;
use PhpDA\Entity\AnalysisCollection;
use PhpParser\Error;
use PhpParser\NodeTraverserInterface;
use PhpParser\ParserAbstract;
use Symfony\Component\Finder\SplFileInfo;

class Analyzer implements AnalyzerInterface
{
    /** @var ParserAbstract */
    private $parser;

    /** @var NodeTraverserInterface */
    private $traverser;

    /** @var AnalysisCollection */
    private $collection;

    /**
     * @param ParserAbstract     $parser
     * @param TraverseInterface  $traveser
     * @param AnalysisCollection $collection
     */
    public function __construct(
        ParserAbstract $parser,
        TraverseInterface $traveser,
        AnalysisCollection $collection
    ) {
        $this->parser = $parser;
        $this->traverser = $traveser;
        $this->collection = $collection;
    }

    public function getTraverser()
    {
        return $this->traverser;
    }

    public function analyze(SplFileInfo $file)
    {
        $analysis = new Analysis;

        if ($this->traverser instanceof AnalysisAwareInterface) {
            $this->traverser->setAnalysis($analysis);
        }

        try {
            $stmts = $this->parser->parse($file->getContents());
            $this->traverser->traverse($stmts);
        } catch (Error $error) {
            $analysis->setParseError($error);
        }

        $this->collection->attach($analysis);

        return $analysis;
    }

    public function getAnalysisCollection()
    {
        return $this->collection;
    }
}
