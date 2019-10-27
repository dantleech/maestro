Graph Filter
============

You can filter which nodes are executed using a DSL based on the `Symfony
Expression Language`_.

The filter will match nodes and *all dependencies* required to reach that node.

Examples
--------

Nodes with the name "phpunit":

.. code-block:: bash

   $ maestro run --filter="label == 'phpunit'"

Containing nodes tagged "foobar":

.. code-block:: bash

   $ maestro run --filter="'foobar' in tags"

With the ``package`` task:

.. code-block:: bash

   $ maestro run --filter="task['alias'] == 'package'"

If you just want to run a branch of the graph you can use the ``branch``
function:

.. code-block:: bash

   $ maestro run --filter="branch('/maestrophp/example-math')"

Available Fields
----------------

The attributes are determined by the normalized representation of the node,
which is the same as produced by the :doc:`/reference/reports/json` report.

.. code-block:: bash

    $ maestro run -rjson --no-loop | jq

Or to just see a simple list of nodes:

.. code-block:: bash

    $ maestro run -rnode --no-loop

.. _`Symfony Expression Language`: https://symfony.com/doc/current/components/expression_language.html
