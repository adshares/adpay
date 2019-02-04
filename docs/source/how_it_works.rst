How it works
============

AdPay collects the following information:

- Campaigns (start and end time, filters)
- Banners (keywords)
- Payments for events (paid amount)


Payment calculation process
---------------------------

- AdPay periodically check if it should calculate payments for the last round.
- Payments for each campaign are calculated separately.

#. Each unique user get assigned a user budget. User budget maximum pay out, per event type, is the maximum defined value in campaign. Payment is calculated per user per event type, not per event.
#. User budget payments can be lowered by lowering the share of the user in all events.
#. Each event gets assigned a payment. This payment is a share of the final user budget for this event type.

In detail
---------

#. Each unique user get assigned a user budget. User budget maximum pay out, per event type, is the maximum defined value in campaign. Payment is calculated per user per event type, not per event.

   #. Get all distinct users with events in this campaign in this payment round.
   #. Create default user budget for each of them. Each user, for each event type, gets a default payment equal to what's defined in campaign.

#. User budget payments can be lowered by lowering the share of the user in all events.

    #. User share in payment round is calculated.
    #. Payment for event type is lowered according to user share.

#. Each event gets assigned a payment. This payment is a share of the final user budget for this event type.

User share
----------

.. note::

    [var] in the following documentation means a configurable variable. It can be different for each step.

There are two payment calculation modes:

- main (default) - simple
- legacy - this mode calculates user share by analyzing keyword frequencies

In the main mode, the user share is simply value divided by number of events (of this event type).

In legacy mode, user keyword profiles and human score play a role. AdPay calculates global and user keyword frequencies (how rare is a keyword).

- The keyword frequencies decay [var] percent with each payment round calculation.
- Only frequencies larger than [var] are taken into account.
- User human score directly correlates to user share.
- Users with globally rare keywords or high keyword frequency get higher share.
