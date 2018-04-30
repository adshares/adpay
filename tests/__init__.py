import json
import socket

from mock import MagicMock
from copy import deepcopy

import txmongo

from twisted.trial import unittest
from twisted.internet import defer, reactor
from twisted.internet.protocol import Protocol
from twisted.web.client import Agent
from twisted.internet.defer import succeed
from twisted.web.iweb import IBodyProducer
from twisted.web.http_headers import Headers

from zope.interface import implements

from adpay.iface import server as iface_server
from adpay.iface import consts as iface_consts
from adpay import db


class StringProducer(object):
    implements(IBodyProducer)

    def __init__(self, body):
        self.body = body
        self.length = len(body)

    def startProducing(self, consumer):
        consumer.write(self.body)
        return succeed(None)

    def pauseProducing(self):
        pass

    def stopProducing(self):
        pass


class ReceiverProtocol(Protocol):
    def __init__(self, finished):
        self.finished = finished
        self.body = []

    def dataReceived(self, databytes):
        self.body.append(databytes)

    def connectionLost(self, reason):
        self.finished.callback(''.join(self.body))


class DataTestCase(unittest.TestCase):
    _campaigns = [{'time_start': 1024765751, 'campaign_id': 'c_Marci', 'time_end': 2024765751,
                   'filters': {'exclude': [{'filter': {'args': 'Jong', 'type': '='}, 'keyword': 'Julianto'}],
                               'require': [{'filter': {'args': 'Jerry', 'type': '='}, 'keyword': 'Lea'}]},
                   'keywords': {'Rusty': 'Max', 'Malaclypse': 'Jin', 'Wendi': 'Kimberly', 'Sidney': 'Jane',
                                'Blair': 'Hans', 'Ravindran': 'Sekar'},
                   'banners': [{'keywords': {'Carolyn': 'Lyndon'}, 'banner_id': 'b_Juri', 'banner_size': '10x10'},
                               {'keywords': {'Sidney': 'Jane'}, 'banner_id': 'b_Shirley', 'banner_size': '25x25'},
                               {'keywords': {'Santa': 'Malaclypse'}, 'banner_id': 'b_Jan', 'banner_size': '50x50'}]},
                  {'time_start': 1024765751, 'campaign_id': 'c_Ti', 'time_end': 2024765751,
                   'filters': {'exclude': [{'filter': {'args': 'Niels', 'type': '='}, 'keyword': 'Tricia'}],
                               'require': [{'filter': {'args': 'Dale', 'type': '='}, 'keyword': 'Wolf'}]},
                   'keywords': {'Rusty': 'Max', 'Santa': 'Malaclypse', 'Sidney': 'Jane', 'Malaclypse': 'Jin',
                                'Jeffrey': 'Victoria', 'Saiid': 'Liber'},
                   'banners': [{'keywords': {'Ravindran': 'Sekar'}, 'banner_id': 'b_Sabrina', 'banner_size': '96x96'},
                               {'keywords': {'Ravindran': 'Sekar'}, 'banner_id': 'b_Jeffrey', 'banner_size': '16x16'},
                               {'keywords': {'Wendi': 'Kimberly'}, 'banner_id': 'b_Laurent', 'banner_size': '93x93'}]},
                  {'time_start': 1024765751, 'campaign_id': 'c_Dieter', 'time_end': 2024765751,
                   'filters': {'exclude': [{'filter': {'args': 'Jinny', 'type': '='}, 'keyword': 'Claire'}],
                               'require': [{'filter': {'args': 'Jiri', 'type': '='}, 'keyword': 'Hector'}]},
                   'keywords': {'Sidney': 'Jane', 'Rusty': 'Max', 'Santa': 'Malaclypse', 'Wendi': 'Kimberly',
                                'Ravindran': 'Sekar', 'Saiid': 'Liber'},
                   'banners': [{'keywords': {'Malaclypse': 'Jin'}, 'banner_id': 'b_Melinda', 'banner_size': '16x16'},
                               {'keywords': {'Blair': 'Hans'}, 'banner_id': 'b_Vincenzo', 'banner_size': '20x20'},
                               {'keywords': {'Sidney': 'Jane'}, 'banner_id': 'b_Roxanne', 'banner_size': '48x48'}]},
                  {'time_start': 1024765751, 'campaign_id': 'c_Johann', 'time_end': 2024765751,
                   'filters': {'exclude': [{'filter': {'args': 'Simon', 'type': '='}, 'keyword': 'Pierre'}],
                               'require': [{'filter': {'args': 'Stagger', 'type': '='}, 'keyword': 'Noemi'}]},
                   'keywords': {'Rusty': 'Max', 'Sidney': 'Jane', 'Santa': 'Malaclypse', 'Malaclypse': 'Jin',
                                'Jeffrey': 'Victoria', 'Saiid': 'Liber'},
                   'banners': [{'keywords': {'Sidney': 'Jane'}, 'banner_id': 'b_Nicolette', 'banner_size': '54x54'},
                               {'keywords': {'Sidney': 'Jane'}, 'banner_id': 'b_Claudia', 'banner_size': '32x32'},
                               {'keywords': {'Santa': 'Malaclypse'}, 'banner_id': 'b_Barrio', 'banner_size': '66x66'}]},
                  {'time_start': 1024765751, 'campaign_id': 'c_Annard', 'time_end': 2024765751,
                   'filters': {'exclude': [{'filter': {'args': 'Kate', 'type': '='}, 'keyword': 'Raja'}],
                               'require': [{'filter': {'args': 'Kikki', 'type': '='}, 'keyword': 'Nichael'}]},
                   'keywords': {'Sidney': 'Jane', 'Wendi': 'Kimberly', 'Saiid': 'Liber', 'Blair': 'Hans',
                                'Ravindran': 'Sekar', 'Carolyn': 'Lyndon'},
                   'banners': [{'keywords': {'Carolyn': 'Lyndon'}, 'banner_id': 'b_Matthias', 'banner_size': '21x21'},
                               {'keywords': {'Malaclypse': 'Jin'}, 'banner_id': 'b_Boyce', 'banner_size': '51x51'},
                               {'keywords': {'Carolyn': 'Lyndon'}, 'banner_id': 'b_Pradeep', 'banner_size': '56x56'}]},
                  {'time_start': 1024765751, 'campaign_id': 'c_Malloy', 'time_end': 2024765751,
                   'filters': {'exclude': [{'filter': {'args': 'Jesus', 'type': '='}, 'keyword': 'Cory'}],
                               'require': [{'filter': {'args': 'Tom', 'type': '='}, 'keyword': 'Shari'}]},
                   'keywords': {'Carolyn': 'Lyndon', 'Sidney': 'Jane', 'Santa': 'Malaclypse', 'Malaclypse': 'Jin',
                                'Wendi': 'Kimberly', 'Saiid': 'Liber'},
                   'banners': [{'keywords': {'Ravindran': 'Sekar'}, 'banner_id': 'b_Jacob', 'banner_size': '36x36'},
                               {'keywords': {'Jeffrey': 'Victoria'}, 'banner_id': 'b_Elisabeth',
                                'banner_size': '60x60'},
                               {'keywords': {'Ravindran': 'Sekar'}, 'banner_id': 'b_Connie', 'banner_size': '22x22'}]},
                  {'time_start': 1024765751, 'campaign_id': 'c_Holly', 'time_end': 2024765751,
                   'filters': {'exclude': [{'filter': {'args': 'Jacobson', 'type': '='}, 'keyword': 'Nelken'}],
                               'require': [{'filter': {'args': 'Suyog', 'type': '='}, 'keyword': 'Jesse'}]},
                   'keywords': {'Jeffrey': 'Victoria', 'Carolyn': 'Lyndon', 'Sidney': 'Jane', 'Malaclypse': 'Jin',
                                'Ravindran': 'Sekar', 'Saiid': 'Liber'},
                   'banners': [{'keywords': {'Malaclypse': 'Jin'}, 'banner_id': 'b_Harv', 'banner_size': '91x91'},
                               {'keywords': {'Blair': 'Hans'}, 'banner_id': 'b_Shean', 'banner_size': '46x46'},
                               {'keywords': {'Wendi': 'Kimberly'}, 'banner_id': 'b_Anderson', 'banner_size': '63x63'}]},
                  {'time_start': 1024765751, 'campaign_id': 'c_Martha', 'time_end': 2024765751,
                   'filters': {'exclude': [{'filter': {'args': 'Lukas', 'type': '='}, 'keyword': 'Cris'}],
                               'require': [{'filter': {'args': 'Patrice', 'type': '='}, 'keyword': 'Brenda'}]},
                   'keywords': {'Sidney': 'Jane', 'Jeffrey': 'Victoria', 'Wendi': 'Kimberly', 'Santa': 'Malaclypse',
                                'Malaclypse': 'Jin', 'Saiid': 'Liber'},
                   'banners': [{'keywords': {'Blair': 'Hans'}, 'banner_id': 'b_Rand', 'banner_size': '52x52'},
                               {'keywords': {'Ravindran': 'Sekar'}, 'banner_id': 'b_Loukas', 'banner_size': '61x61'},
                               {'keywords': {'Saiid': 'Liber'}, 'banner_id': 'b_Catherine', 'banner_size': '73x73'}]},
                  {'time_start': 1024765751, 'campaign_id': 'c_Mara', 'time_end': 2024765751,
                   'filters': {'exclude': [{'filter': {'args': 'Vance', 'type': '='}, 'keyword': 'Sandip'}],
                               'require': [{'filter': {'args': 'Gregor', 'type': '='}, 'keyword': 'Srikanth'}]},
                   'keywords': {'Rusty': 'Max', 'Carolyn': 'Lyndon', 'Sidney': 'Jane', 'Malaclypse': 'Jin',
                                'Ravindran': 'Sekar', 'Jeffrey': 'Victoria'},
                   'banners': [{'keywords': {'Ravindran': 'Sekar'}, 'banner_id': 'b_Ernie', 'banner_size': '64x64'},
                               {'keywords': {'Santa': 'Malaclypse'}, 'banner_id': 'b_Ahmed', 'banner_size': '97x97'},
                               {'keywords': {'Wendi': 'Kimberly'}, 'banner_id': 'b_Greg', 'banner_size': '21x21'}]},
                  {'time_start': 1024765751, 'campaign_id': 'c_Emmett', 'time_end': 2024765751,
                   'filters': {'exclude': [{'filter': {'args': 'Cary', 'type': '='}, 'keyword': 'Anderson'}],
                               'require': [{'filter': {'args': 'Doug', 'type': '='}, 'keyword': 'Kory'}]},
                   'keywords': {'Rusty': 'Max', 'Sidney': 'Jane', 'Santa': 'Malaclypse', 'Malaclypse': 'Jin',
                                'Wendi': 'Kimberly', 'Carolyn': 'Lyndon'},
                   'banners': [{'keywords': {'Rusty': 'Max'}, 'banner_id': 'b_Anatoly', 'banner_size': '84x84'},
                               {'keywords': {'Carolyn': 'Lyndon'}, 'banner_id': 'b_Pratapwant', 'banner_size': '72x72'},
                               {'keywords': {'Wendi': 'Kimberly'}, 'banner_id': 'b_Donal', 'banner_size': '93x93'}]}]
    _impressions = [{'keywords': {'Rusty': 'Max', 'Jeffrey': 'Victoria', 'Blair': 'Hans', 'Ravindran': 'Sekar',
                                  'Carolyn': 'Lyndon'}, 'user_id': 'user_Gregory', 'banner_id': 'b_Juri',
                     'publisher_id': 'pub_Ellen', 'paid_amount': 0.17}, {
                        'keywords': {'Rusty': 'Max', 'Blair': 'Hans', 'Carolyn': 'Lyndon', 'Santa': 'Malaclypse',
                                     'Saiid': 'Liber'}, 'user_id': 'user_Guy', 'banner_id': 'b_Shirley',
                        'publisher_id': 'pub_Ellen', 'paid_amount': 0.55}, {
                        'keywords': {'Rusty': 'Max', 'Blair': 'Hans', 'Ravindran': 'Sekar', 'Santa': 'Malaclypse',
                                     'Saiid': 'Liber'}, 'user_id': 'user_Gregory', 'banner_id': 'b_Jan',
                        'publisher_id': 'pub_Ellen', 'paid_amount': 0.77}, {
                        'keywords': {'Rusty': 'Max', 'Sidney': 'Jane', 'Malaclypse': 'Jin', 'Wendi': 'Kimberly',
                                     'Santa': 'Malaclypse'}, 'user_id': 'user_Aimee', 'banner_id': 'b_Sabrina',
                        'publisher_id': 'pub_Tovah', 'paid_amount': 0.21}, {
                        'keywords': {'Malaclypse': 'Jin', 'Ravindran': 'Sekar', 'Carolyn': 'Lyndon', 'Blair': 'Hans',
                                     'Saiid': 'Liber'}, 'user_id': 'user_Earle', 'banner_id': 'b_Jeffrey',
                        'publisher_id': 'pub_Lee', 'paid_amount': 0.47}, {
                        'keywords': {'Rusty': 'Max', 'Blair': 'Hans', 'Wendi': 'Kimberly', 'Santa': 'Malaclypse',
                                     'Saiid': 'Liber'}, 'user_id': 'user_Earle', 'banner_id': 'b_Laurent',
                        'publisher_id': 'pub_Jared', 'paid_amount': 0.0038}, {
                        'keywords': {'Rusty': 'Max', 'Malaclypse': 'Jin', 'Ravindran': 'Sekar', 'Sidney': 'Jane',
                                     'Santa': 'Malaclypse'}, 'user_id': 'user_Gregory', 'banner_id': 'b_Melinda',
                        'publisher_id': 'pub_Lee', 'paid_amount': 0.61}, {
                        'keywords': {'Rusty': 'Max', 'Carolyn': 'Lyndon', 'Sidney': 'Jane', 'Santa': 'Malaclypse',
                                     'Saiid': 'Liber'}, 'user_id': 'user_Gregory', 'banner_id': 'b_Vincenzo',
                        'publisher_id': 'pub_Ellen', 'paid_amount': 0.49}, {
                        'keywords': {'Jeffrey': 'Victoria', 'Rusty': 'Max', 'Wendi': 'Kimberly', 'Sidney': 'Jane',
                                     'Saiid': 'Liber'}, 'user_id': 'user_Gregory', 'banner_id': 'b_Roxanne',
                        'publisher_id': 'pub_Sanjib', 'paid_amount': 0.93}, {
                        'keywords': {'Blair': 'Hans', 'Ravindran': 'Sekar', 'Sidney': 'Jane', 'Wendi': 'Kimberly',
                                     'Saiid': 'Liber'}, 'user_id': 'user_Gregory', 'banner_id': 'b_Nicolette',
                        'publisher_id': 'pub_Jared', 'paid_amount': 0.73}]

    def load_campaigns(self):
        for campaign in self.campaigns:
            db.utils.update_campaign(campaign)

            for banner in campaign['banners']:
                banner['campaign_id'] = campaign['campaign_id']
                yield db.utils.update_banner(banner)


class DBTestCase(DataTestCase):
    @defer.inlineCallbacks
    def setUp(self):
        self.conn = yield db.get_mongo_connection()
        self.db = yield db.get_mongo_db()

        yield db.configure_db()
        self.timeout = 5

    @defer.inlineCallbacks
    def tearDown(self):
        yield self.conn.drop_database(self.db)
        yield db.disconnect()


try:
    import mongomock

    class MongoMockTestCase(DataTestCase):

        def setUp(self):

            self.campaigns = deepcopy(self._campaigns)
            self.impressions = deepcopy(self._impressions)

            db.MONGO_CONNECTION = None

            self.connection = mongomock.MongoClient()
            self.connection.disconnect = MagicMock()
            self.connection.disconnect.return_value = True

            self.mock_lazyMongoConnectionPool = MagicMock()
            self.mock_lazyMongoConnectionPool.return_value = self.connection
            self.patch(txmongo, 'lazyMongoConnectionPool', self.mock_lazyMongoConnectionPool)

            def mock_create_index(obj, index, *args, **kwargs):
                obj.old_create_index([i[1][0] for i in index.items()], *args, **kwargs)

            mongomock.Collection.old_create_index = mongomock.Collection.create_index
            mongomock.Collection.create_index = mock_create_index

            def mock_find(obj, *args, **kwargs):
                with_cursor = False
                if 'cursor' in kwargs.keys():
                    with_cursor = True
                    del kwargs['cursor']

                if 'sort' in kwargs.keys():
                    kwargs['sort'] = kwargs['sort']['orderby']

                cursor = obj.old_find(*args, **kwargs)

                if with_cursor:
                    return cursor, ([], None)
                else:
                    return cursor

            def cursor_len(cur_instance):
                return cur_instance.count()

            mongomock.collection.Cursor.__len__ = cursor_len

            mongomock.Collection.old_find = mongomock.Collection.find
            mongomock.Collection.find = mock_find

            def mock_find_one(obj, *args, **kwargs):
                kwargs['limit'] = 1
                cursor = obj.old_find(*args, **kwargs)
                if cursor.count() > 0:
                    return cursor[0]
                return None

            mongomock.Collection.find_one = mock_find_one

        def tearDown(self):
            mongomock.Collection.create_index = mongomock.Collection.old_create_index
            mongomock.Collection.find = mongomock.Collection.old_find

    db_test_case = MongoMockTestCase
except ImportError:
    db_test_case = DBTestCase


class WebTestCase(db_test_case):

    @defer.inlineCallbacks
    def setUp(self):
        yield super(WebTestCase, self).setUp()

        self.port = iface_server.configure_iface()
        self.client = Agent(reactor)

    @defer.inlineCallbacks
    def tearDown(self):
        yield super(WebTestCase, self).tearDown()

        self.port.stopListening()

    @defer.inlineCallbacks
    def get_response(self, method, params=None):
        post_data = StringProducer(json.dumps({
            "jsonrpc": "2.0",
            "id": "test_hit",
            "method": method,
            "params": params
        }))

        host = socket.gethostbyname(socket.gethostname())

        response = yield self.client.request('POST',
                                             'http://{0}:{1}'.format(host, iface_consts.SERVER_PORT),
                                             Headers({'content-type': ['text/plain']}),
                                             post_data)

        finished = defer.Deferred()
        response.deliverBody(ReceiverProtocol(finished))
        data = yield finished
        defer.returnValue(json.loads(data) if data else None)
