PhpDependencyAnalysis
=====================

[![Build Status](https://travis-ci.org/mamuz/PhpDependencyAnalysis.svg?branch=master)](https://travis-ci.org/mamuz/PhpDependencyAnalysis)
[![Coverage Status](https://img.shields.io/coveralls/mamuz/PhpDependencyAnalysis.svg)](https://coveralls.io/r/mamuz/PhpDependencyAnalysis?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/mamuz/PhpDependencyAnalysis/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/mamuz/PhpDependencyAnalysis/?branch=master)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/5dad5765-c411-41a5-9d3c-f1cf3d40ed45/mini.png)](https://insight.sensiolabs.com/projects/5dad5765-c411-41a5-9d3c-f1cf3d40ed45)
[![HHVM Status](http://hhvm.h4cc.de/badge/mamuz/php-dependency-analysis.png)](http://hhvm.h4cc.de/package/mamuz/php-dependency-analysis)
[![Dependency Status](https://www.versioneye.com/user/projects/5431680abeeeeed15600019e/badge.svg)](https://www.versioneye.com/user/projects/5431680abeeeeed15600019e)

[![Latest Stable Version](https://poser.pugx.org/mamuz/php-dependency-analysis/v/stable.svg)](https://packagist.org/packages/mamuz/php-dependency-analysis)
[![Latest Unstable Version](https://poser.pugx.org/mamuz/php-dependency-analysis/v/unstable.svg)](https://packagist.org/packages/mamuz/php-dependency-analysis)
[![Total Downloads](https://poser.pugx.org/mamuz/php-dependency-analysis/downloads.svg)](https://packagist.org/packages/mamuz/php-dependency-analysis)
[![License](https://poser.pugx.org/mamuz/php-dependency-analysis/license.svg)](https://packagist.org/packages/mamuz/php-dependency-analysis)

PhpDependencyAnalysis is an extandable static code analysis for
PHP-Projects (>= 5.3.3) to provide a [`dependency graph`](http://en.wikipedia.org/wiki/Dependency_graph)
for abstract Datatypes (Classes, Interfaces and Traits) based on [`namespaces`](http://php.net/manual/en/language.namespaces.php).
Read the [Introduction](https://github.com/mamuz/PhpDependencyAnalysis/wiki/1.-Introduction) for further informations.

## Installation

For graph creating [`GraphViz`](http://www.graphviz.org/) is required on your machine, which is
an open source graph visualization software and available for the most platforms.

After installing [`GraphViz`](http://www.graphviz.org/) the recommended way to install
[`mamuz/php-dependency-analysis`](https://packagist.org/packages/mamuz/php-dependency-analysis) is through
[composer](http://getcomposer.org/) by adding dependency to your `composer.json`:

```json
{
    "require-dev": {
        "mamuz/php-dependency-analysis": "dev-master"
    }
}
```

## Features

- Providing high customizing level
- Creating dependency graphs on customized levels respectively different scopes
- Detecting cycles and violations between layers in a tiered architecture
- Printing graphs in several formats (HTML, SVG, DOT)
- Adding user-defined detection plugins
- Adding user-defined output plugins for printing graphs
- Supporting collecting namespaces from [`IoC-Containers`](http://en.wikipedia.org/wiki/Inversion_of_control)
- Supporting collecting [`PHP-Superglobals`](http://php.net/manual/en/language.variables.superglobals.php) as a dependency
- Supporting collecting PHP-Statements, which cannot be resolved, like `create_function` or `eval`
- Supporting collecting namespaces, which are declared in DocBlocks
- Supporting collecting string, which looks like a namespace

## Configuration

This tool is configurable by a [`YAML`](http://en.wikipedia.org/wiki/YAML) file.
You can copy a prepared file from the vendor directory.

```sh
cp ./vendor/mamuz/php-dependency-analysis/phpda.yml ./myconfig.yml
```

See [`here`](https://github.com/mamuz/PhpDependencyAnalysis/blob/master/phpda.yml) for prepared configuration
and read [Configurations](https://github.com/mamuz/PhpDependencyAnalysis/wiki/3.-Configuration) for available options.

## Usage

Run this command line to create a dependecy graph:

```sh
./vendor/bin/phpda analyze /path/to/myconfig.yml
```

After that open report file, which is declared as `target` in the configuration, with your prefered tool.

## Limitations

PHP is a dynamic language with a weak type system.
It also contains a lot of expressions, which will be resolved first in runtime.
This tool is a static code analysis, thus it have some limitations.
Here is a non-exhaustive list of unsupported php-features:

- Dynamic features such as `eval` and `$$x`
- Globals such as `global $x;`
- Dynamic funcs such as `call_user_func`, `call_user_func_array`, `create_function`

The cleaner your project is, the more dependencies can be detected.
Or in other words, it's highly recommend to have a clean project before using this tool.
Clean means having less violations detected by [`PHP_CodeSniffer`](https://github.com/squizlabs/PHP_CodeSniffer).

## [Wiki](https://github.com/mamuz/PhpDependencyAnalysis/wiki)

1. [Introduction](https://github.com/mamuz/PhpDependencyAnalysis/wiki/1.-Introduction)
2. [Requirements](https://github.com/mamuz/PhpDependencyAnalysis/wiki/2.-Requirements)
3. [Configuration](https://github.com/mamuz/PhpDependencyAnalysis/wiki/3.-Configuration)
4. [Examples](https://github.com/mamuz/PhpDependencyAnalysis/wiki/4.-Examples)
5. [Plugins](https://github.com/mamuz/PhpDependencyAnalysis/wiki/5.-Plugins)
