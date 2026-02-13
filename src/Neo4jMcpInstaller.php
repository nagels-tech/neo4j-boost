<?php

namespace Neo4j\LaravelBoost;

use Illuminate\Support\Facades\Http;

class Neo4jMcpInstaller
{
    protected const GITHUB_RELEASE_URL = 'https://github.com/neo4j/mcp/releases/download';

    protected const PLATFORM_ASSETS = [
        'Linux_x86_64' => 'neo4j-mcp_Linux_x86_64.tar.gz',
        'Linux_arm64' => 'neo4j-mcp_Linux_arm64.tar.gz',
        'Linux_i386' => 'neo4j-mcp_Linux_i386.tar.gz',
        'Darwin_x86_64' => 'neo4j-mcp_Darwin_x86_64.tar.gz',
        'Darwin_arm64' => 'neo4j-mcp_Darwin_arm64.tar.gz',
        'Windows_x86_64' => 'neo4j-mcp_Windows_x86_64.zip',
        'Windows_arm64' => 'neo4j-mcp_Windows_arm64.zip',
        'Windows_i386' => 'neo4j-mcp_Windows_i386.zip',
    ];

    public function getBinaryPath(): string
    {
        $path = config('neo4j-boost.neo4j_mcp.binary_path');
        if ($path !== null && $path !== '') {
            return $path;
        }
        $base = storage_path('app/neo4j-mcp/neo4j-mcp');
        if (PHP_OS_FAMILY === 'Windows') {
            return $base . '.exe';
        }
        return $base;
    }

    public function isInstalled(): bool
    {
        $path = $this->getBinaryPath();
        return is_file($path) && is_executable($path);
    }

    public function getPlatformAssetName(): ?string
    {
        $override = config('neo4j-boost.neo4j_mcp.platform_asset');
        if ($override !== null && isset(self::PLATFORM_ASSETS[$override])) {
            return self::PLATFORM_ASSETS[$override];
        }
        $os = PHP_OS_FAMILY;
        $arch = $this->normalizeArch(php_uname('m'));
        $key = ($os === 'Darwin' ? 'Darwin_' : $os . '_') . $arch;
        return self::PLATFORM_ASSETS[$key] ?? null;
    }

    public function getDownloadUrl(): ?string
    {
        $asset = $this->getPlatformAssetName();
        if ($asset === null) {
            return null;
        }
        $version = config('neo4j-boost.neo4j_mcp.version', 'v1.4.0');
        return self::GITHUB_RELEASE_URL . '/' . $version . '/' . $asset;
    }

    /**
     * Download and install the Neo4j MCP binary. Returns the binary path on success.
     *
     * @return string path to the installed binary
     * @throws \RuntimeException
     */
    public function install(): string
    {
        $url = $this->getDownloadUrl();
        if ($url === null) {
            throw new \RuntimeException('Unsupported platform. Set neo4j-boost.neo4j_mcp.platform_asset to a supported value (e.g. Linux_x86_64).');
        }

        $response = Http::timeout(120)->get($url);
        if (! $response->successful()) {
            throw new \RuntimeException('Failed to download Neo4j MCP binary: HTTP ' . $response->status());
        }

        $binaryPath = $this->getBinaryPath();
        $dir = dirname($binaryPath);
        if (! is_dir($dir)) {
            if (! @mkdir($dir, 0755, true)) {
                throw new \RuntimeException('Cannot create directory: ' . $dir);
            }
        }

        $isZip = str_ends_with($url, '.zip');
        $tempFile = $dir . '/._neo4j_mcp_dl.' . ($isZip ? 'zip' : 'tar.gz');
        file_put_contents($tempFile, $response->body());

        try {
            if ($isZip) {
                $this->extractZip($tempFile, $dir);
            } else {
                $this->extractTarGz($tempFile, $dir);
            }
            $exe = $this->findExecutableInDir($dir);
            if ($exe === null) {
                throw new \RuntimeException('Executable neo4j-mcp not found inside archive.');
            }
            if (realpath($exe) !== realpath($binaryPath)) {
                @rename($exe, $binaryPath);
            }
            @chmod($binaryPath, 0755);
        } finally {
            @unlink($tempFile);
            $this->removeExtractDir($dir);
        }

        return $binaryPath;
    }

    protected function extractTarGz(string $archive, string $dest): void
    {
        $phar = new \PharData($archive);
        $phar->extractTo($dest);
    }

    protected function extractZip(string $archive, string $dest): void
    {
        $zip = new \ZipArchive();
        if ($zip->open($archive) !== true) {
            throw new \RuntimeException('Failed to open ZIP archive.');
        }
        $zip->extractTo($dest);
        $zip->close();
    }

    protected function findExecutableInDir(string $dir): ?string
    {
        $names = ['neo4j-mcp', 'neo4j-mcp.exe'];
        $it = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS)
        );
        foreach ($it as $file) {
            if (! $file->isFile()) {
                continue;
            }
            if (in_array($file->getFilename(), $names, true)) {
                return $file->getPathname();
            }
        }
        return null;
    }

    protected function normalizeArch(string $m): string
    {
        $map = ['aarch64' => 'arm64', 'x86_64' => 'x86_64', 'amd64' => 'x86_64', 'i386' => 'i386', 'i686' => 'i386'];
        return $map[$m] ?? $m;
    }

    protected function removeExtractDir(string $dir): void
    {
        $binaryPath = $this->getBinaryPath();
        $it = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS | \RecursiveDirectoryIterator::FOLLOW_SYMLINKS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($it as $file) {
            $path = $file->getPathname();
            if ($path === $binaryPath) {
                continue;
            }
            if ($file->isDir()) {
                @rmdir($path);
            } else {
                @unlink($path);
            }
        }
    }
}
