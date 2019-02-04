Testing
=======

For testing you'll need additional libraries (mock, mongomock, behave). Install the environment:

.. code-block:: sh

    pipenv install --dev

You might need to set PYTHONPATH. If you're in the checkout directory, you can:

.. code-block:: sh

    export PYTHONPATH=`pwd`

Test using Twisted Trial and mongomock:

.. code-block:: sh

    pipenv run tests

Test with a live MongoDB instance, without mongomock:

.. code-block:: sh

    pipenv run tests_mongo

Test code coverage, after running tests run:

.. code-block:: sh

    pipenv run quality

To see code coverage, run:

.. code-block:: sh

    pipenv run python coverage report -m

To do behavioural tests, run:

.. code-block:: sh

    pipenv run behave tests

See `Pipfile` for command details.
