# Batch command execution

Sometimes it is required that we execute multiple commands sequentially. 

Depending on your needs there are different approaches. 

## Shell Script
The easiest one is to execute just one command after another. In this case you can just create a shell script 
and set it up as cron. 

Create a file. e.g /bin/Shell-Scripts/job-every-5-minutes.sh
```shell
#!/bin/sh

~/www/bin/console app:process-one
~/www/bin/console app:process-two

```

and add the /bin/Shell-Scripts/job-every-5-minutes.sh script to your crontab.

## Advanced aproach

In other cases you have to check if previous jobs were successfully and only execute
the next job if it was sucessfully. Or you want to start 2 jobs but job 3 has to wait until 1 and 2 are finished.

In this cases you can create a command and define your logic in the command. 
An example / starting point is here [Group Execution](/doc/sample/src/App/Command/GroupExecutionCommand.php).

This example just executes one job after another, but of course you can do more advanced things :-). 