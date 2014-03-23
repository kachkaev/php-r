# php-r [![Build Status](https://travis-ci.org/kachkaev/php-r.png?branch=master)](https://travis-ci.org/kachkaev/php-r)

PHPR (or php-r) is a library that provides ability to run R scripts from PHP.

The idea is based on invoking a command-line version of R and exchanging messages with this external process.
Integration with server-based implementation of R can be easily implemented on demand as the library architecture supports that.

It is possible to both run all R code at once and write commands to R process interactively.

# Usage

```
use Kachkaev\PHPR\RCore;
use Kachkaev\PHPR\Engine\CommandLineREngine;

$r = new RCore(new CommandLineREngine('/usr/bin/R'));

$result = $r->run(<<<EOF
x = 1
y = 2
x + y
x + z
x - y
EOF;
    );

echo $result;
```

PHP output:
```
> x = 1
> y = 2
> x + y
[1] 3
> x + z
Error: object 'z' not found 
> x - y
[1] -1
```

Method ```run()``` is always called in a new empty scope of R variables, i.e. the following usage will result an R error:

```
echo $r->run("x = 100")
echo "=====\n"
echo $r->run("x * x")
```

PHP output:
```
> x = 100
=====
> x * x
Error: object 'x' not found
```

To exchange commands with a single R process interactively, another approach should be used:
```
use Kachkaev\PHPR\RCore;
use Kachkaev\PHPR\Engine\CommandLineREngine;

$r = new RCore(new CommandLineREngine('/usr/bin/R'));
$rProcess = $r->createInteractiveProcess();

$rProcess->write('x = 100');
// Do something else
$rProcess->write('x * x');

echo $rProcess->getAllResult();
```

```
> x = 100
> x * x
[1] 100000
```

Multiple R commands can be written in one ```write()```, they can be multi-line too:

```
$rProcess->write(<<<EOF
x = 1
y = 10 + (x 
+ x / 2)
z = 42
EOF;
    );
```

However, it is strictly necessary to keep commands complete. The following example will result a critical error, and R process will be terminated losing all variables in its scope.

```
$rProcess->write('x = 1 + (');
// IncompleteRCommandException
```

If it is necessary to make sure that the R process is running with no errors, it can be made sensible to them.
RErrorsException will be thrown on ```write()```:

```
$rProcess->setErrorSensitive(true);
$rProcess->write('x = 1 + missingVariable');
// RErrorsException: 
```

Such exception is not critical, the same instance of RProcess can be still used. If last-write input contained multiple commands, and several of them caused errors, RErrorsException will have the complete list:

```
$allErrors = $rErrorsException->getErrors();
$secondError = $allErrors[1];
echo $secondError->getErrorMessage();
```

To avoid extensive parsing of R input, output and errors, the result of script execution can be accessed separately:
```
TO BE ADDED
```



## License

MIT. See [LICENSE](LICENSE).
