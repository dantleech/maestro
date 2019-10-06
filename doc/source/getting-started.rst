Getting Started
===============

In this example you are the maintainer of two PHP packages
``maestrophp/example-math`` and
``maestrophp/example-science``.

Installation
------------

You can install it as a project dependency:

.. code-block:: bash

   $ composer require dantleech/maestro

Create a configuration file
---------------------------

Let's start by creating a ``maestro.json`` configuration file:

.. literalinclude:: ../../example/tutorial/getting-started-1.json
   :language: json

Now run the plan:

.. code-block:: bash

    $ ./vendor/bin/maestro run -v

You should see something like:

.. code-block:: bash

   Run Report
   ==========

    Summary of all tasks executed during run                                                                               

   +----------------------------+----------+--------------------------------+----+--+
   | package                    | label    | action                         | ✔  |  |
   +----------------------------+----------+--------------------------------+----+--+
   | maestrophp/example-math    | checkout | checking out git@github.com:ma | ✔  |  |
   |                            |          | estrophp/example-math          |    |  |
   +----------------------------+----------+--------------------------------+----+--+
   | maestrophp/example-science | checkout | checking out git@github.com:ma | ✔  |  |
   |                            |          | estrophp/example-science       |    |  |
   +----------------------------+----------+--------------------------------+----+--+
    5 nodes, 0 pending 5 succeeded, 0 cancelled, 0 failed 

This is the default :doc:`run report <reference/reports/run>`. It shows the
status of the tasks that have been executed.

Applying templates
------------------

Now lets add some tasks. We want all of our packages to have a README file,
create the following Twig template somewhere relative to your
``maestro.json``:

.. literalinclude:: ../../example/tutorial/templates/readme.md.twig
   :language: twig

- We used the ``package`` variable, this refers to the current package object.
- We specified two custom variables which we will define below.

And add a task to apply it in your configuration:

.. literalinclude:: ../../example/tutorial/getting-started-2.json
   :language: json

Using Prototypes
----------------

We don't want to repeat information. Frequently packages in the same ecosystem
will have similar requirements. Maestro allows you to use *prototypes* :

.. literalinclude:: ../../example/tutorial/getting-started-3.json
   :language: json

- We declared a new prototype ``example``.
- We made both of our packages use the prototype.
- We used a special environment variable ``$PACKAGE_NAME`` in our URL.

Declaring Versions
------------------

Declare versions for your packages and then generate a version report.

.. literalinclude:: ../../example/tutorial/getting-started-4.json
   :language: json

Note:

- We added the ``version`` key to each of our packages.
- We added a :doc:`/reference/tasks/tag` task to the prototype: this will automatically tag the version if it is
  not existing.
- The ``tag`` task *depends* on the README task to succeed. In real life this would depend on
  all tests passing.

Then, for extra points we:

- We added a :doc:`/reference/tasks/survey` task to gather versioning
  information.

We can then run our plan and generate both :doc:`/reference/reports/run` and
a :doc:`/reference/reports/version` reports:

.. code-block:: bash

    ./bin/maestro run -v --report=run --report=version

Summary
-------

This tutorial demonstrates some basic functionality of Maestro. Checkout the
:doc:`reference` for more detailed informations.
