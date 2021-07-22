<?php

namespace Styla\CmsIntegration\Styla\Client;

use Styla\CmsIntegration\Exception\ClientInstanceNotFound;

class ClientRegistry
{
    /**
     * @var array|ClientInterface[]
     */
    private array $clients;

    public function registerClient(ClientInterface $client)
    {
        $this->clients[] = $client;
    }

    public function getClientByAccountName(string $accountName): ClientInterface
    {
        foreach ($this->clients as $client) {
            if ($client->getConfiguration()->getAccountName() === $accountName) {
                return $client;
            }
        }

        throw new ClientInstanceNotFound(
            sprintf('Client instance for account %s was not registered', $accountName)
        );
    }
}
