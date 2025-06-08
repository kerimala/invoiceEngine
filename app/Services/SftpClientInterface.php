<?php
namespace App\Services;

interface SftpClientInterface
{
    /**
     * Download a single file from remotePath to localPath.
     * Return true on success, false on failure.
     */
    public function get(string $remotePath, string $localPath): bool;

    /**
     * Optional (for batch downloads): list files under a remote directory.
     * Return an array of filenames (e.g. ['inv1.csv', 'inv2.xml']).
     */
    public function ls(string $remoteDir): array;

    /**
     * Optional (for authentication): 
     * Return true if authentication succeeds, false otherwise.
     */
    public function connect(string $host, string $user, string $pass): bool;
}