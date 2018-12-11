API methods
===========

campaign_update
^^^^^^^^^^^^^^^

    .. http:post:: /

        Updates (creates, if don't exist) campaign data, including banners.

        Please note:

        * the params attribute contains a list of :ref:`CampaignObject`.

        **Example request**:

        .. sourcecode:: http

              POST / HTTP/1.1
              Host: example.com

              {
               "jsonrpc": "2.0",
               "method": "campaign_update",
               "id": 2,
               "params": [
                    {
                        "time_start": 1543326642,
                        "campaign_id": "BXfmBKBdsQdDOdNbCtxd",
                        "time_end": 1643326642,
                        "advertiser_id": "QdDOdNbCtxdAXfmBKBds",
                        "max_cpc": 0.01,
                        "filters": {
                                    "require": {
                                                "age": ["18--30"],
                                                "interest": ["cars"],
                                                "movies": ["action", "horror", "thriller"]
                                                },
                                    "exclude": {"country": ["DE"]}
                                    },
                        "budget": 0.75,
                        "keywords": "{JSONObject object}",
                        "banners": [
                                {
                                    "keywords": {"movies": "horror"},
                                    "banner_id": "ZBOGqlCqaqjDICNWHRnT",
                                    "banner_size": "100x400"
                                },
                                {
                                    "keywords": {"movies": "action"},
                                    "banner_id": "FcNMkMibdAZMSdqugKvb",
                                    "banner_size": "100x400"
                                }
                        ],
                        "max_cpm": 0.005
                    }
                    ]
                }
                ]
               }

        **Example success response**:

        .. sourcecode:: http

            HTTP/1.1 200 OK
            Content-Type: application/json

            {
                "jsonrpc": "2.0",
                "result": "True",
                "id": 2
            }


        :resheader Content-Type: application/json
        :statuscode 200: Success or JSON-RPC error (see :ref:`json-rpc-errors`)

campaign_delete
^^^^^^^^^^^^^^^

    .. http:post:: /

        Removes campaign data (including banners) from AdSelect database.

        **Example request**:

        .. sourcecode:: http

              POST / HTTP/1.1
              Host: example.com

              {
               "jsonrpc": "2.0",
               "method": "campaign_delete",
               "id": 2,
               "params": [
                          "432gfdxhs",
                          "3wr42trse",
                          "fsdsafsw4"
                         ]
               }

        **Example success response**:

        .. sourcecode:: http

            HTTP/1.1 200 OK
            Content-Type: application/json

            {
                "jsonrpc": "2.0",
                "result": "True",
                "id": 2
            }


        :resheader Content-Type: application/json
        :statuscode 200: Success or JSON-RPC error (see :ref:`json-rpc-errors`)

add_events
^^^^^^^^^^

    .. http:post:: /

        Add information about impressions to AdSelect.

        **Example request**:

        .. sourcecode:: http

              POST / HTTP/1.1
              Host: example.com

              {
               "jsonrpc": "2.0",
               "method": "add_events",
               "id": 2,
               "params": [
                          {
                        "banner_id": "gPSlyhJAJwYnNmOLEWyl",
                        "event_type": "nORtFGEyjnEwznpmAUZL",
                        "event_id": "LWRNjngSddILRIhVTjAg",
                        "timestamp": 1543326642,
                        "their_keywords": "{JSONObject object}",
                        "our_keywords": "{JSONObject object}",
                        "human_score": 1.0,
                        "publisher_id": "cyXugkOnQvZlTzrOMVgb",
                        "event_value": 0.5
                    }
                        ]
               }

        **Example success response**:

        .. sourcecode:: http

            HTTP/1.1 200 OK
            Content-Type: application/json

            {
                "jsonrpc": "2.0",
                "result": "True",
                "id": 2
            }


        :resheader Content-Type: application/json
        :statuscode 200: Success or JSON-RPC error (see :ref:`json-rpc-errors`)


get_payments
^^^^^^^^^^^^

    .. http:post:: /

        Request payments.

        **Example request**:

        .. sourcecode:: http

              POST / HTTP/1.1
              Host: example.com

              {
               "jsonrpc": "2.0",
               "method": "get_payments",
               "id": 2,
               "params": [{"timestamp": 1643326642}]
              }

        **Example success response**:

        .. sourcecode:: http

            HTTP/1.1 200 OK
            Content-Type: application/json

            {
                "jsonrpc": "2.0",
                "result": [
                            {
                            "event_id": "EMtkCfWfcaVwmreyLSyL",
                            "amount": 0.965
                            },
                            {
                            "event_id": "caVwmreyLdasSyL",
                            "amount": 0.165
                            }
                           ],
                "id": 2
            }

        **Example not calculated yet response**:

        .. sourcecode:: http

            HTTP/1.1 200 OK
            Content-Type: application/json

            {
             "jsonrpc": "2.0",
             "id": 2,
              "error": {
                        "message": "Payments not calculated yet.",
                        "code": -32603
                        }
            }

        :resheader Content-Type: application/json
        :statuscode 200: Success or JSON-RPC error (see :ref:`json-rpc-errors`)
