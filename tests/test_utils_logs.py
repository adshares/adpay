import logging.config

from mock import MagicMock, mock_open, patch
from twisted.trial import unittest

from adpay.utils import consts as utils_consts, logs as utils_logs


class TestContribLogs(unittest.TestCase):

    def setUp(self):
        """
        Reset logging

        :return:
        """
        logging.shutdown()
        reload(logging)

    def tearDown(self):
        """
        Reset logging

        :return:
        """
        logging.shutdown()
        reload(logging)

    def test_configure_setup(self):
        """
        1. Test default setup
        2. Test custom setup (from json file)
        :return:
        """
        # Test default setup
        utils_logs.setup()
        self.assertEqual(logging.getLogger().getEffectiveLevel(), getattr(logging, utils_consts.LOG_LEVEL))

        # Custom json config
        json_config = """
                       {
                          "version":1,
                          "disable_existing_loggers":false,
                          "formatters":{
                            "simple":{
                              "format":"%(asctime)s - %(name)s - %(levelname)s - %(message)s"
                            }
                          },
                          "handlers":{
                            "console":{
                              "class":"logging.StreamHandler",
                              "level":"INFO",
                              "formatter":"simple",
                              "stream":"ext://sys.stdout"
                            }
                          },
                          "root":{
                            "level":"INFO",
                            "handlers":[
                              "console"
                            ]
                          }
                        }
                      """

        # Test custom setup
        utils_logs.utils_consts.LOG_CONFIG_JSON_FILE = True
        with patch("os.path.exists", MagicMock(return_value=True)):
            with patch("__builtin__.open", mock_open(read_data=json_config)):
                utils_logs.setup()

            self.assertEqual(logging.getLogger().getEffectiveLevel(), logging.INFO)
