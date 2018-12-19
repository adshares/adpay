from behave import fixture


@fixture
def adpay_server(context, **kwargs):
    context.server_url = 'http://localhost:8092'
