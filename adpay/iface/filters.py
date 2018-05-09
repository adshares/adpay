class Filter(object):
    """
    Filter base class for filtering keywords.
    """
    NAME = None
    NESTED_FILTERS = False

    def __init__(self, filter_arg):
        self.filter_arg = filter_arg

    def is_valid(self, value):
        raise NotImplementedError()


class AndFilter(Filter):
    NAME = 'and'
    NESTED_FILTERS = True

    def is_valid(self, value):
        for filter_obj in self.filter_arg:
            if not filter_obj.is_valid(value):
                return False
        return True


class OrFilter(Filter):
    NAME = "or"
    NESTED_FILTERS = True

    def is_valid(self, value):
        for filter_obj in self.filter_arg:
            if filter_obj.is_valid(value):
                return True
        return False


class EqualFilter(Filter):
    NAME = '='

    def is_valid(self, value):
        return self.filter_arg == value


class GreaterEqualFilter(Filter):
    NAME = ">="

    def is_valid(self, value):
        return value >= self.filter_arg


class LessEqualFilter(Filter):
    NAME = "<="

    def is_valid(self, value):
        return value <= self.filter_arg


class LessFilter(Filter):
    NAME = '<'

    def is_valid(self, value):
        return value < self.filter_arg


class GreaterFilter(Filter):
    NAME = '>'

    def is_valid(self, value):
        return value > self.filter_arg


REGISTERED_FILTERS = [
    AndFilter,
    OrFilter,
    EqualFilter,
    GreaterEqualFilter,
    LessEqualFilter,
    LessFilter,
    GreaterFilter
]
FILTERS_NAMES_DICT = dict([(cls.NAME, cls) for cls in REGISTERED_FILTERS])


def json2filter(json_data):
    """
    Convert nested json type filter to object filter e.g.

    {type: and,
     args: [ {type: type1, args: ''}, ... ]}

    :param json_data: JSON data containing filter *'type'*
    :return: Filter object
    """
    filter_type = json_data.get('type')
    if filter_type not in FILTERS_NAMES_DICT:
        return

    args = json_data.get('args')
    if args is None:
        return

    filter_class = FILTERS_NAMES_DICT[filter_type]
    if filter_class.NESTED_FILTERS:
        args = [json2filter(arg) for arg in args]
    return filter_class(args)


def validate_filters(filters_dict, keywords):
    """
    Validate required and excluded keywords.

    :param filters_dict: Required and excluded keywords
    :param keywords: Keywords being tested.
    :return: True or False
    """
    for filter_json in filters_dict.get('require', []):
        keyword = filter_json['keyword']
        if keyword not in keywords:
            return False

        filter_obj = json2filter(filter_json['filter'])
        if not filter_obj.is_valid(keywords.get(keyword)):
            return False

    for filter_json in filters_dict.get('exclude', []):
        keyword = filter_json['keyword']
        if keyword not in keywords:
            continue

        filter_obj = json2filter(filter_json['filter'])
        if filter_obj.is_valid(keywords.get(keyword)):
            return False
    return True
