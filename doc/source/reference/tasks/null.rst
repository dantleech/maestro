null
====

- **Alias**: null
- **Task**: Maestro\Library\Task\Task\NullTask
- **Handler**: Maestro\Library\Task\Task\NullHandler
- **Arguments**:
   - *This task has no arguments*

This task does nothing. It can be used to create aggregate targets.

In the following example the ``null`` tasks 4, 5 and 6 depend on all of tasks
1, 2, and 3 to be successful. Rather than each explicitly declaring all the
dependencies, they can target the ``null`` task.

Example:

.. literalinclude:: ../../../../example/null.json
   :language: json

Which looks something like this:

.. graphviz::

    digraph maestro {
      rankdir=TB
      "root" [color=black label=<<b>root</b> (done) <br/><font point-size='10'><font color='blue'>was initializing</font><br/>Artifact: <i>Maestro\\Library\\Support\\Environment\\Environment</i><br/>Artifact: <i>Maestro\\Library\\Support\\Variables\\Variables</i><br/>Artifact: <i>Maestro\\Library\\Support\\ManifestPath</i><br/></font>>]
      "example/package" [color=black label=<<b>example/package</b> (done) <br/><font point-size='10'><font color='blue'>was initializing package example/package</font><br/>Artifact: <i>Maestro\\Library\\Support\\Environment\\Environment</i><br/>Artifact: <i>Maestro\\Library\\Support\\Package\\Package</i><br/>Artifact: <i>Maestro\\Library\\Workspace\\Workspace</i><br/></font>>]
      "example/package/phpunit" [color=black label=<<b>phpunit</b> (done) <br/><font point-size='10'><font color='blue'>was doing nothing</font><br/></font>>]
      "example/package/phpstan" [color=black label=<<b>phpstan</b> (done) <br/><font point-size='10'><font color='blue'>was doing nothing</font><br/></font>>]
      "example/package/php-cs-fixer" [color=black label=<<b>php-cs-fixer</b> (done) <br/><font point-size='10'><font color='blue'>was doing nothing</font><br/></font>>]
      "example/package/qa" [color=black label=<<b>qa</b> (done) <br/><font point-size='10'><font color='blue'>was doing nothing</font><br/></font>>]
      "example/package/tag_versions" [color=black label=<<b>tag_versions</b> (done) <br/><font point-size='10'><font color='blue'>was doing nothing</font><br/></font>>]
      "example/package/build_documentation" [color=black label=<<b>build_documentation</b> (done) <br/><font point-size='10'><font color='blue'>was doing nothing</font><br/></font>>]
      "root"->"example/package"
      "example/package"->"example/package/phpunit"
      "example/package"->"example/package/phpstan"
      "example/package"->"example/package/php-cs-fixer"
      "example/package/phpunit"->"example/package/qa"
      "example/package/phpstan"->"example/package/qa"
      "example/package/php-cs-fixer"->"example/package/qa"
      "example/package/qa"->"example/package/tag_versions"
      "example/package/qa"->"example/package/build_documentation"
    }
