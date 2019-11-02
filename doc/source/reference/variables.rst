Variables
=========

Variables can be defined on any configured node. They:

- Can replace values in the configuration iteslf.
- Are made available within an artifact to any tasks.

Config Replacement
------------------

You can use variables to replace any argument value in the configuration for
example:

.. literalinclude:: ../../../example/variables.json
   :language: json

The following rules apply:

- ``string`` values can be replaced within a string as above.
- ``array`` values will replace the _entire_ value. Any surrounding text or
  subsequent tokens will not be taken into account.

Artifacts
---------

The ``Variables`` artifact is made available for all task handlers and
contains the aggregated variables for the ancestors of the task's originating
node.
  

