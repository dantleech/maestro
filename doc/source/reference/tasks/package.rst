package
=======

- **Alias**: ``package``
- **Task**: ``Maestro\Extension\Runner\Task\PackageInitTask``
- **Handler**: ``Maestro\Extension\Runner\Task\PackageHandler``
- **Arguments**:
  - ``name`` (string) Name of the package (uses the node name if not given).
  - ``url`` (string) canonical URL for this package.
  - ``purgeWorkspace`` (bool) Remove all data from the workspace.
  - ``version``: Current or desired semantic version for this package.
  - ``env``: Environment variables for dependent nodes
  - ``vars``: Variables for dependent nodes.

Defines package metadata data which can be used by dependent nodes.
