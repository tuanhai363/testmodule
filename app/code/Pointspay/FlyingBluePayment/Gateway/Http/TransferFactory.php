<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Pointspay\FlyingBluePayment\Gateway\Http;

use Magento\Payment\Gateway\Http\TransferBuilder;
use Magento\Payment\Gateway\Http\TransferFactoryInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use Pointspay\FlyingBluePayment\Gateway\Config\Config;

class TransferFactory implements TransferFactoryInterface
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * @var TransferBuilder
     */
    private $transferBuilder;

    /**
     * @var array
     */
    public static $headers = [
        'Content-Type' => 'application/json;charset=UTF-8',
        'Accept' => 'application/json',
    ];

    /**
     * @var array
     */
    public static $clientConfig = [
        'timeout' => 60,
    ];

    /**
     * @param TransferBuilder $transferBuilder
     */
    public function __construct(
        Config $config,
        TransferBuilder $transferBuilder
    ) {
        $this->config = $config;
        $this->transferBuilder = $transferBuilder;
    }

    /**
     * Builds gateway transfer object
     *
     * @param array $request
     * @return TransferInterface
     */
    public function create(array $request)
    {
        $headers = self::$headers;
        return $this->transferBuilder
            ->setClientConfig(self::$clientConfig)
            ->setHeaders($headers)
            ->setUri($this->config->getApiUrl())
            ->setBody($request)
            ->build();
    }
}
