from behave import fixture


@fixture
def adpay_server(context, **kwargs):
    context.server_url = 'http://dev.e11.click:8092'
