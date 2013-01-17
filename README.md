PhingTasks
==========

* ErrBitTask - notfies errbit about deployments
Example:
```

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

    <for list="${build.projects}" param="build.project">
    <do>
         <phingcall target="bootstrap.export" />
    </do>
    </for>
```
