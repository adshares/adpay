
from adpay import db
from tests import db_test_case


class TestConfigure_db(db_test_case):

    def test_configure_db(self):
        dfr = db.configure_db()
        self.assertIsNotNone(dfr)

    def test_disconnect(self):
        db.disconnect()
        self.assertIsNone(db.MONGO_CONNECTION)
