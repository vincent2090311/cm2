<?php
declare(strict_types=1);

namespace Fuutur\CampaignMonitor\Logger;

use Psr\Log\LoggerInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Fuutur\CampaignMonitor\Helper\Data;

class Info
{
    /**
     * @param LoggerInterface $logger
     * @param SerializerInterface $serializer
     * @param Data $helper
     */
    public function __construct(
        private LoggerInterface $logger,
        private SerializerInterface $serializer,
        private Data $helper
    ) {
        $this->logger = $logger;
        $this->serializer = $serializer;
        $this->helper = $helper;
    }

    /**
     * Logs all extension specific notices to a separate file
     *
     * @param string|array $message
     * @return void
     */
    public function log($message)
    {
        if ($this->helper->canLog()) {
            if (!is_string($message)) {
                $message = $this->serializer->serialize($message);
            }
            $this->logger->info($message);
        }
    }
}
