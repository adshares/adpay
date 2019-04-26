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
        "id": "317ebce66492479997ee8908b0351306",
        "method": "campaign_update",
        "params": [
          {
            "campaign_id": "aaf274729d544f4ab1e6754091e75fc4",
            "advertiser_id": "e265864d4bfd47ccb4196e269cdf5fd3",
            "budget": ,
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
          "id": "317ebce66492479997ee8908b0351306",
          "method": "campaign_update2",
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
         "error":    {
            "message": "Method campaign_update2 not found",
            "code": -32601
         }
      }
    """
  Scenario: Campaign update code -32603
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
            "budget": "",
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
         "error":    {
            "message": "u'' not of type (<type 'int'>, <type 'long'>)",
            "code": -32010
         }
      }
    """
  Scenario: Campaign update banner null
    Given I want to campaign update
    When I provide the data:
    """
      {
        "jsonrpc": "2.0",
        "id": "317ebce66492479997ee8908b0351306",
        "method": "campaign_update",
        "params": [
          {
            "campaign_id": "",
            "advertiser_id": "",
            "budget": 12345678901234,
            "max_cpc": 1234567890123,
            "max_cpm": 123456789012,
            "time_start": 1542420675,
            "time_end": 1576244160,
            "banners": [
              {
                "banner_id": "",
                "banner_size": ""

              }
            ]
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
  Scenario: Campaign update budget, max_cpc, max_cpm, time_start, time_end null
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
            "budget": 0,
            "max_cpc": 0,
            "max_cpm": 0,
            "time_start": 0,
            "time_end": 0,
            "banners": [
              {
                "banner_id": "",
                "banner_size": ""

              }
            ]
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

  Scenario: Campaign update code -32010
    Given I want to campaign update
    When I provide the data:
    """
      {
          "jsonrpc": "2.0",
          "id": "jqZOU0bzSvf3xS2Z9VpwqnULlKrqNv1J",
          "method": "campaign_update",
          "params": [
              {
                  "advertiser_id": "Buf8Fe4Z7HF5M3G1kHczWbIKdQfCz8ZD",
                  "budget": 1000000000,
                  "max_cpc": 100,
                  "max_cpm": 100,
                  "time_start": 1542882034,
                  "time_end": 1676147247,
                  "banners": [],
                  "filters": {
                      "exclude": {},
                      "require": {}
                  },
                  "keywords": {}
              }
          ]
      }
    """
    When I request resource
    Then the response should contain:
    """
      {
         "jsonrpc": "2.0",
         "id": "jqZOU0bzSvf3xS2Z9VpwqnULlKrqNv1J",
         "error":    {
            "message": "Property campaign_id is required.",
            "code": -32010
         }
      }
    """