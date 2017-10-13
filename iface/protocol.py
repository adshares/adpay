import jsonobject

class KeywordFilterObject(jsonobject.JsonObject):
    keyword = jsonobject.StringProperty()
    filter = jsonobject.DictProperty()


class RequireExcludeListObject(jsonobject.JsonObject):
    require = jsonobject.ListProperty(KeywordFilterObject)
    exclude = jsonobject.ListProperty(KeywordFilterObject)


class BannerObject(jsonobject.JsonObject):
    banner_id = jsonobject.StringProperty()
    banner_size = jsonobject.StringProperty()
    keywords = jsonobject.DictProperty()


class CamapaignObject(jsonobject.JsonObject):
    campaign_id = jsonobject.StringProperty()
    advertiser_id = jsonobject.StringProperty()
    time_start = jsonobject.IntegerProperty()
    time_end = jsonobject.IntegerProperty()
    filters = jsonobject.ObjectProperty(RequireExcludeListObject)
    keywords = jsonobject.DictProperty()
    banners = jsonobject.ListProperty(BannerObject)


class EventObject(jsonobject.JsonObject):
    #TODO: timestamp for recalculating?
    event_id = jsonobject.StringProperty()
    banner_id = jsonobject.StringProperty()
    keywords = jsonobject.DictProperty()
    publisher_id = jsonobject.StringProperty()
    user_id = jsonobject.StringProperty()


class PaymentsRequest(jsonobject.JsonObject):
    timestamp = jsonobject.IntegerProperty()


class SinglePaymentResponse(jsonobject.JsonObject):
    event_id = jsonobject.IntegerProperty()
    amount = jsonobject.FloatProperty()


class PaymentsResponse(jsonobject.JsonObject):
    payments = jsonobject.ListProperty(SinglePaymentResponse)

