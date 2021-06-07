<?php
declare(strict_types=1);

namespace Neighborhoods\Bakery;

class Baker implements BakerInterface
{
    const PHP_FILE_EXTENSION_REGEX = '/.php$/';

    public function bake() : BakerInterface
    {
        try {
            $this->log('');
            $this->purgeOpcacheDNSFileCaches();
            $this->opcacheCompile();
        } catch (\Throwable $throwable) {
            $this->log('>> Error:');
            $this->log($throwable->getMessage());
            $this->log($throwable->getTraceAsString());
        }

        return $this;
    }

    public function purgeOpcacheDNSFileCaches() : BakerInterface
    {
        $this->log(">> Ensuring that the Opcache DNS file cache is purged...");
        $this->purgeOpcacheDNSFileCache();
        $this->log('>> Success.');

        return $this;
    }

    protected function opcacheCompile() : BakerInterface
    {
        $this->log(">> Asking Opcache to compile the Composer authoritative classmap...");
        $this->opcacheCompileFiles($this->getComposerAuthoritativeClassmap());
        $this->log('>> Success.');

        $this->log('>> Asking Opcache to compile the DI container cache.');
        $this->opcacheCompileFiles($this->getContainerCacheFiles());
        $this->log('>> Success.');

        $this->log('>> Your Protean product has been successfully baked. 🤘🤘');

        return $this;
    }

    protected function purgeOpcacheDNSFileCache() : BakerInterface
    {
        $recursiveDirectoryIterator = new \RecursiveDirectoryIterator(
            __DIR__ . '/../../../../data/cache/Opcache/DNS',
            \RecursiveDirectoryIterator::SKIP_DOTS
        );

        $recursiveDirectoryIterator = new \RecursiveCallbackFilterIterator(
            $recursiveDirectoryIterator,
            function (\SplFileInfo $fileInformation) {
                return $fileInformation->getExtension() === 'php' ? true : false;
            }
        );
        foreach ($recursiveDirectoryIterator as $fileInformation) {
            $this->rm($fileInformation->getPathname());
        }

        return $this;
    }

    protected function getRealPaths(array $potentialRealPaths) : array
    {
        $realPaths = [];
        foreach ($this->getRealPathClosure()($potentialRealPaths) as $potentialRealPath) {
            if ($potentialRealPath !== false) {
                $realPaths[] = $potentialRealPath;
            }
        }

        return $realPaths;
    }

    protected function getRealPathClosure() : \Closure
    {
        return function (array $potentialRealPaths) {
            foreach ($potentialRealPaths as $potentialRealPath) {
                yield realpath($potentialRealPath);
            }
        };
    }

    protected function getContainerCacheFiles() : array
    {
        $directory = __DIR__ . '/../../../../data/cache';
        $files = array_diff(scandir($directory), ['.', '..']);

        $fullFilePaths = [];

        foreach ($files as $file) {
            if (preg_match(self::PHP_FILE_EXTENSION_REGEX, $file)) {
                $fullFilePaths[] = $directory . '/' . $file;
            }
        }

        $cachedContainerFileRealPaths = $this->getRealPaths($fullFilePaths);

        return $cachedContainerFileRealPaths;
    }

    protected function opcacheCompileFiles(array $fullFilePaths) : BakerInterface
    {
        foreach ($fullFilePaths as $key => $fullFilePath) {
            if (!opcache_is_script_cached($fullFilePath)) {
                $this->opcacheCompileFile($fullFilePath);
            } else {
                $this->log(sprintf('Opcache has already cached the file: %s', $fullFilePath));
            }
        }

        return $this;
    }

    protected function opcacheCompileFile(string $fullFilePath) : BakerInterface
    {
        try {
            if (opcache_compile_file($fullFilePath)) {
                $this->log(sprintf('Opcache has successfully compiled the file: %s', $fullFilePath));
            } else {
                throw new \LogicException(sprintf("Opcache could not compile the file: %s", $fullFilePath));
            }
        } catch (\ErrorException $errorException) {
            $this->log(sprintf('Opcache could not compile the file: %s', $fullFilePath));
        }
        return $this;
    }

    protected function getComposerAuthoritativeClassmap() : array
    {
        $classmapAuthoritativeConfigValueString = exec(
            // List all composer options
            // extract the line with classmap-authoritative
            // take the value (second word)
            'composer config --list | grep classmap-authoritative | awk \'{print $2}\''
        );
        if ($classmapAuthoritativeConfigValueString !== 'true') {
            throw new \RuntimeException('Please enable classmap-authoritative option in your composer.json');
        }
        $composerAuthoritativeClassMap = require __DIR__ . '/../../../../vendor/composer/autoload_classmap.php';
        if (empty($composerAuthoritativeClassMap)) {
            throw new \RuntimeException('Composer authoritative classmap is empty.');
        }
        return $composerAuthoritativeClassMap;
    }

    protected function rm(string $fullFilePath) : BakerInterface
    {
        if (is_file($fullFilePath)) {
            if (!unlink($fullFilePath)) {
                throw new \LogicException(sprintf("Could not unlink %s.", $fullFilePath));
            }
        } else {
            $this->log(sprintf('The file does not exist or it is not a regular file: %s', $fullFilePath));
        }

        return $this;
    }

    protected function log(string $message) : BakerInterface
    {
        fwrite(STDOUT, $message . "\n");

        return $this;
    }
}
