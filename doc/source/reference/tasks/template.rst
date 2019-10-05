template
========

- **Alias**: ``template``
- **Task**: ``Maestro\Extension\Template\Task\TemplateTask``
- **Handler**: ``Maestro\Extension\Template\Task\TemplateHandler``
- **Arguments**:
    - ``path`` (string) Path to the Twig tempalte relative to the
      configuration file.
    - ``targetPath`` (string) File to which the rendered template should be
      placed.

Render a template and put its contents to a file in the package's workspace.

The template will have any variables you defined in the root configuration and
the package node.

.. literalinclude:: ../../../../example/template.json
   :language: json
