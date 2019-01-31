import socket
from threading import Thread

from twisted.internet import reactor

from adpay.iface.consts import SERVER_PORT
from adpay.iface.server import configure_iface


def before_all(context):

    # Configure our interface url
    host = socket.gethostbyname(socket.gethostname())
    port = SERVER_PORT
    context.interface_url = 'http://{0}:{1}'.format(host, port)

    # Start listening
    context.server = configure_iface(port=port)
    Thread(target=reactor.run, args=(False,)).start()


def after_all(context):

    # Shutdown interface
    context.server.stopListening()
    reactor.callLater(1, reactor.stop)
