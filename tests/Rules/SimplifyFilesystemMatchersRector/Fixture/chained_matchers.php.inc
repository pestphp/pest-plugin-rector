<?php

expect($path)->toBeFile()->toBeReadable();
expect($path)->toBeFile()->toBeWritable();
expect($path)->toBeDirectory()->toBeReadable();
expect($path)->toBeDirectory()->toBeWritable();

?>
-----
<?php

expect($path)->toBeReadableFile();
expect($path)->toBeWritableFile();
expect($path)->toBeReadableDirectory();
expect($path)->toBeWritableDirectory();

?>
