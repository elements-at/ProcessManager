# Migrations for configurations

Sometimes it is requrired to automatically update/create configurations for the process manager for deployments.

The ProcessManager provides a command which generates a migration for you.

```shell
php bin/console process-manager:migrations:generate 
```

Just execute the command and specify the configurations for which you want to generate a migration on the development
server.
After the generation you can add the file to your pipeline and let it execute at the regular deployments

The namespace and location can be changed with the following settings.

````yaml
elements_process_manager:
    configurationMigrationsDirectory: "%kernel.project_dir%/src/Migrations"
    configurationMigrationsNamespace: "App\Migrations"
``