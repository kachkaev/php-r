# php-r [![Build Status](https://travis-ci.org/kachkaev/php-r.png?branch=master)](https://travis-ci.org/kachkaev/php-r)

PHPR (or php-r) is a library that provides ability to run R scripts from PHP. Available [as a bundle](https://github.com/kachkaev/KachkaevPHPRBundle) for Symfony2 users.

The idea is based on invoking a command-line version of R and exchanging messages with this external process.
Integration with server-based implementation of R can be easily implemented on demand as the library architecture supports that.

It is possible to both run all R code in one batch and interactively exchange commands with R interpreter.

# Usage
## Option 1: all R code in one batch
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

Method ```run()``` is always called in a clean scope of R variables, i.e. the following usage will result an R error:

```
echo $r->run("x = 100")
echo "\n=====\n"
echo $r->run("x * x")
```

PHP output:
```
> x = 100
=====
> x * x
Error: object 'x' not found
```

## Option 2: interactive exchange of data
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

PHP output:
```
> x = 100
> x * x
[1] 10000
```

The process is synchronous, i.e. if R code sent to ```write()``` implies some complex computations, PHP will wait until they are finished.
 
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

It is strictly necessary to keep commands complete. The following example will result a critical error, and R process will be terminated.

```
$rProcess->write('x = 1 + (');
// IncompleteRCommandException
```

### Separate access to input / output / errors for each R command
To avoid parsing a mix of R input, output and errors, the result of script execution can be accessed separately:
```
$rProcess = $r->createInteractiveProcess();

$rProcess->write(<<<EOF
x = 1
y = 2
x
y
EOF;
    );
    
$rProcess->write(<<<EOF
x + 41
x + xxx
x + y
EOF;
    );
    
echo $rProcess->getLastWriteInput();
// x + 41
// x + xxx
// x + y

echo $rProcess->getLastWriteOutput();
// 42
// 
// [1] 3

echo $rProcess->hasLastWriteErrors();
// true

echo $rProcess->getLastWriteErrorCount();
// 1

$errors = $rProcess->getLastWriteErrors();
echo $errors[0]->getErrorMessage()
// object 'xxx' not found
echo $errors[0]->getCommand()
// x + xxx

echo $rProcess->getAllInput();
echo $rProcess->getAllOutput();
echo $rProcess->hasErrors();
echo $rProcess->getErrorCount();
echo $rProcess->getErrors();
```

Passing ```true``` to ```getXXXInput/getXXXOutput/getXXXResult``` splits strings into arrays, where each element corresponds to a single command:

```
$rProcess = $r->createInteractiveProcess();
$rProcess->write(<<<EOF
x = 
1 + 1
y
x
EOF;
    );
$inputAsArray = $rProcess->getLastWriteInput(true);
// ['x = {newline} 1 + 1', 'y', 'x']
$outputAsArray = $rProcess->getLastWriteOutput(true);
// ['', null, '2']
$resultAsArray = $rProcess->getLastWriteResult(true);
// [
//   ['x = {newline} 1 + 1', '', null],
//   ['y', null, 'Error: object \'y\' not found'],
//   ['x', '2', null]
// ]
```

### Sensitivity to R errors
If it is necessary to make sure that a sequence of R commands is running with no errors, the process can be made sensible to them.
```RErrorsException``` will be thrown on ```write()```:

```
$rProcess->setErrorSensitive(true);
$rProcess->write('x = 1 + missingVariable');
// RErrorsException 
```

This is the same as:
```
$rProcess->setErrorSensitive(false);
$rProcess->write('x = 1 + missingVariable');
if ($rProcess->hasLastWriteErrors()) {
    throw new RErrorsException($rProcess->getLastWriteInput(true), $rProcess->getLastWriteOutput(true), $rProcess->getLastWriteErrors());
}
```

R-related errors and the exception thrown are not critical; the same instance of R process can be still used after they occur. If last input contains multiple commands, and several of them cause errors, ```RErrorsException``` will have the complete list:

```
$allErrors = $rErrorsException->getErrors();
if (count ($allErrors) > 1) {
    $secondError = $allErrors[1];
    echo $secondError->getErrorMessage();
}
```

### Interpreting R output 

To ease the interpretation of R output, ```ROutputInterpreter``` can be used:

```
use Kachkaev\PHPR\ROutputInterpreter;

$rOutputInterpreter = new ROutputInterpreter();
$rProcess->write('21 + 21');
var_dump($rProcess->getLastWriteOutput());
// string(6) "[1] 42"
var_dump($rOutputInterpreter->singleNumber($rProcess->getLastWriteOutput()));
// int(42)
```

See PHPdoc annotations to classes for more details.

# License

MIT. See [LICENSE](LICENSE).
