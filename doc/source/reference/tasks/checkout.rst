checkout
========

- **Alias**: ``checkout``
- **Task**: ``Maestro\Extension\Vcs\Task\CheckoutTask``
- **Handler**: ``Maestro\Extension\Vcs\Task\CheckoutHandler``
- **Arguments**:
    - ``url`` (string)

Checkout a repository from a VCS system (currently limited to GIT) onto the
current workspace.

.. warning::

    Internal task. This is task is when initializing a package internally. It
    would make no sense to use this task in your plan.

.. literalinclude:: ../../../../example/json_file.json
   :language: json

