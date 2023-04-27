### Dedicated CLI server using Symfony messenger

The process manager offers a specialized mechanism to use a dedicated CLI server in multi server setups based on message queues and Symfony messenger. This mechanism executes the command triggered in the backend on a different server then the webserver where the Pimcore backend is running. 

**Caution: The message queue mechanismn currently only works for one single CLI server. Multiple CLI servers are not supported.**

To use the message queue mechanismn you have to configure the following settings:

```yaml
framework:
    messenger:
        transports:
            elements_process_manager:
                dsn: 'doctrine://default?queue_name=elements_process_manager'
```

In this example the default doctrine queue is used. In a real world scenario a real queuing system like RabbitMQ should be preferred.

Additionally you need to setup a running CLI server that is able to process the messages from the queue. On this server you need to start a message worker with the following command:

```bash
bin/console messenger:consume elements_process_manager
```

The CLI server should be configured to run the command in a loop. This can be done by using a cronjob, supervisor or a systemd service. Take a look at the [Symfony docs](https://symfony.com/doc/current/messenger.html) for details.
