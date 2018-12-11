daemon.py
=========

``daemon.py`` is the main entry point for the server.

When run, it does the following (in order):

* sets up logging
* configures database
* initializes cache
* configures periodical tasks
* sets up the interface
* starts listening for connections
