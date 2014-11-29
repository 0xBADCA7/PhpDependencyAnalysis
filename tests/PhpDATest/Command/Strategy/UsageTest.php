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

namespace PhpDATest\Command\Strategy;

use PhpDA\Command\Strategy\Usage;

class UsageTest extends \PHPUnit_Framework_TestCase
{
    /** @var Usage */
    protected $fixture;

    /** @var \Symfony\Component\Finder\Finder | \Mockery\MockInterface */
    protected $finder;

    /** @var \PhpDA\Entity\AnalysisCollection | \Mockery\MockInterface */
    protected $collection;

    /** @var \PhpDA\Parser\AnalyzerInterface | \Mockery\MockInterface */
    protected $analyzer;

    /** @var \PhpDA\Writer\AdapterInterface | \Mockery\MockInterface */
    protected $writer;

    /** @var \PhpDA\Layout\BuilderInterface | \Mockery\MockInterface */
    protected $builder;

    /** @var \Symfony\Component\Console\Output\OutputInterface | \Mockery\MockInterface */
    protected $output;

    /** @var \PhpDA\Command\Config | \Mockery\MockInterface */
    protected $config;

    protected function setUp()
    {
        $this->builder = \Mockery::mock('PhpDA\Layout\BuilderInterface');
        $this->collection = \Mockery::mock('PhpDA\Entity\AnalysisCollection');
        $this->finder = \Mockery::mock('Symfony\Component\Finder\Finder');
        $this->analyzer = \Mockery::mock('PhpDA\Parser\AnalyzerInterface');
        $this->writer = \Mockery::mock('PhpDA\Writer\AdapterInterface');
        $this->output = \Mockery::mock('Symfony\Component\Console\Output\OutputInterface')->shouldIgnoreMissing();
        $this->config = \Mockery::mock('PhpDA\Command\Config');

        $formatter = \Mockery::mock('Symfony\Component\Console\Formatter\OutputFormatterInterface');
        $formatter->shouldIgnoreMissing();

        $this->output->shouldReceive('getFormatter')->andReturn($formatter);
        $this->output->shouldReceive('writeln');
        $this->analyzer->shouldReceive('getAnalysisCollection')->andReturn($this->collection);

        $filePattern = '*.php';
        $source = './src';
        $ignores = array('test');

        $this->config->shouldReceive('getFilePattern')->once()->andReturn($filePattern);
        $this->config->shouldReceive('getSource')->once()->andReturn($source);
        $this->config->shouldReceive('getIgnore')->once()->andReturn($ignores);

        $this->finder->shouldReceive('files')->once()->andReturnSelf();
        $this->finder->shouldReceive('name')->once()->with($filePattern)->andReturnSelf();
        $this->finder->shouldReceive('in')->once()->with($source)->andReturnSelf();
        $this->finder->shouldReceive('exclude')->once()->with($ignores)->andReturnSelf();

        $this->fixture = new Usage($this->finder, $this->analyzer, $this->builder, $this->writer);
    }

    public function testNothingToParse()
    {
        $this->finder->shouldReceive('count')->once()->andReturn(0);
        $this->fixture->setOptions(array('output' => $this->output, 'config' => $this->config));
        $this->fixture->execute();
    }

    public function testExecute()
    {
        $testcase = $this;
        $this->prepareAnalyzer();

        $file = \Mockery::mock('Symfony\Component\Finder\SplFileInfo');
        $file->shouldReceive('getRealPath')->once()->andReturn('anypath');

        $this->output->shouldReceive('getVerbosity')->andReturn(3);
        $this->finder->shouldReceive('count')->once()->andReturn(6000);
        $this->finder->shouldReceive('getIterator')->andReturn(array($file));
        $this->fixture->setOptions(array('output' => $this->output, 'config' => $this->config));

        $this->analyzer->shouldReceive('analyze')->once()->with($file);

        $formatter = 'format';
        $target = 'destination';
        $groupLength = 12;
        $graphViz = \Mockery::mock('PhpDA\Layout\GraphViz');

        $this->config->shouldReceive('hasVisitorOptionsForAggregation')->once()->andReturn(false);
        $this->config->shouldReceive('getFormatter')->once()->andReturn($formatter);
        $this->config->shouldReceive('getTarget')->twice()->andReturn($target);
        $this->config->shouldReceive('getGroupLength')->once()->andReturn($groupLength);

        $this->builder->shouldReceive('setGroupLength')->once()->with($groupLength);
        $this->builder->shouldReceive('setAnalysisCollection')->once()->with($this->collection);
        $this->builder->shouldReceive('setLayout')->once()->andReturnUsing(
            function ($layout) use ($testcase) {
                $testcase->assertInstanceOf('PhpDA\Layout\Standard', $layout);
            }
        );
        $this->builder->shouldReceive('create')->once()->andReturnSelf();
        $this->builder->shouldReceive('getGraphViz')->once()->andReturn($graphViz);

        $this->writer->shouldReceive('write')->once()->with($graphViz)->andReturnSelf();
        $this->writer->shouldReceive('with')->once()->with($formatter)->andReturnSelf();
        $this->writer->shouldReceive('to')->once()->with($target)->andReturnSelf();

        $this->fixture->execute();
    }

    private function prepareAnalyzer()
    {
        $visitor = array('foo');
        $visitorOptions = array('bar');

        $this->config->shouldReceive('getVisitor')->once()->andReturn($visitor);
        $this->config->shouldReceive('getVisitorOptions')->once()->andReturn($visitorOptions);

        $traverser = \Mockery::mock('PhpDA\Parser\NodeTraverser');
        $traverser->shouldReceive('setRequiredVisitors')->once()->with(
            array(
                'PhpDA\Parser\Visitor\Required\DeclaredNamespaceCollector',
                'PhpDA\Parser\Visitor\Required\MetaNamespaceCollector',
                'PhpDA\Parser\Visitor\Required\UsedNamespaceCollector',
            )
        );
        $traverser->shouldReceive('bindVisitors')->once()->with($visitor, $visitorOptions);

        $logger = \Mockery::mock('PhpDA\Parser\Logger');
        $logger->shouldReceive('isEmpty')->once()->andReturn(true);

        $this->analyzer->shouldReceive('getNodeTraverser')->once()->andReturn($traverser);
        $this->analyzer->shouldReceive('getLogger')->once()->andReturn($logger);
    }
}
