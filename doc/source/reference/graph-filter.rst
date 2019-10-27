Graph Filter
============

You can filter which nodes are executed using a DSL based on the `Symfony
Expression Language`_.

The filter will match nodes and *all dependencies* required to reach that node.

For example to run only nodes with the name "phpunit":

.. code-block:: bash

   $ maestro run --filter="label == 'phpunit'"

Or only containing nodes tagged "foobar":

.. code-block:: bash

   $ maestro run --filter="'foobar' in tags"

If you just want to run a branch of the graph you can use the ``branch``
function:

.. code-block:: bash

   $ maestro run --filter="branch('/maestrophp/example-math')"

You can filter on any node attribute. The attributes are determined by the
normalized representation of the node, which is the same as produced by the
:doc:`/reference/reports/json` report.

.. _`Symfony Expression Language`: https://symfony.com/doc/current/components/expression_language.html
