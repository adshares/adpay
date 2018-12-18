@fixture.adpay.db
Feature: Campaign functionality

  Scenario: Adding data
    Given Campaigns
      |_id|  campaign_id|    advertiser_id|    time_start|    time_end|  filters|      keywords|      max_cpc|    max_cpm|    budget |
      |5c190764004ed896c12674dd|dasdffaq2    |    ad2d           |             0|999|{}            |{}          |             0|          0|          0|
      |5c190744004ed896aa2674dd|dasda22q2333    |    ad2d           |             0|999|{}            |{}          |             0|          0|          0|
    And Banners
      |  campaign_id |banner_id|banner_size|keywords|
      |dasdaq2    |fnawo47t |100x100    |{}      |
    And Events
      |event_id|    event_type|    user_id|    human_score|    publisher_id|    timestamp|    banner_id|    our_keywords|    their_keywords|    event_value |
      |a34     |click         |dasda3     |0.5            |n43aop          |500          |fnawo47t     |{}              |{}                |0.25            |
      |a35     |click         |dasda3     |0.5            |n43aop          |500          |fnawo47t     |{}              |{}                |0.25            |
      |a36     |click         |dasda3     |0.5            |n43aop          |500          |fnawo47t     |{}              |{}                |0.25            |
    When I execute payment calculation for timestamp "499"
    Then I have a payment round in DB timestamp "499"
    And I have payments for timestamp "499" and "a36"
