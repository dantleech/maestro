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
  - ``vars``: Variables for dependent nodes.

Defines and initializes a package/project.
