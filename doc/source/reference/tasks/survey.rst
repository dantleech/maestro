survey
======

- **Alias**: ``survey``
- **Task**: ``Maestro\Extension\Survey\Task\SurveyTask``
- **Handler**: ``Maestro\Extension\Survey\Task\SurveyHandler``
- **Arguments**:
    - *This task has no arguments*

Performs a survey using any configured *Surveyors*.

The `Survey` results will be available as artifacts to dependent nodes, and
various reports can be generated based on the survey results (for example the
generic :doc:`/reference/reports/survey` report).

Surveys can yield any informatio related to a package. For example there is a
surveyor to check a packages version on the Packagist package repository.

.. literalinclude:: ../../../../example/survey.json
   :language: json

