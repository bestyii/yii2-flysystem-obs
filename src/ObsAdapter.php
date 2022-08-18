<?php

namespace bestyii\flysystem\obs;


use Zing\Flysystem\Obs\ObsAdapter as BaseObsAdapter;
use League\Flysystem\Config;
use League\Flysystem\Util;
use Obs\ObsClient;
use Obs\ObsException;

class ObsAdapter extends BaseObsAdapter
{

    /**
     * write a file.
     *
     * @param string $path
     * @param string $contents
     *
     * @return array|false
     */
    public function write($path, $contents, Config $config)
    {
        $path = $this->applyPathPrefix($path);

        $options = $this->getOptionsFromConfig($config);
        if (!isset($options['ACL'])) {
            /** @var string|null $visibility */
            $visibility = $config->get('visibility');
            if ($visibility !== null) {
                $options['ACL'] = $options['ACL'] ?? ($visibility === self::VISIBILITY_PUBLIC ? ObsClient::AclPublicRead : ObsClient::AclPrivate);
            }
        }

        $shouldDetermineMimetype = $contents !== '' && !\array_key_exists('ContentType', $options);

        if ($shouldDetermineMimetype) {
            $mimeType = Util::guessMimeType($path, $contents);
            if ($mimeType) {
                $options['ContentType'] = $this->obsMimeConvert($mimeType);
            }
        }

        try {
            $this->client->putObject(array_merge($options, [
                'Bucket' => $this->bucket,
                'Key' => $path,
                'Body' => $contents,
            ]));
        } catch (ObsException $obsException) {
            return false;
        }

        return true;
    }
    /**
     * obs mime type convert
     *
     * @param string $mimeType
     *
     * @return string
     */
    private function obsMimeConvert($mimeType)
    {
        $mimeMaps = [
            'image/svg' => 'image/svg+xml',
            'image/svg;charset=UTF-8' => 'image/svg+xml;charset=UTF-8'
        ];

        if (isset($mimeMaps[$mimeType])) {
            return $mimeMaps[$mimeType];
        }

        return $mimeType;
    }

}
