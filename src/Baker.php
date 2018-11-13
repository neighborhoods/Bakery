<?php
declare(strict_types=1);

namespace Neighborhoods\Bakery;

use Zend\HttpHandlerRunner\Exception\EmitterException;

class Baker implements BakerInterface
{
    public function bake(): BakerInterface
    {
        try {
            $this->log('');
            $this->purgeOpcacheDNSFileCaches();
            $this->purgeDIFileCaches();
            $this->opcacheCompile();
        } catch (\Throwable $throwable) {
            $this->log('>> Error:');
            $this->log($throwable->getMessage());
            $this->log($throwable->getTraceAsString());
        }

        return $this;
    }

    public function purgeDIFileCaches(): BakerInterface
    {
        $this->log(">> Ensuring that the DI container file cache is purged...");
        $this->purgeDiContainerFileCache();
        $this->log('>> Success.');

        $this->log(">> Ensuring that the DI YAML intermediary file cache is purged...");
        $this->purgeDIYAMLIntermediaryFileCache();
        $this->log('>> Success.');

        return $this;
    }

    public function purgeOpcacheDNSFileCaches(): BakerInterface
    {
        $this->log(">> Ensuring that the Opcache DNS file cache is purged...");
        $this->purgeOpcacheDNSFileCache();
        $this->log('>> Success.');

        return $this;
    }

    protected function opcacheCompile(): BakerInterface
    {
        $this->log(">> Asking Opcache to compile the Composer authoritative classmap...");
        $this->opcacheCompileFiles($this->getComposerAuthoritativeClassmap());
        $this->log('>> Success.');

        $this->compileDIContainer();

        $this->log('>> Asking Opcache to compile the DI container cache.');
        $this->opcacheCompileFiles($this->getContainerCacheFiles());
        $this->log('>> Success.');

        $this->log('>> Your Protean product has been successfully baked.🤘🤘');

        return $this;
    }

    protected function compileDIContainer(): BakerInterface
    {
        $this->log('>> Including index.php to generate the DI container cache...');
        ob_start();
        try {
            include $this->getIndexFile();
        } catch (EmitterException $emitterException) {
            $this->log('>> Intentionally ignoring EmitterException.');
        } catch (\Throwable $throwable) {
            ob_clean();
            throw $throwable;
        }
        ob_clean();
        $this->log('>> Success.');

        return $this;
    }

    protected function purgeOpcacheDNSFileCache(): BakerInterface
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

    protected function getDIYamlIntermediaryFilePaths(): array
    {
        $potentialRealPaths[] = __DIR__ . '/../../../../data/cache/expressive.yml';
        $realPaths = $this->getRealPaths($potentialRealPaths);

        return $realPaths;
    }

    protected function getRealPaths(array $potentialRealPaths): array
    {
        $realPaths = [];
        foreach ($this->getRealPathClosure()($potentialRealPaths) as $potentialRealPath) {
            if ($potentialRealPath !== false) {
                $realPaths[] = $potentialRealPath;
            }
        }

        return $realPaths;
    }

    protected function getRealPathClosure(): \Closure
    {
        return function (array $potentialRealPaths) {
            foreach ($potentialRealPaths as $potentialRealPath) {
                yield realpath($potentialRealPath);
            }
        };
    }

    protected function getContainerCacheFiles(): array
    {
        $cachedContainerFilePotentialRealPaths = [
            __DIR__ . '/../data/cache/config-cache.php',
            __DIR__ . '/../data/cache/container.php',
        ];

        $cachedContainerFileRealPaths = $this->getRealPaths($cachedContainerFilePotentialRealPaths);

        return $cachedContainerFileRealPaths;
    }

    protected function getIndexFile(): string
    {
        return __DIR__ . '/../../../../public/index.php';
    }

    protected function opcacheCompileFiles(array $fullFilePaths): BakerInterface
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

    protected function opcacheCompileFile(string $fullFilePath): BakerInterface
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

    protected function getComposerAuthoritativeClassmap(): array
    {
        $composerAuthoritativeClassMap = require __DIR__ . '/../../../../vendor/composer/autoload_classmap.php';
        if (empty($composerAuthoritativeClassMap)) {
            throw new \RuntimeException('Composer authoritative classmap is empty.');
        }

        return $composerAuthoritativeClassMap;
    }

    protected function rm(string $fullFilePath): BakerInterface
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

    protected function log(string $message): BakerInterface
    {
        fwrite(STDOUT, $message . "\n");

        return $this;
    }

    protected function purgeDiContainerFileCache(): BakerInterface
    {
        foreach ($this->getContainerCacheFiles() as $key => $containerCacheFile) {
            $this->rm($containerCacheFile);
        }

        return $this;
    }

    protected function purgeDIYAMLIntermediaryFileCache(): BakerInterface
    {
        foreach ($this->getDIYamlIntermediaryFilePaths() as $dIYamlIntermediaryFilePath) {
            $this->rm($dIYamlIntermediaryFilePath);
        }

        return $this;
    }
}
