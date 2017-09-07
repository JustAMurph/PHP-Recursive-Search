<?php

/**
 * A simple class for recursively searching for files or directories in PHP.
 *
 * Makes use of RecursiveDirectoryIterator and RecursiveIteratorIterator.
 *
 * Class RecursiveSearch
 * @see RecursiveDirectoryIterator
 * @see RecursiveIteratorIterator
 */
class RecursiveSearch {

    /**
     *
     * The default allowed extension to be search for.
     *
     * @var array
     */
    private $allowedExtensions = ['jpg','png','gif', 'pdf', 'doc', 'csv', 'xml', 'json'];

    /**
     *
     * Default starting directory for searches.
     *
     * @var string
     */
    private $defaultDirectory;

    /**
     *
     * The base pattern for file and directory matching.
     *
     * THIS IS NOT A FINAL REGEX. It is missing a final delimiter: '/'
     *
     * File regex may append a condition for extensions if there is not an extension in the search.
     *
     * @var string
     */
    private $basePattern = '/[a-z0-9-. ]*(?:%s)(?:[a-z0-9-])*';

    /**
     * RecursiveSearch constructor.
     * @param string $directory Where to start the search
     */
    public function __construct($directory = __DIR__)
    {
        $this->defaultDirectory = $directory;
    }

    /**
     *
     * Recursively searches for files and directories.
     *
     * $allowedExtensions are only applied to files.
     *
     * @param $name
     * @param array $allowedExtensions
     * @return array Index 0 is a list of matching files. Index 1 is a list of matching directories.
     */
    public function find($name, $allowedExtensions = []) {

        list($fileName, $startingDirectory) = $this->splitName($name);

        $directoryIterator = new \RecursiveDirectoryIterator($startingDirectory, \FilesystemIterator::FOLLOW_SYMLINKS | \FilesystemIterator::SKIP_DOTS | \RecursiveIteratorIterator::SELF_FIRST);
        $fileIterator = new \RecursiveIteratorIterator($directoryIterator, RecursiveIteratorIterator::SELF_FIRST);

        $result = [
            'files' => [],
            'directories' => []
        ];

        list($filePattern, $directoryPattern) = $this->getSearchRegexs($fileName, $allowedExtensions);

        foreach( $fileIterator as $file){
            if ($file->isDir() && preg_match($directoryPattern, $file->getBasename())) {
                $result['directories'][] = $file->getBasename();
            } else if ($file->isFile() && preg_match($filePattern, $file->getBasename()) ) {
                $result['files'][] = $file->getBasename();
            }
        }

        return [$result['files'], $result['directories']];
    }

    /**
     *
     * Returns a regex for file matching and directory matching.
     *
     * The file matching regex may include conditionals for extensions.
     *
     * @param string $searchString String to base the Regex off.
     * @param array $allowedExtensions Override the default allowed extensions.
     * @return array Index 0 is a Regex for matching files. Index 1 is a Regex for matching directories.
     */
    private function getSearchRegexs($searchString, $allowedExtensions = []) {

        if (empty($allowedExtensions)){
            $allowedExtensions = $this->allowedExtensions;
        }

        $endPattern = '';
        if (!empty($allowedExtensions)) {
            $imageExtensionPattern = '.(?:' . implode('|', $allowedExtensions) . ')+$';
            if (!preg_match('/' . $imageExtensionPattern . '/', $searchString)) {
                $endPattern .= $imageExtensionPattern;
            }
        }

        $filePattern = sprintf($this->basePattern . '%s/', $searchString, $endPattern);
        $directoryPattern = sprintf($this->basePattern . '/', $searchString);

        return [$filePattern, $directoryPattern];
    }

    /**
     *
     * Splits a $searchString into a basename and a startingDirectory.
     *
     * $searchString = "/contents/images/file"; Returns ['contents/images/', 'file'];
     * $searchString = 'file'; Returns [$this->defaultDirectory, 'file'];
     * $searchSTring = 'file.jpg'; Returns [$this->defaultDirectory, 'file.jpg'];
     *
     *
     * @param string $searchString String to split into parts.
     * @return array Index 0 is a filename, Index 1 is a starting directory.
     */
    private function splitName($searchString) {
        $startingDirectory = $this->defaultDirectory;

        $pathInfo = pathinfo($searchString);
        if (!empty($pathInfo['dirname'])) {
            $startingDirectory = realpath($pathInfo['dirname']);
        }

        $fileName = $pathInfo['basename'];

        return [$fileName, $startingDirectory];
    }

    /**
     * Static method for searching for files and directories quickly without instantiating a class.
     *
     * @param string $searchString String to search for.
     * @param array $allowedExtensions The extensions which are allowed to be found.
     * @param string $startingDirectory What directory to start the search from.
     * @return array Index 0 contains a list of matching files, Index 1 contains a list of matching directories.
     * @see RecursiveSearch::find()
     */
    public static function search($searchString, $allowedExtensions = [], $startingDirectory = __DIR__) {
        $search = new RecursiveSearch($startingDirectory);
        return $search->find($searchString, $allowedExtensions);
    }

    /**
     * Static method for searching for files quickly without instantiating a class.
     *
     * @param string $searchString String to search for.
     * @param array $allowedExtensions The extensions which are allowed to be found.
     * @param string $startingDirectory What directory to start the search from.
     * @return array List of matching files.
     * @see RecursiveSearch::find()
     */
    public static function searchFiles($searchString, $allowedExtensions = [], $startingDirectory = __DIR__) {
        list($files, $directories) = static::search($searchString, $allowedExtensions, $startingDirectory);
        return $files;
    }

    /**
     * Static method for searching for directories quickly without instantiating a class.
     *
     * @param string $searchString String to search for.
     * @param string $startingDirectory What directory to start the search from.
     * @return array List of matching directories.
     * @see RecursiveSearch::find()
     */
    public static function searchDirectories($searchString, $startingDirectory = __DIR__) {
        list($files, $directories) = static::search($searchString, [], $startingDirectory);
        return $directories;
    }
}