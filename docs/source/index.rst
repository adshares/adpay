Welcome to AdPay's documentation!
=================================

What is AdPay?
---------------

AdPay is an element in the Adshares network. It calculates payments for individual events, based on:

    * campaign budget,
    * total events for this campaign in given period of time,
    * individual event worth, which can calculated based on:
        * event type,
        * user value,
        * human score.

User Guide
==========

Goal of AdPay
--------------

Provide AdServer with payment calculations, ie. answer the question: how much to pay for those events?

Architecture
------------

AdPay is a Twisted app, backed by MongoDB and communicating with AdServer using a JSON-RPC interface.

Python stack is as follows:

    * Twisted for the core network communication and asynchronous event handling
    * txmongo for asynchronous MongoDB communication
    * fastjsonrpc for JSON-RPC protocol
    * jsonobject for easy JSON-Python object mapping

Development
===========

Extending functionality
-----------------------

All the payment calculations happen in the :py:mod:`adpay.stats` module, so if you want to change the algorithms, you'll need to rewrite that part of the code.

The calculations are run periodically. The main function for each run is the :py:func:`adpay.stats.tasks._adpay_task`. Calculation functions can be found in :py:mod:`adpay.stasts.utils`.

Table of contents
-----------------

.. toctree::
   :maxdepth: 6

   api
   reference
   testing
   config
   deploy
   contributing

Indices and tables
==================

* :ref:`genindex`
* :ref:`modindex`
* :ref:`search`
