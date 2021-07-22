<?php

namespace Styla\CmsIntegration\UseCase;

use Psr\Log\LoggerInterface;
use Styla\CmsIntegration\Entity\StylaPage\StylaPage;
use Styla\CmsIntegration\Exception\AccountNotFoundException;
use Styla\CmsIntegration\Exception\PageDetailsRequestFiledException;
use Styla\CmsIntegration\Exception\PageNotFoundException;
use Styla\CmsIntegration\Exception\UseCaseInteractorException;
use Styla\CmsIntegration\Styla\Client\ClientRegistry;
use Styla\CmsIntegration\Styla\Client\DTO\PageDetails;
use Styla\CmsIntegration\Styla\Page\Guesser\StylaPageToReplaceGuesserInterface;
use Styla\CmsIntegration\Styla\Page\Guesser\DTO\ShopwarePageDetails;
use Styla\CmsIntegration\Styla\Page\PageCacheInteractor;
use Symfony\Component\HttpFoundation\Request;

class StylaPagesInteractor
{
    private ClientRegistry $clientRegistry;
    private StylaPageToReplaceGuesserInterface $guesser;
    private PageCacheInteractor $pageCacheInteractor;
    private LoggerInterface $logger;

    public function __construct(
        ClientRegistry $clientRegistry,
        StylaPageToReplaceGuesserInterface $guesser,
        PageCacheInteractor $pageCacheInteractor,
        LoggerInterface $logger
    ) {
        $this->clientRegistry = $clientRegistry;
        $this->guesser = $guesser;
        $this->pageCacheInteractor = $pageCacheInteractor;
        $this->logger = $logger;
    }

    /**
     * @param StylaPage $stylaPage
     *
     * @return PageDetails
     * @throws AccountNotFoundException
     * @throws PageNotFoundException
     * @throws UseCaseInteractorException
     */
    public function getPageDetails(StylaPage $stylaPage): PageDetails
    {
        try {
            $pageDetails = $this->pageCacheInteractor->getByPage($stylaPage);
            if (!$pageDetails) {
                $pageDetails = $this->getPageDetailsFromStyla($stylaPage);
                $this->pageCacheInteractor->save($stylaPage, $pageDetails);
            }

            return $pageDetails;
        } catch(UseCaseInteractorException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            throw new UseCaseInteractorException(
                'Could not get page details',
                UseCaseInteractorException::CODE_FAILED_TO_GET_PAGE_DETAILS,
                $exception
            );
        }
    }

    /**
     * @param ShopwarePageDetails $shopwarePageDetails
     *
     * @return StylaPage|null
     */
    public function guessPageToReplaceShopwarePage(ShopwarePageDetails $shopwarePageDetails): ?StylaPage
    {
        try {
            if (!$this->guesser->isSupported($shopwarePageDetails)) {
                return null;
            }

            return $this->guesser->guessStylaPage($shopwarePageDetails);
        } catch (\Throwable $exception) {
            $this->logger->error(
                'Exception happened during search for styla page that match request',
                [
                    'exception' => $exception,
                    'pageDetails' => $shopwarePageDetails
                ]
            );

            return null;
        }
    }

    /**
     * @param StylaPage $stylaPage
     * @throws AccountNotFoundException
     * @throws PageNotFoundException
     * @throws UseCaseInteractorException
     */
    public function refreshPageDetails(StylaPage $stylaPage): void
    {
        $pageDetails = $this->getPageDetailsFromStyla($stylaPage);
        $this->pageCacheInteractor->save($stylaPage, $pageDetails);
    }

    /**
     * @param StylaPage $stylaPage
     *
     * @return PageDetails
     * @throws AccountNotFoundException
     * @throws PageNotFoundException
     * @throws UseCaseInteractorException
     */
    private function getPageDetailsFromStyla(StylaPage $stylaPage): PageDetails
    {
        try {
            $client = $this->clientRegistry->getClientByAccountName($stylaPage->getAccountName());
        } catch (\Throwable $throwable) {
            $message = sprintf(
                'Could not get client instance for account name %s, probably configuration was changed',
                $stylaPage->getAccountName()
            );
            $this->logger->warning($message, ['exception' => $throwable]);

            throw new AccountNotFoundException($message, '', $throwable);
        }

        try {
            return $client->getPageData($stylaPage->getPath());
        } catch (PageDetailsRequestFiledException $exception) {
            throw new PageNotFoundException(sprintf('Styla page %s was not found', $stylaPage->getId()));
        } catch (\Throwable $exception) {
            throw new UseCaseInteractorException(
                'Could not get Styla page details',
                UseCaseInteractorException::CODE_FAILED_TO_GET_PAGE_DETAILS
            );
        }
    }
}
