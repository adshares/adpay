Feature: API functionality

  Scenario: Adding campaigns
    Given I want to create or update a campaign using HTTP API
    When I provide the data
      """
      [
          {
              "campaign_id": "sRmYl0FtHUtAMPYG",
              "advertiser_id": "ZOTyCh8YNHSxWed7Q8gf9tnWCN1tueLt",
              "budget": 10000000000000,
              "max_cpc": 1000,
              "max_cpm": 1000,
              "time_start": 1542882034,
              "time_end": 1576147247,
              "banners": [
                  {
                      "banner_id": "DiUnPzpmRZYtIici7bGjP5CYQ1WL6X7A",
                      "banner_size": "728x90",
                      "keywords": {
                          "type": 0
                      }
                  },
                  {
                      "banner_id": "FT4XCuNLf34lnqIYEdMEshkqFD38jBmC",
                      "banner_size": "750x200",
                      "keywords": {
                          "type": 0
                      }
                  },
                  {
                      "banner_id": "uNgBbMO6F3BTyyxqBqjhOhi1HdaiCwKz",
                      "banner_size": "120x600",
                      "keywords": {
                          "type": 0
                      }
                  },
                  {
                      "banner_id": "vz9s0aHwXs9b9hTpCxqPyHAUENa2hPkB",
                      "banner_size": "160x600",
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
                          "pl",
                          "en",
                          "it",
                          "jp"
                      ],
                      "user:gender": [
                          "pl"
                      ],
                      "device:os": [
                          "Linux",
                          "Windows"
                      ]
                  }
              },
              "keywords": {
                  "open source": 1
              }
          }
      ]
      """
    And I make request
    Then The response should contain
    """
      {
         "jsonrpc": "2.0",
         "id": "ba23cfba9b0e4437b2c333cf6db7d534",
         "result": true
      }
    """