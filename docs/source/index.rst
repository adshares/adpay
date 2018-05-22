Welcome to AdPay's documentation!
=================================

What is AdPay?
---------------

AdPay is an element in the Adshares network. It calculates payments based on:

    * campaign budget,
    * total events for this campaign,
    * individual event worth, which is calculated based on:
        * event type,
        * user value.


Deployment
==========

Installation
------------
Full installation instructions can be found in `README.md <https://github.com/adshares/adpay/blob/master/README.md>`_. AdPay is run within a Virtualenv. Dependencies are provided in requirements.txt and you can use pip to install them.

Make sure you set up the ``$ADPAY_ROOT`` environment variable to point to the root directory of AdPay - the directory containing the adpay package.

Configuration
-------------

Configuration is spread among 5 files:

    * adpay.db.const
    * adpay.iface.const
    * adpay.stats.const
    * config/log_config.json
    * config/supervisord.conf

AdPay logging config
^^^^^^^^^^^^^^^^^^^^^

*config/log_config.json* contains Python logging configuration. You can learn more about it `here. <https://docs.python.org/2/library/logging.config.html>`_ The AdPay daemon will look for this file in the ``$ADPAY_ROOT/aduser/config`` directory, where ``$ADPAY_ROOT`` is an environmental variable.

AdPay database configuration
^^^^^^^^^^^^^^^^^^^^^^^^^^^^

*adpay.db.const* is a python file containing configuration for the MongoDB.

AdPay interface configuration
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

*adpay.iface.const* is a python file containing configuration for the JSON-RPC interface.

AdPay calculation configuration
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

*adpay.stats.const* is a python file containing configuration for the payment calculations.

Supervisor config
^^^^^^^^^^^^^^^^^

Config for supervisor daemon configuration (log and pid file paths) is in *config/supervisord.conf*.

Logging
-------

Logging config for the Python app can be found in the *config/log_config.json* file. By default, it's captured by supervisor to ``$ADPAY_ROOT/log/aduser.log``. Other logs (MongoDB, supervisord) can also be found in the same directory.

User Guide
==========

Goal of AdPay
--------------

Inform AdServer about payment calculations. Payments are calculated based on campaign budget, total events and user values.

Architecture
------------

AdPay is a Twisted app, backed by MongoDB and communicating with AdServer using a JSON-RPC interface.

Python stack is as follows:

    * Twisted for the core network communication and asynchronous event handling
    * txmongo for asynchronous MongoDB communication
    * fastjsonrpc for JSON-RPC protocol
    * jsonobject for easy JSON-Python object mapping
    * supervisor for running it as a daemon

Development
===========

Extending functionality
-----------------------

All the payment calculations happen in the :py:mod:`adpay.stats` module, so if you want to change the algorithms, you'll need to rewrite that part of the code. Some configuration is possible through the config files.

The calculations are run periodically. The main function for each run is the :py:func:`adpay.stats.tasks._adpay_task`. Calculation functions can be found in :py:mod:`adpay.stasts.utils`.

Testing
-------

For testing you'll need additional libraries (mock and mongomock). Tests can be run using Twisted Trial.

    ``trial tests``

To test with a live MongoDB instance, run the tests without the mongomock library.

    ``trial tests --without mongomock``

Packages
--------

.. toctree::
   :maxdepth: 1

   modules
   adpay
   adpay.db
   adpay.iface
   adpay.stats
   adpay.utils

Indices and tables
==================

* :ref:`genindex`
* :ref:`modindex`
* :ref:`search`
