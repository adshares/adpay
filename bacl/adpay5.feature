@fixture.adpay.db
Feature: Campaign functionality

  Scenario: Adding data
   Given Campaigns
      |advertiser_id|banners|budget|campaign_id|filters|keywords|max_cpc|max_cpm|time_end|time_start|
      |ZOTyCh8YNHSxWed7Q8gf9tnWCN1tueLt||10000000000000|sRmYl0FtHUtAMPYG|{u'exclude': {u'user:lang': [u'it'], u'site:domain': [u'coinmarketcap.com', u'icoalert.com']}, u'require': {u'site:lang': [u'pl', u'en', u'it', u'jp'], u'user:gender': [u'pl'], u'device:os': [u'Linux', u'Windows']}}|{u'open source': 1}|1000|1000|1576147247|1542882034|
   And Banners
      |banner_id|campaign_id|
      |DiUnPzpmRZYtIici7bGjP5CYQ1WL6X7A|sRmYl0FtHUtAMPYG|
      |FT4XCuNLf34lnqIYEdMEshkqFD38jBmC|sRmYl0FtHUtAMPYG|
      |uNgBbMO6F3BTyyxqBqjhOhi1HdaiCwKz|sRmYl0FtHUtAMPYG|
      |vz9s0aHwXs9b9hTpCxqPyHAUENa2hPkB|sRmYl0FtHUtAMPYG|
   And Events
      |banner_id|campaign_id|event_id|event_type|human_score|our_keywords|publisher_id|their_keywords|timestamp|user_id|
      |DiUnPzpmRZYtIici7bGjP5CYQ1WL6X7A|sRmYl0FtHUtAMPYG|xJ5svowJ9kLziMu3NpqrEBHu8bUYHZPI|view|0|{}|tskUvNoOMUDWKeEuExtEC8yxQhAC99GP|{u'accio:200142': 1}|1544778000|gfhMqAi9U4gxHkp85ZFGoMtZCFCrpXXD|

   When I execute payment calculation for timestamp "1544778000"
   Then I have a payment round in DB timestamp "1544778000"
   And I have "0" payments for timestamp "1544778000" and "xJ5svowJ9kLziMu3NpqrEBHu8bUYHZPI"
