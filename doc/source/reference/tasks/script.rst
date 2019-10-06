script
======

- **Alias**: ``script``
- **Task**: ``Maestro\Extension\Script\Task\ScriptTask``
- **Handler**: ``Maestro\Extension\Script\Task\ScriptHandler``
- **Arguments**:
    - ``script`` (string) The script to run.

The script task runs a script. If the script fails then the node fails.

.. literalinclude:: ../../../../example/script.json
   :language: json

