<?php

namespace WEBcoast\DeferredImageProcessing\Middleware;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use WEBcoast\DeferredImageProcessing\Resource\Processing\FileRepository;
use WEBcoast\DeferredImageProcessing\Utility\PathUtility;

class ImageProcessor implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $path = PathUtility::stripLeadingSlash($request->getUri()->getPath());

        if (($processingInstructions = FileRepository::getProcessingInstructionsByUrl($path)) !== false) {
            $storage = GeneralUtility::makeInstance(ResourceFactory::class)->getStorageObject($processingInstructions['storage']);
            $configuration = unserialize($processingInstructions['configuration']);
            $configuration['deferred'] = true;
            $processedFile = $storage->processFile(
                GeneralUtility::makeInstance(ResourceFactory::class)->getFileObject($processingInstructions['source_file']),
                $processingInstructions['task_type'] . '.' . $processingInstructions['task_name'],
                $configuration);

            $response = GeneralUtility::makeInstance(ResponseFactoryInterface::class)->createResponse();
            if ($processedFile->exists()) {
                FileRepository::deleteProcessingInstructions($processingInstructions['uid']);
                $response = $response->withStatus(200)
                    ->withHeader('Content-type', $processedFile->getMimeType());
                $response->getBody()->write($processedFile->getContents());

                return $response;
            }
        }

        return $handler->handle($request);
    }
}
