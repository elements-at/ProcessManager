# ProcessManager

## Introduction

> The ProcessManager allows you to manage (define,execute...) arbitrary processes/commands in the Pimcore backend. 
You can display the execution progress of the script in the Admin interface and the user can view the details log information. 
In addition you can define "actions" - e.g.  a download of a file after the process has finished. Furthermore callback actions are available and the processes are monitored (you get an email if a process dies)

## Installation
```
{
    "require": {
        "pimcore-plugins/ProcessManager": "~1.0"
    },
    "repositories": [
        { "type": "composer", "url": "https://composer-packages.elements.at/" }
    ]
}
```

**Be careful, normally there's already a `require` node, so you need to add the new line at the bottom**     

Run composer update: 
`composer update`

### Development instance
Not jet available