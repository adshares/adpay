Testing
=======

For testing you'll need additional libraries (mock and mongomock). Install the environment:

    ``pipenv install --dev``

Test using Twisted Trial and mongomock:

    ``pipenv run tests``

Test with a live MongoDB instance, without mongomock:

    ``pipenv run tests_mongo``

Test code coverage, after running tests run:

    ``pipenv run quality``

See ``Pipfile`` for command details.
