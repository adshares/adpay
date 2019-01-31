Feature: Campaign functionality

  Scenario: Get payments
    Given I want to get payments
    When I provide the data:
    """
      {
       "jsonrpc": "2.0",
       "method": "get_payments",
       "id": "jqZOU0bzSvf3xS2Z9VpwqnULlKrqNv1J",
       "params": [{"timestamp": 1544778000}]
      }
    """
    And I execute payment calculation for timestamp "1544778000"
    And I request resource
    Then the response should contain:
    """
      {
         "jsonrpc": "2.0",
         "id": "jqZOU0bzSvf3xS2Z9VpwqnULlKrqNv1J",
         "result": {"payments": []}
      }
    """
  Scenario: Get payments -32700
    Given I want to get payments
    When I provide the data:
    """
      {
       "jsonrpc": "2.0",
       "method": "debug_force_payment_recalculation",
       "id": "5c147c58163a745c91261940",
       "params":
      }
    """
    And I request resource
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
  Scenario: Get payments -32601
    Given I want to get payments
    When I provide the data:
    """
      {
        "jsonrpc": "2.0",
        "method": "get_payments2",
        "id": "llj3wWF5Ze3vkQ3zDnB7GO7lA7j4Nda5",
        "params": [{"timestamp": 1544778000}]
      }
    """
    When I request resource
    Then the response should contain:
    """
    {
      "jsonrpc": "2.0",
      "id": "llj3wWF5Ze3vkQ3zDnB7GO7lA7j4Nda5",
      "error":    {
        "message": "Method get_payments2 not found",
        "code": -32601
      }
    }
    """
  Scenario: Get payments -32602
    Given I want to get payments
    When I provide the data:
    """
    {
      "jsonrpc": "2.0",
      "method": "get_payments",
      "id": "5c137146163a745c91aed341",
      "params": []
    }
    """
    When I request resource
    Then the response should contain:
    """
    {
      "jsonrpc": "2.0",
      "id": "5c137146163a745c91aed341",
      "error":    {
        "message": "jsonrpc_get_payments() takes exactly 2 arguments (1 given)",
        "code": -32602
      }
    }
    """
  Scenario: Get payments -32603
    Given I want to get payments
    When I provide the data:
    """
    {
      "jsonrpc": "2.0",
      "method": "get_payments",
      "id": "llj3wWF5Ze3vkQ3zDnB7GO7lA7j4Nda5",
      "params": [{}]
    }
    """
    When I request resource
    Then the response should contain:
    """
      {
        "jsonrpc": "2.0",
        "id": "llj3wWF5Ze3vkQ3zDnB7GO7lA7j4Nda5",
        "error":    {
          "message": "Property timestamp is required.",
          "code": -32010
        }
      }
    """
  Scenario: Get payments id null
    Given I want to get payments
    When I provide the data:
    """
      {
       "jsonrpc": "2.0",
       "method": "get_payments",
       "id": "",
       "params": [{"timestamp": 1544778000}]
      }
    """
    When I request resource
    Then the response should contain:
    """
      {
         "jsonrpc": "2.0",
         "id": "",
         "result": {"payments": []}
      }
    """
