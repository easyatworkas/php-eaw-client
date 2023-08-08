<?php

namespace Eaw;

use League\MimeTypeDetection\FinfoMimeTypeDetector;

class MimeDetector
{
    /**
     * @param string $path
     * @return string|null
     */
    public static function getMimeType(string $path): ?string
    {
        $detector = new FinfoMimeTypeDetector();

        return $detector->detectMimeTypeFromPath($path);
    }

    /**
     * @param string $path
     * @return string|null
     */
    public static function getFileExtension(string $path): ?string
    {
        $detector = new FinfoMimeTypeDetector();

        $mime = $detector->detectMimeTypeFromFile($path);

        if ($mime === null) {
            return null;
        }

        return $detector->lookupExtension($mime);
    }
}
