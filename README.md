# PHP-Recursive-Search
A library for recursively searching for files in PHP.

## Usage 

##### Class Based
```PHP
require('RecursiveSearch.php');

// Class-based Usage
$search = new RecursiveSearch();
list($files, $directories) = $search->find('filename');

```
##### Static Access

_Get all files and directories._
```PHP
list($files, $directories) = RecursiveSearch::search('filename');
```
_Get all files_
```PHP
$files = RecursiveSearch::searchFiles('filename');
```

_Get all directories_
```PHP
$directories = RecursiveSearch::searchDirectories('filename');
```

_Option list for static methods_
```PHP
public static function search($searchString, $allowedExtensions = [], $startingDirectory = __DIR__)
public static function searchFiles($searchString, $allowedExtensions = [], $startingDirectory = __DIR__)
public static function searchDirectories($searchString, $startingDirectory = __DIR__)
```

### Defaults

##### Search Directories
Search is started within the __DIR__ location unless otherwise declared.

You may declare the search directory in the constructor of the class.

```PHP
$search = new RecursiveSearch('mydir');
```

##### Allowed Extensions
By default only certain file extensions are permitted.

```PHP
private $allowedExtensions = ['jpg','png','gif', 'pdf', 'doc', 'csv', 'xml', 'json'];

```

You may specify your own allowed extensions by passing a second argument. 

```PHP
$search = new RecursiveSearch();
list($files, $directories) = $search->find('filename', ['php', 'rb']);
```
