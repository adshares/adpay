Configuration
=============

Configuration is controlled through environmental variables. Default values are provided below.

Application (adpay.const)
--------------------------

.. automodule:: adpay.const
    :members:
    :undoc-members:
    :show-inheritance:

Database (adpay.db.const)
--------------------------

.. automodule:: adpay.db.const
    :members:
    :undoc-members:
    :show-inheritance:

Logging
-------

Log messages are sent to stdout/stderr.

*config/log_config.json* contains default Python logging configuration. You can learn more about it `here. <https://docs.python.org/2/library/logging.config.html>`_

Supervisor
----------

Config for supervisor daemon is in *config/adpay.conf*.
