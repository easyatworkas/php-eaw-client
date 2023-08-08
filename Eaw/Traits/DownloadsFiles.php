<?php

namespace Eaw\Traits;

use Eaw\Response;
use GuzzleHttp\Promise\PromiseInterface;
use Psr\Http\Message\StreamInterface;

trait DownloadsFiles
{
    /**
     * @param string $method
     * @param string $path
     * @param array|null $parameters
     * @param array|null $data
     * @param array|null $files
     * @param array $options
     * @return PromiseInterface<Response|array>
     */
    abstract function requestAsync(string $method, string $path, array $parameters = null, array $data = null, array $files = null, array $options = []): PromiseInterface;

    /**
     * @param string $method
     * @param string $path
     * @param array|null $parameters
     * @param array|null $data
     * @param array $files
     * @param array $options
     * @return string The path to the downloaded file.
     */
    public function download(string $method, string $path, array $parameters = null, array $data = null, array $files = [], array $options = []): string
    {
        return $this->downloadAsync($method, $path, $parameters, $data, $files, $options)->wait(true);
    }

    /**
     * @param string $method
     * @param string $path
     * @param array|null $parameters
     * @param array|null $data
     * @param array|null $files
     * @param array $options
     * @return PromiseInterface<string> Promise that resolves to the path to the downloaded file.
     */
    public function downloadAsync(string $method, string $path, array $parameters = null, array $data = null, array $files = null, array $options = []): PromiseInterface
    {
        return $this->requestAsync($method, $path, $parameters, $data, $files, $options + [ 'raw' => true ])
            ->then(function (Response $response) {
                $stream = $response->getStream();

                $path = tempnam(sys_get_temp_dir(), 'eaw');
                $fh = fopen($path, 'w');

                while (!$stream->eof()) {
                    fwrite($fh, $stream->read(1024));
                }

                fclose($fh);

                return $path;
            });
    }
}
