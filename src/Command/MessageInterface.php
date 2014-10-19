<?php

namespace PhpDA\Command;

interface MessageInterface
{
    const VERSION = 'v0.0.1-beta.3';

    const NAME = 'PhpDependencyAnalyse by Marco Muths';

    const COMMAND = 'analyze';

    const HELP = 'Please visit <info>https://github.com/mamuz/PhpDependencyAnalysis</info> for detailed informations.';

    const ARGUMENT_CONFIG = 'Path to yaml configuration file.';

    const OPTION_SOURCE = 'Directory to analyze.';

    const OPTION_FILE_PATTERN = 'Pattern to match files for analysis.';

    const OPTION_IGNORE = 'Exclude directories from source for analysis.';

    const OPTION_FORMATTER = 'Formatter as FQN for creating dependency graph.';

    const OPTION_TARGET = 'Filepath for writing created dependency graph.';

    const READ_CONFIG_FROM = 'Configuration read from ';

    const WRITE_GRAPH_TO = 'Write dependency graph to ';

    const PROGRESS_DISPLAY = '%current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s% Memory: %memory:6s%';

    const PARSE_ERRORS = '<error>Parse Errors:</error>';

    const NOTHING_TO_PARSE = '<error>No files found!</error>';

    const DONE = 'Done.';
}
