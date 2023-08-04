# Contribution notes

PHPStan checks:
```shell
vendor/bin/phpstan analyse vendor/elements/process-manager-bundle -c vendor/elements/process-manager-bundle/phpstan.neon
```
PHP CS Fixer checks:

```shell
vendor/bin/php-cs-fixer fix dev/bundles/elements/process-manager-bundle/ --config vendor/elements/process-manager-bundle/.php-cs-fixer.dist.php --dry-run --diff
```