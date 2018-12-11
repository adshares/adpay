JSONRPC basics
==============

All calls to AdPay follow the JSON-RPC protocol. The `params` field of request objects must use the objects defined in `protocol`. More details can be found in method descriptions.

`https://www.jsonrpc.org/specification`

Simple example
^^^^^^^^^^^^^^

.. http:post:: /

    **Simple request**:

    .. sourcecode:: http

        POST / HTTP/1.1
        Host: example.com
        Content-Type: application/json

        {
            "jsonrpc": "2.0",
            "method": "method_name",
            "id": 2,
            "params": {}
        }

    **Simple success response**:

    .. sourcecode:: http

        HTTP/1.1 200 OK
        Content-Type: application/json

        {
            "jsonrpc": "2.0",
            "result": "ok",
            "id": 2
        }

    :resheader Content-Type: application/json
    :statuscode 200: No error or JSON-RPC error

.. _json-rpc-errors:

Error responses
^^^^^^^^^^^^^^^
.. http:post:: /

    **Invalid JSON**:

    Invalid JSON was received by the server.
    An error occurred on the server while parsing the JSON text.

    .. sourcecode:: http

        HTTP/1.1 200 OK
        Content-Type: application/json

        {
         "jsonrpc": "2.0",
         "error": {
                   "code": -32700,
                   "message": "Parse error"
                   },
         "id": null
        }

    :resheader Content-Type: application/json
    :statuscode 200: No error or JSON-RPC error

    **Invalid request**

    The JSON sent is not a valid Request object.

    .. sourcecode:: http

        HTTP/1.1 200 OK
        Content-Type: application/json

        {
         "jsonrpc": "2.0",
         "error": {
                   "code": -32600,
                   "message": "Invalid Request"
                   },
         "id": null
        }

    :resheader Content-Type: application/json
    :statuscode 200: No error or JSON-RPC error

    **Method not found**

    The method does not exist / is not available.

    .. sourcecode:: http

        HTTP/1.1 200 OK
        Content-Type: application/json

        {
         "jsonrpc": "2.0",
         "error": {
                   "code": -32601,
                   "message": "Method not found"
                   },
         "id": null
        }

    :resheader Content-Type: application/json
    :statuscode 200: No error or JSON-RPC error

    **Invalid method parameter(s)**

    Invalid method parameter(s).

    .. sourcecode:: http

        HTTP/1.1 200 OK
        Content-Type: application/json

        {
         "jsonrpc": "2.0",
         "error": {
                   "code": -32602,
                   "message": "Invalid params"
                   },
         "id": null
        }

    :resheader Content-Type: application/json
    :statuscode 200: No error or JSON-RPC error

    **Internal JSON-RPC error.**

    Internal JSON-RPC error.

    .. sourcecode:: http

        HTTP/1.1 200 OK
        Content-Type: application/json

        {
         "jsonrpc": "2.0",
         "error": {
                   "code": -32603,
                   "message": "Internal error"
                   },
         "id": null
        }

    :resheader Content-Type: application/json
    :statuscode 200: No error or JSON-RPC error
