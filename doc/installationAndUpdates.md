# Installation

Add the bundle to your composer.json and enable/install it. 
```
COMPOSER_MEMORY_LIMIT=-1 composer require elements/process-manager-bundle
./bin/console pimcore:bundle:enable ElementsProcessManagerBundle
./bin/console pimcore:bundle:install ElementsProcessManagerBundle
```

After the installation you have a config file located in /app/config/pimcore/plugin-process-manager.php

By default the processes are checked when the pimcore maintenance is executed. 
It is advisable to set up a extra cronjob, which monitors the script execution.

Just add the following command to your crontab (and set "executeWithMaintenance" to "false" in the config file)
```
* * * * * php ~/www/bin/console process-manager:maintenance > /dev/null 2>&1
```

# Update
To update the bundle please use the following command:

```
composer update elements/process-manager-bundle; bin/console pimcore:bundle:update ElementsProcessManagerBundle
```

# Running with Pimcore < 5.4
With Pimcore 5.4 the location of static Pimcore files like icons has changed. In order to make this bundle work with Pimcore < 5.4, please add following rewrite rule to your .htaccess.
```
# rewrite rule for pre pimcore 5.4 core static files
RewriteRule ^bundles/pimcoreadmin/(.*) /pimcore/static6/$1 [PT,L]
```
