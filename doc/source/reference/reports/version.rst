Version
=======

The version report shows detailed information about many aspects of
packages versions, including:

- The most latest tagged version.
- The version which is registered in the package registry.
- The development version (i.e. ``branch-alias``).
- The configured version (i.e. the ``version`` defined in the maestro
  configuratoin file).

.. note::

    This report depends on a :doc:`/reference//tasks/survey` task being performed.

.. code:: bash

    ./bin/maestro run --plan=example/test.json --report=version

    Version Report
    ==============

     Detailed version overview for each package

    conf: configured version, tag: latest tagged version
    dev: development version (branch alias), reg: package registry version
    tag-id: commit-id of lastest tag, head-id: commit-id of latest commit + number of commits ahead of latest tag
    +------------------------------------+-------+-------+-----------+-------+------------+---------------+---------------------+
    | package                            | conf  | tag   | dev       | reg   | tag-id     | head-id       | message             |
    +------------------------------------+-------+-------+-----------+-------+------------+---------------+---------------------+
    | phpactor/config-loader             | 1.3.0 | 0.1.1 | 1.0.x-dev | 0.1.1 | 61db28afa0 | 3126908eb9 +1 | Update LICENSE      |
    | phpactor/code-transform            | 3.2.0 | 0.2.0 | 0.2.x-dev | 0.2.0 | ae03b2da4f | 62e48d83b1 +1 | Update LICENSE      |
    | phpactor/worse-reflection          | 1.2.0 | 0.3.1 | 0.3.x-dev | 0.3.1 | bbf17bde14 | 53d2f296a2 +4 | Update branch alias |
    | phpactor/code-builder              |       | 0.3.0 | 0.3.x-dev | 0.3.0 | 001a4ae23b | a530ce1710 +1 | Update LICENSE      |
    | phpactor/language-server           |       | 0.2.0 | 0.2.x-dev | 0.2.0 | 391bb9d6f4 | c2f3316c8e +1 | Update LICENSE      |
    | phpactor/language-server-extension |       | 0.2.0 | 0.2.x-dev | 0.2.0 | 2cd4feadc9 | 0382702084 +1 | Update LICENSE      |
    | phpactor/path-finder               |       | 0.1.0 | 1.0-dev   | 0.1.0 | 14f4c7a658 | 2268a591bf +1 | Update LICENSE      |
    +------------------------------------+-------+-------+-----------+-------+------------+---------------+---------------------+
