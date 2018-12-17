@fixture.adpay.server
Feature: Campaign functionality

  Scenario: Adding data
    Given A campaign
      |  campaign_id|    advertiser_id|    time_start|    time_end|                                   filters|      keywords|    banners|    max_cpc|    max_cpm|    budget |
      |dasdaq2    |    ad2d           |             0|9999999999999|{"require": {}, "exclude": {}}            |{}          |          []|          0|          0|          0|
    And Banners
      |banner_id|banner_size|keywords|
      |fnawo47t |100x100    |{}      |
    And Events
      |event_id|    event_type|    user_id|    human_score|    publisher_id|    timestamp|    banner_id|    our_keywords|    their_keywords|    event_value |
      |a34     |click         |dasda3     |0.5            |n43aop          |500          |fnawo47t     |{}              |{}                |0.25            |
