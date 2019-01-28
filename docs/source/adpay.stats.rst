adpay.stats package
===================

This package provides the functionality behind all adpay calculations.

The responsibility break down is:

* adpay.stats.cache provides a cache for speeding up the calculations (legacy mode only)
* adpay.stats.consts is the module hosting configuration
* adpay.stats.legacy provides the legacy, user-value algorithm for calculating payments
* adpay.stats.main provides the default algorithm for calculating payments
* adpay.stats.tasks provides functionality for periodical calculations
* adpay.stats.utils provides helper functionality for calculating payments


adpay.stats.cache module
------------------------

.. automodule:: adpay.stats.cache
    :members:
    :show-inheritance:

adpay.stats.tasks module
------------------------

.. automodule:: adpay.stats.tasks
    :members:
    :show-inheritance:

adpay.stats.utils module
------------------------

.. automodule:: adpay.stats.utils
    :members:
    :show-inheritance:
