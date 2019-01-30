@fixture.adpay.db
Feature: Campaign functionality
  @event
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
          "id": "gtcDEoejSgua7fbQVKiGx2VkDbFJonEw",
          "method": "add_events",
          "params": [
              {
                  "banner_id": "k9A7zuiQPZI0kRaIYKaFaesTnfXwEFaF",
                  "event_type": ,
                  "event_id": "SWQEVeW6abj9mLMdrAfsjCYBu0amfBka",
                  "timestamp": 1544778000,
                  "their_keywords": {
                      "accio:200142": 1
                  },
                  "our_keywords": {},
                  "human_score": 0,
                  "publisher_id": "az0a1P3E5BJZhAhl1PDMqoqcoDbm7o6N",
                  "user_id": "4jiFmdzlGGWozqIsaDbDqoxT5PCu2ic9"
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
  Scenario: Add events -32600
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
    Scenario: Add events -32601
    Given I want to add events
    When I provide the data:
    """
      {
          "jsonrpc": "2.0",
          "id": "gtcDEoejSgua7fbQVKiGx2VkDbFJonEw",
          "method": "add_events2",
          "params": [
              {
                  "banner_id": "k9A7zuiQPZI0kRaIYKaFaesTnfXwEFaF",
                  "event_type": "view",
                  "event_id": "SWQEVeW6abj9mLMdrAfsjCYBu0amfBka",
                  "timestamp": 1544778000,
                  "their_keywords": {
                      "accio:200142": 1
                  },
                  "our_keywords": {},
                  "human_score": 0,
                  "publisher_id": "az0a1P3E5BJZhAhl1PDMqoqcoDbm7o6N",
                  "user_id": "4jiFmdzlGGWozqIsaDbDqoxT5PCu2ic9"
              }
          ]
      }
    """
    When I request resource
    Then the response should contain:
    """
      {
         "jsonrpc": "2.0",
         "id": "gtcDEoejSgua7fbQVKiGx2VkDbFJonEw",
         "error":    {
            "message": "Method add_events2 not found",
            "code": -32601
         }
      }
    """

  Scenario: Add events -32603
    Given I want to add events
    When I provide the data:
    """
      {
        "jsonrpc": "2.0",
        "id": "gz0qkJJxfoldKaHZAfoXzubvoMqxqvzZ",
        "method": "add_events",
        "params": [
          {
            "banner_id": "sxEIWuMifrt0frU2g451EiOocRoc0Arp",

            "event_id": "zZ8BWtuZK0TYepLUepgVguOYI5xIgqC9",
            "timestamp": 1544778000,
            "their_keywords": {
              "accio:200142": 1
              },
            "our_keywords": {},
            "human_score": 0,
            "publisher_id": "WgnFNOboQnuUis3uDgo6UPGkiT1uUTbU",
            "user_id": "PM7ED5bXniwnsZhV9H2aA8eHhUfznhl9"
          }
        ]
      }
    """
    When I request resource
    Then the response should contain:
    """
    {
      "jsonrpc": "2.0",
      "id": "gz0qkJJxfoldKaHZAfoXzubvoMqxqvzZ",
      "error":    {
        "message": "Property event_type is required.",
        "code": -32603
      }
    }
    """
  Scenario: Add events params null
    Given I want to add events
    When I provide the data:
    """
      {
        "jsonrpc": "2.0",
        "id": "gz0qkJJxfoldKaHZAfoXzubvoMqxqvzZ",
        "method": "add_events",
        "params": []
      }
    """
    When I request resource
    Then the response should contain:
    """
    {
      "jsonrpc": "2.0",
      "id": "gz0qkJJxfoldKaHZAfoXzubvoMqxqvzZ",
      "result": true
    }
    """
  Scenario: Add events id null
    Given I want to add events
    When I provide the data:
    """
      {
          "jsonrpc": "2.0",
          "id": "",
          "method": "add_events",
          "params": [
              {
                  "banner_id": "${#Project#baner_id_1}",
                  "event_type": "click",
                  "event_id": "${#Project#event_id}",
                  "timestamp": 1544778000,
                  "their_keywords": {
                      "accio:200142": 1
                  },
                  "our_keywords": {},
                  "human_score": 0,
                  "publisher_id": "${#Project#publisher_id}",
                  "user_id": "${#Project#user_id}"
              }
          ]
      }
    """
    When I request resource
    Then the response should contain:
    """
    {
       "jsonrpc": "2.0",
       "id": "",
       "result": true
    }
    """
  Scenario: Add events -32010
    Given I want to add events
    When I provide the data:
    """
      {
          "jsonrpc": "2.0",
          "id": "jqZOU0bzSvf3xS2Z9VpwqnULlKrqNv1J",
          "method": "add_events",
          "params": [
              {
                  "event_type": "click",
                  "event_id": "7oPvkaFLYRbHy8TPwETE1ifWdMHaIR3e",
                  "timestamp": 1544778000,
                  "their_keywords": {},
                  "our_keywords": {},
                  "human_score": 1.0,
                  "publisher_id": "3thnm97ruzIOA34riaSpJosZdwfMIWzH",
                  "user_id": "3qJjQW8befAMMCgFHkweEa4LCSVkOTf7"
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
          "message": "Property banner_id is required.",
          "code": -32010
       }
    }
    """