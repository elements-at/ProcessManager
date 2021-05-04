### Commands validator

The Commands validator is a Service which determines which Commands should be executable.
By default all Commands which implements the "Elements\Bundle\ProcessManagerBundle\ExecutionTrait"
will be available.

You can change or alter the behaviour by changing the Service.
If you want that all commands are available, change the $strategy to "all". Then no validation is done. 

```yaml
    Elements\Bundle\ProcessManagerBundle\Service\CommandsValidator:
        public: true
        arguments:
            $strategy: "default
            $whiteList: ["router:match","valis:command"]
            $blackList: ["process-manager:maintenance","do-no-execute:command"]
```