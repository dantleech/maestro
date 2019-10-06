tag
===

- **Alias**: ``tag``
- **Task**: ``Maestro\Extension\Vcs\Task\TagVersionTask``
- **Handler**: ``Maestro\Extension\Vcs\Task\TagVersionHandler``
- **Arguments**:
    - *This task has no arguments*

Tag a packages configured version only if the latest semantic version is
different from the configured version.

.. note::

   Use the :doc:`/reference/reports/version` report to check which packages will be tagged.

.. literalinclude:: ../../../../example/tag.json
   :language: json
