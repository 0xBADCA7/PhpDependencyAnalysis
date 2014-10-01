<?php

namespace PhpDA\Entity;

trait AnalysisAwareTrait
{
    /** @var Analysis */
    private $analysis;

    /**
     * @param Analysis $analysis
     */
    public function setAnalysis(Analysis $analysis)
    {
        $this->analysis = $analysis;
    }

    /**
     * @return Analysis $analysis
     */
    public function getAnalysis()
    {
        return $this->analysis;
    }
}
