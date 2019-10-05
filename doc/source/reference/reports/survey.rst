Survey
======

The survey report dumps all information collected by surveys.

.. code:: bash

    $ ./bin/maestro run --plan=example/test.json --report=survey

    Survey results
    ==============

     Shows all the information collected during any surveys


    phpactor/config-loader/survey
    -----------------------------

     Maestro\Extension\Vcs\Survey\VersionResult

    +-----------------------+----------------------------------------------+
    | packageName           | "phpactor\/config-loader"                    |
    | configuredVersion     | "1.3.0"                                      |
    | mostRecentTagName     | "0.1.1"                                      |
    | mostRecentTagCommitId | "61db28afa005ac814d7cf48fce70f07e897e038c"   |
    | headId                | "3126908eb9ee957aaea193a084d113f5684f59ec"   |
    | headComment           | "Update LICENSE"                             |
    | commitsBetween        | ["3126908eb9ee957aaea193a084d113f5684f59ec"] |
    +-----------------------+----------------------------------------------+

     Maestro\Extension\Composer\Survery\ComposerConfigResult

    +-------------+-------------+
    | branchAlias | "1.0.x-dev" |
    +-------------+-------------+

     Maestro\Library\Composer\PackagistPackageInfo

    +---------+---------------------------+
    | name    | "phpactor\/config-loader" |
    | version | "0.1.1"                   |
    +---------+---------------------------+

    phpactor/code-transform/survey
    ------------------------------

    # ... etc
