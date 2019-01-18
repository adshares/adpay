daemon.py
=========

``daemon.py`` is the main entry point for the server.

When run, it does the following (in order):

1) Sets up logging.
2) Configures database.
3) (optional) Initializes cache.
4) (optional) Sets periodical calculations.
5) Sets up the server interface.
6) Starts listening for connections.
