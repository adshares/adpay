<p align="center">
    <a href="https://adshares.net/" title="Adshares sp. z o.o." target="_blank">
        <img src="https://adshares.net/logos/ads.svg" alt="Adshares" width="100" height="100">
    </a>
</p>
<h3 align="center"><small>Adshares / Adpay</small></h3>
<p align="center">
    <a href="https://github.com/adshares/adpay/issues/new?template=bug_report.md&labels=Bug">Report bug</a>
    ·
    <a href="https://github.com/adshares/adpay/issues/new?template=feature_request.md&labels=New%20Feature">Request feature</a>
    ·
    <a href="https://github.com/adshares/adpay/wiki">Wiki</a>
</p>


Adpay is a back-end service for valuating events.
It accepts requests from [AdServer](https://github.com/adshares/adserver) internally.

## Quick Start

### Development

```
git clone https://github.com/adshares/adpay.git
cd adpay
composer install
composer dump-env dev
vi .env.local.php
php bin/console doctrine:migration:migrate
php bin/console server:run
```

### Production

```
git clone https://github.com/adshares/adpay.git
cd adpay
composer install --no-dev --optimize-autoloader
composer dump-env prod
vi .env.local.php
php bin/console doctrine:migration:migrate
```

Nginx configuration:
https://symfony.com/doc/current/setup/web_server_configuration.html#web-server-nginx

## Contributing

Please follow our [Contributing Guidelines](docs/CONTRIBUTING.md)

## Versioning

We use [SemVer](http://semver.org/) for versioning.
For the versions available, see the [tags on this repository](https://github.com/adshares/adpay/tags).

## Authors

* **[Adam Włodarkiewicz](https://github.com/m-pilarczyk)** - _Python programmer_
* **[Maciej Pilarczyk](https://github.com/m-pilarczyk)** - _PHP programmer_

See also the list of [contributors](https://github.com/adshares/adpay/contributors) who participated in this project.


## License

This work is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This work is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
[GNU General Public License](LICENSE) for more details.

You should have received a copy of the License along with this work.
If not, see <https://www.gnu.org/licenses/gpl.html>.
