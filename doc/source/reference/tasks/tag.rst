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

Example configuration using the tag task - the package will be tagged with
``1.5.0`` if the actual version is not ``1.5.0`` and if the ``tests`` node
passes.

.. literalinclude:: ../../../../example/tag.json
   :language: json
