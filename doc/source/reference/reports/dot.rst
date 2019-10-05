Dot
===

Generates a dot file which can be used to render a directional graph.

.. code:: bash

    $ ./bin/maestro run --plan=example/tag.json --report=dot

    Dot graph
    =========

     Dump the graph to a dotfile

    Writing dot file to: /home/daniel/www/dantleech/maestro/maestro.dot
    Generate the image with: dot /home/daniel/www/dantleech/maestro/maestro.dot -Tpng -o maestro.png


The below diagram shows the:

- Node name followed by the node state in parenthesis
- The task description
- List of any artifacts generated

.. graphviz::

    digraph maestro {
      rankdir=TB
      "root" [color=black label=<<b>root</b> (done) <br/><font point-size='10'><font color='blue'>was initializing</font><br/>Artifact: <i>Maestro\\Library\\Support\\Environment\\Environment</i><br/>Artifact: <i>Maestro\\Library\\Support\\Variables\\Variables</i><br/>Artifact: <i>Maestro\\Library\\Support\\ManifestPath</i><br/></font>>]
      "phpactor/container" [color=black label=<<b>phpactor/container</b> (done) <br/><font point-size='10'><font color='blue'>was initializing package phpactor/container</font><br/>Artifact: <i>Maestro\\Library\\Support\\Environment\\Environment</i><br/>Artifact: <i>Maestro\\Library\\Support\\Package\\Package</i><br/>Artifact: <i>Maestro\\Library\\Workspace\\Workspace</i><br/></font>>]
      "phpactor/container/tests" [color=black label=<<b>tests</b> (done) <br/><font point-size='10'><font color='blue'>was running true</font><br/>Artifact: <i>Maestro\\Library\\Script\\ScriptResult</i><br/></font>>]
      "phpactor/container/tag" [color=black label=<<b>tag</b> (done) <br/><font point-size='10'><font color='blue'>was applying tag</font><br/></font>>]
      "root"->"phpactor/container"
      "phpactor/container"->"phpactor/container/tests"
      "phpactor/container/tests"->"phpactor/container/tag"
    }

