@fixture.adpay.db
Feature: Campaign functionality

#  Background:
#    Given Campaigns
#      | campaign_id                      | budget         | max_cpc | max_cpm | time_start | time_end   | filters                                                                                                                                                                                                                       |
#      | dGlxjZZHtUh4lJsMdGlxjZZHtUh4lJsM | 9876543210     | 600     | 600     | 1542882034 | 1576147247 | { "exclude" : { "user:lang" : [ "it" ], "site:domain" : [ "coinmarketcap.com", "icoalert.com" ] }, "require" : { "site:lang" : [ "pl", "en", "it", "jp" ], "user:gender" : [ "pl" ], "device:os" : [ "Linux", "Windows" ] } } |
#      | k24vw5A4EmuMuWQSk24vw5A4EmuMuWQS | 11109876543    | 700     | 700     | 1542882034 | 1576147247 | { "exclude" : { "user:lang" : [ "it" ], "site:domain" : [ "coinmarketcap.com", "icoalert.com" ] }, "require" : { "site:lang" : [ "pl", "en", "it", "jp" ], "user:gender" : [ "pl" ], "device:os" : [ "Linux", "Windows" ] } } |
#      | dcUOVNOMkO902SrFdcUOVNOMkO902SrF | 1312111098765  | 900     | 900     | 1542882034 | 1576147247 | { "exclude" : { "user:lang" : [ "it" ], "site:domain" : [ "coinmarketcap.com", "icoalert.com" ] }, "require" : { "site:lang" : [ "pl", "en", "it", "jp" ], "user:gender" : [ "pl" ], "device:os" : [ "Linux", "Windows" ] } } |
#      | tt5ts00oAIxNaEyKtt5ts00oAIxNaEyK | 14131211109076 | 1000    | 1000    | 1542882034 | 1576147247 | { "exclude" : { "user:lang" : [ "it" ], "site:domain" : [ "coinmarketcap.com", "icoalert.com" ] }, "require" : { "site:lang" : [ "pl", "en", "it", "jp" ], "user:gender" : [ "pl" ], "device:os" : [ "Linux", "Windows" ] } } |
#    And Banners
#      | banner_id                        | campaign_id                      |
#      | 0402cd91762c4002aa6df8c00e5483cb | dGlxjZZHtUh4lJsMdGlxjZZHtUh4lJsM |
#      | 0cbaf8c74b04406d97d6d90b950c9ea7 | dGlxjZZHtUh4lJsMdGlxjZZHtUh4lJsM |
#      | a9e6117efdc84f819c580d41f862cf9d | k24vw5A4EmuMuWQSk24vw5A4EmuMuWQS |
#      | ee34923d8acf4c4a8ff57fd37d7335ea | k24vw5A4EmuMuWQSk24vw5A4EmuMuWQS |
#      | fb39d67f09214772a45b4c106c3217d2 | k24vw5A4EmuMuWQSk24vw5A4EmuMuWQS |
#    And Events
#      | banner_id                        | user_id                          | event_type | event_id                         | timestamp  | their_keywords         | campaign_id                      | our_keywords                                                                                                                                 | human_score | keywords                                                                                                                                     | publisher_id                     | event_value |
#      | 0402cd91762c4002aa6df8c00e5483cb | 4D9T4bnavSGt3QNpwCwlyUiIbdru1rdO | click      | 5iSLsNIdrVFoxW15kKVXE5agFzNmY9en | 1544778000 | { "accio:200142" : 1 } | 86dd0aa8e4914ec5b7584fdce3f96271 | { "platform" : [ "Ubuntu" ], "javascript" : [ true ], "device_type" : [ "Desktop" ], "interest" : [ "5072651" ], "browser" : [ "Firefox" ] } | 0.5         | { "platform" : [ "Ubuntu" ], "javascript" : [ true ], "interest" : [ "5072651" ], "device_type" : [ "Desktop" ], "browser" : [ "Firefox" ] } | e41006f625d446fb885b3d6d211f28e1 | null        |
#      | 0cbaf8c74b04406d97d6d90b950c9ea7 | 51a069dc98f19dcf80e2f3918ad4cc5c | click      | 77bb43987655be630e6e6bf8bcf0e0f0 | 1545044400 | { "accio:200417" : 1 } | 86dd0aa8e4914ec5b7584fdce3f96272 | { "platform" : [ "Ubuntu" ], "javascript" : [ true ], "device_type" : [ "Desktop" ], "interest" : [ "5072651" ], "browser" : [ "Firefox" ] } | 0.5         | { "platform" : [ "Ubuntu" ], "javascript" : [ true ], "interest" : [ "5072651" ], "device_type" : [ "Desktop" ], "browser" : [ "Firefox" ] } | e41006f625d446fb885b3d6d211f28e2 | null        |
#      | a9e6117efdc84f819c580d41f862cf9d | 51a069dc98f19dcf80e2f3918ad4cc5c | view       | b79d956de9f301aa45d2ea65d825ad27 | 1545044400 | { "accio:200417" : 1 } | 86dd0aa8e4914ec5b7584fdce3f96273 | { "platform" : [ "Ubuntu" ], "javascript" : [ true ], "device_type" : [ "Desktop" ], "interest" : [ "5072651" ], "browser" : [ "Firefox" ] } | 0.5         | { "platform" : [ "Ubuntu" ], "javascript" : [ true ], "interest" : [ "5072651" ], "device_type" : [ "Desktop" ], "browser" : [ "Firefox" ] } | e41006f625d446fb885b3d6d211f28e3 | null        |
#      | ee34923d8acf4c4a8ff57fd37d7335ea | 51a069dc98f19dcf80e2f3918ad4cc5c | click      | 31dfbcb15b3bf71e2f3d5339ee07c756 | 1545048000 | { "accio:200417" : 1 } | 5ccf0db64680407c852e5fe34675ebaa | { "platform" : [ "Ubuntu" ], "javascript" : [ true ], "device_type" : [ "Desktop" ], "interest" : [ "5072651" ], "browser" : [ "Firefox" ] } | 0.5         | { "platform" : [ "Ubuntu" ], "javascript" : [ true ], "interest" : [ "5072651" ], "device_type" : [ "Desktop" ], "browser" : [ "Firefox" ] } | e41006f625d446fb885b3d6d211f28e4 | null        |
#      | fb39d67f09214772a45b4c106c3217d2 | 51a069dc98f19dcf80e2f3918ad4cc5c | view       | 616f81980030b518de8d95d66182fb36 | 1545044400 | { "accio:200417" : 1 } | 5ccf0db64680407c852e5fe34675ebab | { "platform" : [ "Ubuntu" ], "javascript" : [ true ], "device_type" : [ "Desktop" ], "interest" : [ "5072651" ], "browser" : [ "Firefox" ] } | 0.5         | { "platform" : [ "Ubuntu" ], "javascript" : [ true ], "interest" : [ "5072651" ], "device_type" : [ "Desktop" ], "browser" : [ "Firefox" ] } | e41006f625d446fb885b3d6d211f28e5 | null        |

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
  Scenario: Campaign update code -32700
    Given I want to campaign update
    When I provide the data:
    """
      {
          "jsonrpc": "2.0",
          "id": "xkaWsa2f8Mtq8QKAiWP0fEs5Zc566Dqd",
          "method": "campaign_update",
          "params": [
              {
                  "campaign_id": "Ioma0EwaYqqnPunX",
                  "advertiser_id": "WCxzAyHo3Cm8BXfD1hKHSa3wmFPaW6k6",
                  "budget": 10000000000000,
                  "max_cpc": 1000,
                  "max_cpm": 1000,
                  "time_start": 1542882034,
                  "time_end": 1576147247,
                  "banners": [
                      {
                          "banner_id": "68O9646jdsxdpFprMf8XmiGjQOflg7p3",
                          "banner_size": "728x90",
                          "keywords": {
                              "type": 0
                          }
                      }
                      {
                          "banner_id": "WOzZAwA8mnXW0q1Vvm8FBvrhqDNhela0",
                          "banner_size": "750x200",
                          "keywords": {
                              "type": 0
                          }
                      }
                      }
                  ],
                  "filters": {
                      "exclude": {
                          "site:domain": [
                              "coinmarketcap.com",
                              "icoalert.com"
                          ],
                          "user:lang": [
                              "it"
                          ]
                      },
                      "require": {
                          "site:lang": [
                              "jp"
                          ],
                          "user:gender": [
                              "pl"
                          ],
                          "device:os": [
                              "Windows"
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
         "id": null,
         "error":    {
            "message": "Parse error",
            "code": -32700
         }
      }
    """
   Scenario: Campaign update code -32600
    Given I want to campaign update
    When I provide the data:
    """
     {
            "jsonrpc": "2.0",
            "id": "PW4ungvlh2DnuRRZh0Emdir9Z1gzKlZ5",
            "method1": "campaign_update",
            "params": [
                {
                    "campaign_id": "6scUNqDN48mz0kV5",
                    "advertiser_id": "V1dBxWYu7Iq2vtBP7tQANmvn6IdIFd61",
                    "budget": 10000000000000,
                    "max_cpc": 1000,
                    "max_cpm": 1000,
                    "time_start": 1542882034,
                    "time_end": 1576147247,
                    "banners": [
                        {
                            "banner_id": "t5ci0EE6TNVM4gCQwp2XPm5ZwTx9x2lA",
                            "banner_size": "728x90",
                            "keywords": {
                                "type": 0
                            }
                        }
                    ],
                    "filters": {
                        "exclude": {
                            "site:domain": [
                                "coinmarketcap.com",
                                "icoalert.com"
                            ],
                            "user:lang": [
                                "it"
                            ]
                        },
                        "require": {
                            "site:lang": [
                                "pl"
                            ],
                            "user:gender": [
                                "pl"
                            ],
                            "device:os": [
                                "Linux"
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
         "id": "PW4ungvlh2DnuRRZh0Emdir9Z1gzKlZ5",
         "error":    {
            "message": "Invalid method type",
            "code": -32600
         }
      }
    """
   Scenario: Campaign update code -32601
    Given I want to campaign update
    When I provide the data:
    """
      {
        "jsonrpc": "2.0",
        "id": "ckf7bpsHoqfSSvyuRdK8Ez8pA49uJedB",
        "method1": "campaign_update2",
        "params": [
            {
                "campaign_id": "Zwrgq6qPdBjrP6lz",
                "advertiser_id": "TSCgd2b4u26YRY9nLKvcpwACvB1YkLyN",
                "budget": 10000000000000,
                "max_cpc": 1000,
                "max_cpm": 1000,
                "time_start": 1542882034,
                "time_end": 1576147247,
                "banners": [
                    {
                        "banner_id": "cxgHoFZe9oVPOGgmKT4I5mHDqHh5Xzxc",
                        "banner_size": "728x90",
                        "keywords": {
                            "type": 0
                        }
                    }
                ],
                "filters": {
                    "exclude": {
                        "site:domain": [
                            "coinmarketcap.com",
                            "icoalert.com"
                        ],
                        "user:lang": [
                            "it"
                        ]
                    },
                    "require": {
                        "site:lang": [
                            "pl"
                        ],
                        "user:gender": [
                            "pl"
                        ],
                        "device:os": [
                            "Linux"
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
         "id": "ckf7bpsHoqfSSvyuRdK8Ez8pA49uJedB",
         "error":    {
            "message": "Method campaign_update2 not found",
            "code": -32601
         }
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
  Scenario: Add events code -32700
    Given I want to add events
    When I provide the data:
    """
      {
          "jsonrpc": "2.0",
          "id": "hRe54TQ9oUWq4q7FzxG8NhJbT3Rrfnqs",
          "method": "add_events",
          "params": [
              {
                  "banner_id": "76DV2JJl8EhcyuLb3gSQeSXLSTINEpfF",
                  "event_type": "click",
                  "event_id": "uRiCHgd3zGQ3ADmKwlH4WmU0VFItIban",
                  "timestamp": 1544778000,
                  "their_keywords": {
                      "accio:200142": 1
                  }
                  "our_keywords": {},
                  "human_score": 0,
                  "publisher_id": "uapqGoguUYmUWE7bWuL7aJ3X9YmC7M5n",
                  "user_id": "bqINQy1Nag45iM4Z5u6CHQ9K075MEqFH"
              }
          ]
      }
    """
    When I request resource
    Then the response should contain:
    """
        {
           "jsonrpc": "2.0",
           "id": null,
           "error":    {
              "message": "Parse error",
              "code": -32700
           }
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
       "id": "HkEjVJz7ATMItGIs98bUJt8PIXh3zZBa",
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
  Scenario: Get payments error
    Given I want to get payments
    When I provide the data:
    """
      {
       "jsonrpc": "2.0",
       "method": "get_payments",
       "id": "",
       "params": [{"timestamp": 1544770000}]
      }
    """
    When I request resource
    Then the response should contain:
    """
      {
         "jsonrpc": "2.0",
         "id": "",
         "error":    {
            "message": "Payments not calculated yet.",
            "code": -32000
         }
      }
    """



  @delete
  Scenario: Campaign delete
    Given I want to campaign delete
    When I provide the data:
    """
      {
          "jsonrpc": "2.0",
          "id": "CAL75YKS1FLmG1rLdchyOvtVQXLZkbnf",
          "method": "campaign_delete",
          "params": [
              "xDRgl2OXl5Qfm4JS"
          ]
      }
    """
    When I request resource
    Then the response should contain:
    """
      {
      "jsonrpc": "2.0",
      "id": "CAL75YKS1FLmG1rLdchyOvtVQXLZkbnf",
      "result": true
      }
    """
  Scenario: Campaign delete code -32700
    Given I want to campaign delete
    When I provide the data:
    """
      {
          "jsonrpc": "2.0",
          "id": "${#Project#id}",
          "method": "campaign_delete",
          "params": [
              "${#Project#campaign_id}"
      }
    """
    When I request resource
    Then the response should contain:
    """
      {
         "jsonrpc": "2.0",
         "id": null,
         "error":    {
            "message": "Parse error",
            "code": -32700
         }
      }
    """
  Scenario: Campaign delete code -32600
    Given I want to campaign delete
    When I provide the data:
    """
      {
          "jsonrpc": "2.0",
          "id": "VxKeEvXssuvG0Ue300YB8KXwMwho5gA9",
          "method1": "campaign_delete",
          "params": [
              "eDirTrpOrNhhGwSf"
          ]
      }
    """
    When I request resource
    Then the response should contain:
    """
     {
         "jsonrpc": "2.0",
         "id": "VxKeEvXssuvG0Ue300YB8KXwMwho5gA9",
         "error":    {
            "message": "Invalid method type",
            "code": -32600
         }
     }
    """
   Scenario: Campaign delete code -32601
    Given I want to campaign delete
    When I provide the data:
    """
      {
          "jsonrpc": "2.0",
          "id": "${#Project#id}",
          "method": "campaign_delete2",
          "params": [
              "${#Project#campaign_id}"
          ]
      }
    """
    When I request resource
    Then the response should contain:
    """
      {
         "jsonrpc": "2.0",
         "id": "VxKeEvXssuvG0Ue300YB8KXwMwho5gA9",
         "error":    {
            "message": "Method campaign_delete2 not found",
            "code": -32601
         }
      }
    """
  Scenario: Campaign delete code -32602
    Given I want to campaign delete
    When I provide the data:
    """
      {
          "jsonrpc": "2.0",
          "method": "campaign_delete",
          "params": [
              1,
              2
          ],
          "id": 1
      }
    """
    When I request resource
    Then the response should contain:
    """
    """

