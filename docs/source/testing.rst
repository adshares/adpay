Testing
=======

For testing you'll need additional libraries (mock, mongomock, behave). Install the environment:

    ``pipenv install --dev``

You might need to set PYTHONPATH. If you're in the checkout directory, you can:

    ``export PYTHONPATH=`pwd```

Test using Twisted Trial and mongomock:

    ``pipenv run tests``

Test with a live MongoDB instance, without mongomock:

    ``pipenv run tests_mongo``

Test code coverage, after running tests run:

    ``pipenv run quality``

To see code coverage, run:

    ``pipenv run python coverage report -m``

To do behavioural tests, run:

    ``pipenv run behave``

See ``Pipfile`` for command details.
