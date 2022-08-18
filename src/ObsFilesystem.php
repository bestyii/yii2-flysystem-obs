<?php

namespace bestyii\flysystem\obs;

use creocoder\flysystem\Filesystem as BaseFileSystem;
use Obs\ObsClient;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;

/**
 * ObsFilesystem
 *
 * @author ez <ez@bestyii.com>
 */
class ObsFilesystem extends BaseFileSystem
{
    /**
     * @var string $prefix
     */
    public $prefix = '';
    /**
     * @var string
     */
    public $key;
    /**
     * @var string
     */
    public $secret;
    /**
     * @var string
     */
    public $region;
    /**
     * @var string
     */
    public $bucket;

    /**
     * @var string
     */
    public $endpoint;
    /**
     * @var boolean $isCname
     */
    public $isCname;
    /**
     * @var array $options
     */
    public $options;


    /**
     * @inheritdoc
     */
    public function init()
    {

        if ($this->key === null) {
            throw new InvalidConfigException('The "key" property must be set.');
        }

        if ($this->secret === null) {
            throw new InvalidConfigException('The "secret" property must be set.');
        }

        if ($this->bucket === null) {
            throw new InvalidConfigException('The "bucket" property must be set.');
        }

        if ($this->endpoint === null) {
            throw new InvalidConfigException('The "endpoint" property must be set.');
        }

        if ($this->region === null) {
            throw new InvalidConfigException('The "region" property must be set.');
        }

        parent::init();
    }

    /**
     * @return ObsAdapter
     */

    protected function prepareAdapter()
    {

        $config = [
            'key' => $this->key,
            'secret' => $this->secret,
            'bucket' => $this->bucket,
            'endpoint' => $this->endpoint,
            'region' => $this->region,
        ];

        $options = [
            'bucket_endpoint' => $config['is_cname'] ?? false
        ];
        if ($this->isCname !== null) {
            $config ['is_cname'] = $this->isCname;
        }
        if (($url = ArrayHelper::getValue($this->options, 'url')) == null) {
            $options ['url'] = $url;
        }

        $client = new ObsClient($config);
        if (YII_DEBUG) {
            $client->initLog([
                'FilePath' => \Yii::getAlias('@runtime/logs'),
                'FileName' => 'eSDK-OBS-PHP.log',
                'MaxFiles' => 10,
                'Level' => DEBUG
            ]);
        }
        return new ObsAdapter($client, $this->endpoint, $this->bucket, $this->prefix, $options);
    }
}
