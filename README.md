PhingTasks
==========

* ErrBitTask - notfies errbit about deployments
Example:
```

    <taskdef name="errbit" classname="ErrBitTask" classpath="${lib.path}/tasks" />
    <errbit
        host = "${errbit.host}"
        apikey = "${errbit.apikey}"
        repository = "${svn.repo}" 
        revision = "${svn.rev}"
        username = "${user}"
    />
```
* ForTask - inline For task
Example:
```

    <taskdef name="for" classname="ForTask" classpath="${lib.path}/tasks" />
    <for list="${build.projects}" param="build.project">
    <do>
         <phingcall target="bootstrap.export" />
    </do>
    </for>
```
