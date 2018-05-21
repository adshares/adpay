Welcome to AdPay's documentation!
=================================

What is AdPay?
---------------

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

Supervisor config
^^^^^^^^^^^^^^^^^

Config for supervisor daemon configuration (log and pid file paths) is in *config/supervisord.conf*.

User Guide
==========

Goal of AdPay
--------------

Architecture
------------

Development
===========

Extending functionality
-----------------------

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
