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

Please take a look at the "Update" section below if you want to execute migrations automatically when the bundle is updated.
 
# Update notes

## v3.1.0
* Implemented userfriendly execution mode for processes. After the update all MonitoringItems from the users will be hidden as they would otherwise appear in the active process list.
* Added Open Item  + JS Event Actions
* Custom Action must now implement the method getStorageData()
* Migrations are now executed by default

# Update
To update the bundle please use the following command:

```
composer update elements/process-manager-bundle; bin/console pimcore:bundle:update ElementsProcessManagerBundle
```

If you want that the migrations of the ProcessManagerBundle are automatically executed when you do a "composer update elements/process-manager-bundle;",  please add  
"Elements\\Bundle\\ProcessManagerBundle\\Composer::executeMigrationsUp" 
to your **project composer.json**
```
  "scripts": {
    "post-create-project-cmd": "Pimcore\\Composer::postCreateProject",
    "post-install-cmd": [
       //...,
    ],
    "post-update-cmd": [
       //...,
      "Elements\\Bundle\\ProcessManagerBundle\\Composer::executeMigrationsUp"
    ],
```

# Running with Pimcore < 5.4
With Pimcore 5.4 the location of static Pimcore files like icons has changed. In order to make this bundle work with Pimcore < 5.4, please add following rewrite rule to your .htaccess.
```
# rewrite rule for pre pimcore 5.4 core static files
RewriteRule ^bundles/pimcoreadmin/(.*) /pimcore/static6/$1 [PT,L]
```
