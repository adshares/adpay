Deployment
==========

Getting the code
----------------

Check out code from repo:

.. code-block:: sh

    git clone https://github.com/adshares/adpay.git

Requirements
------------

For a `tl;dr` solution, please check `Quick requirements install`_ for Ubuntu 18.04.

Python
~~~~~~


You'll need Python 2.7. Other versions are untested. It may be already installed on your system, you can check it by typing:

.. code-block:: sh

    python --version

or

.. code-block:: sh

    python2.7 --version


You should get something like this:

.. code-block:: sh

    Python 2.7.15rc1

If you don't see it, install Python from your system repo.

PIP (Python package manager)
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

You will also need PIP. It may be already installed on your system, you can check it by typing:

.. code-block:: sh

    pip --version

You should get something like this:

.. code-block:: sh

    pip 18.1 from /home/ubuntu/.local/lib/python2.7/site-packages/pip (python 2.7)

If you don't see it, install PIP from your system repo.

Pipenv
~~~~~~

Once you have PIP operational, install pipenv package:

.. code-block:: sh

    pip install pipenv

Project Python dependencies
~~~~~~~~~~~~~~~~~~~~~~~~~~~

You can now use pipenv to install project dependencies.

.. code-block:: sh

    pipenv install

or for development:

.. code-block:: sh

    pipenv install --dev

Additional requirements
~~~~~~~~~~~~~~~~~~~~~~~

This project uses `MongoDB <https://www.mongodb.com/>`_ as a database backend. Installation instructions can be found on the net.

.. _quick_req_install:

Quick requirements install
--------------------------

The following scripts were designed for Ubuntu 18.04.

.. code-block:: bash


    git clone https://github.com/adshares/adpay.git
    cd adpay
    bash scripts/pre-build.sh
    bash scripts/pre-install.sh

Start the server
----------------

To run the server, simply execute in project directory:

.. code-block:: sh

    pipenv run python daemon.py

Other deployment configurations
-------------------------------

Supervisord
~~~~~~~~~~~

Example `Supervisor <http://supervisord.org/>`_ config file:

.. code-block:: ini

    [program:adpay]
    directory=%(ENV_ADPAY_ROOT)s
    command=pipenv run python daemon.py
    pidfile=%(ENV_ADPAY_ROOT)s/adpay.pid
    stdout_logfile=%(ENV_ADPAY_ROOT)s/adpay.log
    stdout_logfile_maxbytes=50MB
    stdout_logfile_backups=10
    redirect_stderr=true

ENV_ADPAY_ROOT is the path to the project directory.

Docker
~~~~~~

Example `Dockerfile <https://www.docker.com/>`_:


.. code-block:: docker

    FROM ubuntu:18.04
    RUN apt-get -y install git
    RUN git clone https://github.com/adshares/adpay.git /adpay
    WORKDIR /adpay

    # Install dependencies
    RUN bash scripts/pre-build.sh
    RUN bash scripts/pre-install.sh

    # Build project
    RUN bash scripts/build.sh

    ENTRYPOINT ["pipenv run python daemon.py"]
