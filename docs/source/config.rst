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
