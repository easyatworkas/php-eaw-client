<?php

namespace Eaw\Traits;

/**
 * @property string $baseUrl Defined in Client.
 * @property array $headers Defined in Client.
 */
trait BuildsHttpRequestData
{
    /**
     * @return bool
     */
    abstract function isAuthenticated(): bool;

    /**
     * @param string $path
     * @param array|null $parameters
     * @return string
     */
    protected function buildRequestUrl(string $path = '/', array $parameters = null): string
    {
        $url = $this->baseUrl . $path;

        if ($parameters) {
            $url .= '?' . http_build_query($parameters);
        }

        return $url;
    }

    /**
     * @param array|null $data
     * @param array|null $files
     * @return array
     */
    protected function buildRequestOptions(array $data = null, array $files = null): array
    {
        $options = [
            'headers' => $this->headers,
            'multipart' => [],
        ];

        if ($this->isAuthenticated()) {
            $options['headers']['Authorization'] = $this->credentials['token_type'] . ' ' . $this->credentials['access_token'];
        }

        if ($files) {
            if ($data) {
                // Super hacky way to build a multipart request.
                foreach (explode('&', http_build_query($data)) as $pair) {
                    list($var, $val) = explode('=', $pair, 2);

                    $options['multipart'][] = [
                        'name' => urldecode($var),
                        'contents' => urldecode($val),
                    ];
                }
            }

            foreach ($files as $name => $file) {
                if (is_resource($file)) {
                    $file = [
                        'handle' => $file,
                        'filename' => stream_get_meta_data($file)['uri'] ?? null,
                    ];
                }

                $options['multipart'][] = array_filter([
                    'name' => $name,
                    'contents' => $file['handle'] ?? null,
                    'filename' => $file['filename'] ?? null,
                ]);
            }
        } else {
            $options['json'] = $data;
        }

        return array_filter($options);
    }
}
