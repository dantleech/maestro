Run
===

The run report shows an overview of all the tasks that were executed.

It is the default report.

.. code:: bash

    ./bin/maestro run --plan=example/template.json --report=run
    Run Report
    ==========

     Summary of all tasks executed during run

    +-----------------+--------+--------------------------------+----+---------------------------------------+
    | package         | label  | action                         | ✔  |                                       |
    +-----------------+--------+--------------------------------+----+---------------------------------------+
    | example/package | readme | applying template "./template/ | ✘  | Variable "parameters" does not exist. |
    |                 |        | travis.yml.twig" to "./.travis |    |                                       |
    |                 |        | .yml"                          |    |                                       |
    +-----------------+--------+--------------------------------+----+---------------------------------------+
     3 nodes, 0 pending 2 succeeded, 0 cancelled, 1 failed
