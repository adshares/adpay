@fixture.adpay.db
Feature: Campaign functionality

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
          "id": "CM32fgseo1yCSm2VSH59uX6MOaprfDdM",
          "method": "campaign_delete2",
          "params": [
              "mNYpY5B9R8K9ahet"
          ]
      }
    """
    When I request resource
    Then the response should contain:
    """
      {
         "jsonrpc": "2.0",
         "id": "CM32fgseo1yCSm2VSH59uX6MOaprfDdM",
         "error":    {
            "message": "Method campaign_delete2 not found",
            "code": -32601
         }
      }
    """
  Scenario: Campaign delete id, params null
    Given I want to campaign delete
    When I provide the data:
    """
      {
          "jsonrpc": "2.0",
          "method": "campaign_delete",
          "params": [""
          ],
          "id": ""
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