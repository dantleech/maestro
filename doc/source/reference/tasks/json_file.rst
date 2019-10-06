json_file
=========

- **Alias**: ``json_file``
- **Task**: ``Maestro\Extension\Json\Task\JsonFileTask``
- **Handler**: ``Maestro\Extension\Json\Task\JsonFileHandler``
- **Arguments**:
    - ``targetPath`` (string) Path for new/existing JSON file relative to the
      package workspace.
    - ``merge`` (array) data to merge into JSON file

Create or update a JSON file. If the file exists then the data will be merged
into it.

Example:

.. literalinclude:: ../../../../example/json_file.json
   :language: json
