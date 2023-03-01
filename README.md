# Verification of reconciliation acts

[![GitHub Actions][ico-github-actions]][link-github-actions]
[![GitHub Issues][ico-github-issues]][link-github-issues]
[![Software License][ico-license]](LICENSE)

<!-- START doctoc generated TOC please keep comment here to allow auto update -->
<!-- DON'T EDIT THIS SECTION, INSTEAD RE-RUN doctoc TO UPDATE -->


- [Installation](#installation)
  - [Development](#development)
- [License](#license)

<!-- END doctoc generated TOC please keep comment here to allow auto update -->

## Installation
### Development
```bash
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    laravelsail/php81-composer:latest \
    composer install --ignore-platform-reqs

./vendor/bin/sail up -d --build --force-recreate --remove-orphans
# -d               - Detached mode: Run containers in the background.
# --build          - Build images before starting containers.
# --force-recreate - Recreate containers even if their configuration and image haven't changed.
# --remove-orphans - Remove containers for services not defined in the Compose file.
```

## License

The MIT License ([MIT](https://opensource.org/licenses/MIT)). Please see [License File](LICENSE) for more information.

<!-- Icons -->

[ico-license]: https://img.shields.io/github/license/mashape/apistatus.svg

[ico-github-actions]: https://github.com/finagin/saldo/workflows/GitHub%20Actions/badge.svg?branch=develop
[link-github-actions]: https://github.com/finagin/saldo/actions

[ico-github-issues]: https://img.shields.io/github/issues/finagin/saldo
[link-github-issues]: https://github.com/finagin/saldo/issues
