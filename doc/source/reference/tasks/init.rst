init
====

- **Alias**: ``init``
- **Task**: ``Maestro\Extension\Runner\Task\InitTask``
- **Handler**: ``Maestro\Extension\Vcs\Task\CheckoutHandler``
- **Arguments**:
    - ``env`` (array)
    - ``vars`` (array)

This is the type automatically assumed by the root of the project. It allows
you to set global env and vars.

.. warning::

    Internal task. This task is automatically assigned to the root node of
    your plan.
