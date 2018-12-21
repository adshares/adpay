@fixture.adpay.db
Feature: Campaign functionality

  Background:
    Given Campaigns
      | _id                      | time_start | campaign_id                      | budget         | max_cpc | filters                                                                                                                                                                                                                       | max_cpm | time_end   |
      | 5c177e3a163a745c916c76e1 | 1542882034 | dGlxjZZHtUh4lJsMdGlxjZZHtUh4lJsM | 9876543210     | 600     | { "exclude" : { "user:lang" : [ "it" ], "site:domain" : [ "coinmarketcap.com", "icoalert.com" ] }, "require" : { "site:lang" : [ "pl", "en", "it", "jp" ], "user:gender" : [ "pl" ], "device:os" : [ "Linux", "Windows" ] } } | 600     | 1576147247 |
      | 5c177ed5163a745c916cbc5c | 1542882034 | k24vw5A4EmuMuWQSk24vw5A4EmuMuWQS | 11109876543    | 700     | { "exclude" : { "user:lang" : [ "it" ], "site:domain" : [ "coinmarketcap.com", "icoalert.com" ] }, "require" : { "site:lang" : [ "pl", "en", "it", "jp" ], "user:gender" : [ "pl" ], "device:os" : [ "Linux", "Windows" ] } } | 700     | 1576147247 |
      | 5c178372163a745c916ec491 | 1542221703 | de550ed7eefb457ba2129f1f7678d7c8 | 121110987654   | 800     | { "exclude" : {  }, "require" : { "interest" : [ "100" ] } }                                                                                                                                                                  | 800     | 1576244161 |
      | 5c17880b163a745c9170cbf8 | 1542882034 | dcUOVNOMkO902SrFdcUOVNOMkO902SrF | 1312111098765  | 900     | { "exclude" : { "user:lang" : [ "it" ], "site:domain" : [ "coinmarketcap.com", "icoalert.com" ] }, "require" : { "site:lang" : [ "pl", "en", "it", "jp" ], "user:gender" : [ "pl" ], "device:os" : [ "Linux", "Windows" ] } } | 900     | 1576147247 |
      | 5c17a5ac163a745c917de252 | 1542882034 | tt5ts00oAIxNaEyKtt5ts00oAIxNaEyK | 14131211109076 | 1000    | { "exclude" : { "user:lang" : [ "it" ], "site:domain" : [ "coinmarketcap.com", "icoalert.com" ] }, "require" : { "site:lang" : [ "pl", "en", "it", "jp" ], "user:gender" : [ "pl" ], "device:os" : [ "Linux", "Windows" ] } } | 1000    | 1576147247 |
    And Banners
      | _id                      | banner_id                        | campaign_id                      |
      | 5c1753a0163a745c915a0ce0 | 0402cd91762c4002aa6df8c00e5483cb | 77dbe8457ef2494683297a59e0bd6a7b |
      | 5c17585d163a745c915c0d3c | 0cbaf8c74b04406d97d6d90b950c9ea7 | 4a25f30b8d444f8aac0e18f8c7c2c5d1 |
      | 5c177d3d163a745c916c0774 | a9e6117efdc84f819c580d41f862cf9d | 4584a5272200451e99922d175c16d766 |
      | 5c177d3d163a745c916c0776 | ee34923d8acf4c4a8ff57fd37d7335ea | 4584a5272200451e99922d175c16d766 |
      | 5c177d3d163a745c916c0778 | fb39d67f09214772a45b4c106c3217d2 | 4584a5272200451e99922d175c16d766 |
    And Events
      | _id                      | banner_id                        | user_id                          | event_type | event_id                         | timestamp  | their_keywords         | campaign_id                      | our_keywords                                                                                                                                 | human_score | keywords                                                                                                                                     | publisher_id                     | event_value |
      | 5c17880b163a745c9170cc03 | GQySlgVvcKm1SXCQpuQe7EAWbEZYjaN5 | 4D9T4bnavSGt3QNpwCwlyUiIbdru1rdO | click      | 5iSLsNIdrVFoxW15kKVXE5agFzNmY9en | 1544778000 | { "accio:200142" : 1 } | 86dd0aa8e4914ec5b7584fdce3f96271 | { "platform" : [ "Ubuntu" ], "javascript" : [ true ], "device_type" : [ "Desktop" ], "interest" : [ "5072651" ], "browser" : [ "Firefox" ] } | 0.5         | { "platform" : [ "Ubuntu" ], "javascript" : [ true ], "interest" : [ "5072651" ], "device_type" : [ "Desktop" ], "browser" : [ "Firefox" ] } | e41006f625d446fb885b3d6d211f28e1 | null        |
      | 5c178919163a745c91714458 | a21dd579d30844cd8236edcd2718ba39 | 51a069dc98f19dcf80e2f3918ad4cc5c | click      | 77bb43987655be630e6e6bf8bcf0e0f0 | 1545044400 | { "accio:200417" : 1 } | 86dd0aa8e4914ec5b7584fdce3f96272 | { "platform" : [ "Ubuntu" ], "javascript" : [ true ], "device_type" : [ "Desktop" ], "interest" : [ "5072651" ], "browser" : [ "Firefox" ] } | 0.5         | { "platform" : [ "Ubuntu" ], "javascript" : [ true ], "interest" : [ "5072651" ], "device_type" : [ "Desktop" ], "browser" : [ "Firefox" ] } | e41006f625d446fb885b3d6d211f28e2 | null        |
      | 5c178919163a745c91714465 | a21dd579d30844cd8236edcd2718ba39 | 51a069dc98f19dcf80e2f3918ad4cc5c | view       | b79d956de9f301aa45d2ea65d825ad27 | 1545044400 | { "accio:200417" : 1 } | 86dd0aa8e4914ec5b7584fdce3f96273 | { "platform" : [ "Ubuntu" ], "javascript" : [ true ], "device_type" : [ "Desktop" ], "interest" : [ "5072651" ], "browser" : [ "Firefox" ] } | 0.5         | { "platform" : [ "Ubuntu" ], "javascript" : [ true ], "interest" : [ "5072651" ], "device_type" : [ "Desktop" ], "browser" : [ "Firefox" ] } | e41006f625d446fb885b3d6d211f28e3 | null        |
      | 5c178bc6163a745c917272a9 | b669dc6316f84c18b7f68e016cf6a382 | 51a069dc98f19dcf80e2f3918ad4cc5c | click      | 31dfbcb15b3bf71e2f3d5339ee07c756 | 1545048000 | { "accio:200417" : 1 } | 5ccf0db64680407c852e5fe34675ebaa | { "platform" : [ "Ubuntu" ], "javascript" : [ true ], "device_type" : [ "Desktop" ], "interest" : [ "5072651" ], "browser" : [ "Firefox" ] } | 0.5         | { "platform" : [ "Ubuntu" ], "javascript" : [ true ], "interest" : [ "5072651" ], "device_type" : [ "Desktop" ], "browser" : [ "Firefox" ] } | e41006f625d446fb885b3d6d211f28e4 | null        |
      | 5c178bc6163a745c917272c6 | b669dc6316f84c18b7f68e016cf6a382 | 51a069dc98f19dcf80e2f3918ad4cc5c | view       | 616f81980030b518de8d95d66182fb36 | 1545044400 | { "accio:200417" : 1 } | 5ccf0db64680407c852e5fe34675ebab | { "platform" : [ "Ubuntu" ], "javascript" : [ true ], "device_type" : [ "Desktop" ], "interest" : [ "5072651" ], "browser" : [ "Firefox" ] } | 0.5         | { "platform" : [ "Ubuntu" ], "javascript" : [ true ], "interest" : [ "5072651" ], "device_type" : [ "Desktop" ], "browser" : [ "Firefox" ] } | e41006f625d446fb885b3d6d211f28e5 | null        |

  @update
  Scenario: Campaign update
    Given I want to campaign update
    When I provide the data:
    """
      {
        "jsonrpc": "2.0",
        "id": "317ebce66492479997ee8908b0351306",
        "method": "campaign_update",
        "params": [
          {
            "campaign_id": "aaf274729d544f4ab1e6754091e75fc4",
            "advertiser_id": "e265864d4bfd47ccb4196e269cdf5fd3",
            "budget": 12345678901234,
            "max_cpc": 1234567890123,
            "max_cpm": 123456789012,
            "time_start": 1542420675,
            "time_end": 1576244160,
            "banners": [
              {
                "banner_id": "664a6ce44f854136b8030a5f0f9244a8",
                "banner_size": "728x90",
                "keywords": {
                  "type": 0
                }
              },
              {
                "banner_id": "835c45f7786a45b9ab7396531f10ad4b",
                "banner_size": "750x200",
                "keywords": {
                  "type": 0
                }
              },
              {
                "banner_id": "65fdd13f0c2b45a0b16ba5b31f5a9b51",
                "banner_size": "120x600",
                "keywords": {
                  "type": 0
                }
              },
              {
                "banner_id": "350f2a4fd42d4c3c9d3b351cb86a2a83",
                "banner_size": "160x600",
                "keywords": {
                  "type": 0
                }
              }
            ],
              "filters": {
                "exclude": {},
                "require": {
                  "interest": [
                    "100"
                      ]
                    }
              },
              "keywords": {
                  "open source": 1
              }
          }
        ]
      }
    """
    When I request resource
    Then the response should contain:
    """
      {
         "jsonrpc": "2.0",
         "id": "317ebce66492479997ee8908b0351306",
         "result": true
      }
    """
  @addevents
  Scenario: Add events
    Given I want to add events
    When I provide the data:
    """
      {
          "jsonrpc": "2.0",
          "id": "HkEjVJz7ATMItGIs98bUJt8PIXh3zZBa",
          "method": "add_events",
          "params": [
              {
                  "banner_id": "89Ev9isW66ogH367sZPPy4otfTP66g6s",
                  "event_type": "view",
                  "event_id": "607qhEK6RkqegQaDhpG2hRhqMgiSEvLs",
                  "timestamp": 1544778000,
                  "their_keywords": {
                      "accio:200142": 1
                  },
                  "our_keywords": {},
                  "human_score": 0,
                  "publisher_id": "Al72R1ROTnJAie0zfrAHVyahFTT0GflM",
                  "user_id": "kqAP8JoRtDRR1X7LSt78uZADCVq2JrW3"
              }
          ]
      }
    """
    When I request resource
    Then the response should contain:
    """
      {
         "jsonrpc": "2.0",
         "id": "HkEjVJz7ATMItGIs98bUJt8PIXh3zZBa",
         "result": true
      }
    """
  @no_calculations
  Scenario: Get payments
    Given I want to get payments
    When I provide the data:
    """
      {
       "jsonrpc": "2.0",
       "method": "get_payments",
       "id": "llj3wWF5Ze3vkQ3zDnB7GO7lA7j4Nda5",
       "params": [{"timestamp": 1544778000}]
      }
    """
    When I request resource
    Then the response should contain:
    """
      {"jsonrpc": "2.0",
       "id": "llj3wWF5Ze3vkQ3zDnB7GO7lA7j4Nda5",
        "error": {
                "message": "Payments not calculated yet.",
                "code": -32000}
      }
    """
  @forced
  Scenario: Get payments forced
    Given I want to get payments

    When I provide the data:
    """
      {
       "jsonrpc": "2.0",
       "method": "get_payments",
       "id": "llj3wWF5Ze3vkQ3zDnB7GO7lA7j4Nda5",
       "params": [{"timestamp": 1544778000}]
      }
    """
    And I execute payment calculation for timestamp "1544778000"
    And I request resource
    Then the response should contain:
    """
      {"jsonrpc": "2.0",
       "id": "llj3wWF5Ze3vkQ3zDnB7GO7lA7j4Nda5",
       "result": {"payments": []}
      }
    """

  @delete
  Scenario: Campaign delete
    Given I want to campaign delete
    When I provide the data:
    """
      {
          "jsonrpc": "2.0",
          "id": "ba23cfba9b0e4437b2c333cf6db7d534",
          "method": "campaign_delete",
          "params": [
              ""
          ]
      }
    """
    When I request resource
    Then the response should contain:
    """
      {
         "jsonrpc": "2.0",
         "id": "ba23cfba9b0e4437b2c333cf6db7d534",
         "result": true
      }
    """