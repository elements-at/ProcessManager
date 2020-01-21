# Installation

Add the bundle to your composer.json 
```
COMPOSER_MEMORY_LIMIT=3G composer require elements/process-manager-bundle
```

Run composer update: 
```
COMPOSER_MEMORY_LIMIT=3G composer update elements/process-manager-bundle
```
Go to the Extension manager in the Pimcore admin and enable/install the Bundle.
After the installation you have a config file located in /app/config/pimcore/plugin-process-manager.php

By default the processes are checked when the pimcore maintenance is executed. 
It is advisable to set up a extra cronjob, which monitors the script execution.

Just add the following command to your crontab (and set "executeWithMaintenance" to "false" in the config file)
```
*/5 * * * * php /home/my-project/www/bin/console process-manager:maintenance
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
