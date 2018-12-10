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
                    "time_end": 1643326642,
                    "campaign_id": "BXfmBKBdsQdDOdNbCtxd",
                    "filters": {
                                "require": {
                                            "age": ["18--30"],
                                            "interest": ["cars"],
                                            "movies": ["action", "horror", "thriller"]
                                            },
                                "exclude": {"country": ["DE"]}
                                },
                    "keywords": {},
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

impression_add
^^^^^^^^^^^^^^

    .. http:post:: /

        Add information about impressions to AdSelect.

        **Example request**:

        .. sourcecode:: http

              POST / HTTP/1.1
              Host: example.com

              {
               "jsonrpc": "2.0",
               "method": "impression_add",
               "id": 2,
               "params": [
                          {
                            "keywords": {"movies": "horror"},
                            "publisher_id": "SnalpVeRjxGSUWsGPRQl",
                            "banner_id": "vsbbPLCnckRzPUZtMXXU",
                            "user_id": "tLCCzlEJUgtJyMyqqJFn",
                            "paid_amount": 0.277
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


banner_select
^^^^^^^^^^^^^

    .. http:post:: /

        Select best banner.

        **Example request**:

        .. sourcecode:: http

              POST / HTTP/1.1
              Host: example.com

              {
               "jsonrpc": "2.0",
               "method": "impression_add",
               "id": 2,
               "params": [
                          {
                            "user_id": "CpneRnqUXGrvbferpudC",
                            "banner_size": "100x400",
                            "banner_filters":
                                {
                                    "exclude": {},
                                    "require": {"movies": ["horror"]}
                                },
                            "request_id": 3397,
                            "keywords": {},
                            "publisher_id": 4141
                        }
                        ]
               }

        **Example success response**:

        .. sourcecode:: http

            HTTP/1.1 200 OK
            Content-Type: application/json

            {
                "jsonrpc": "2.0",
                "result": [
                            {
                            "banner_id": "EMtkCfWfcaVwmreyLSyL",
                            "request_id": 965
                            }
                           ],
                "id": 2
            }


        :resheader Content-Type: application/json
        :statuscode 200: Success or JSON-RPC error (see :ref:`json-rpc-errors`)
