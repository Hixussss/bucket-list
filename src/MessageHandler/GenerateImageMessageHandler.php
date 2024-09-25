<?php
// src/MessageHandler/GenerateImageMessageHandler.php

namespace App\MessageHandler;

use App\Message\GenerateImageMessage;
use App\Repository\BucketItemRepository;
use App\Service\ImageService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Psr\Log\LoggerInterface;

class GenerateImageMessageHandler implements MessageHandlerInterface
{
    private $imageService;
    private $logger;
    private $bucketItemRepository;
    private $entityManager;

    public function __construct(ImageService $imageService, LoggerInterface $logger, BucketItemRepository $bucketItemRepository, EntityManagerInterface $entityManager)
    {
        $this->imageService = $imageService;
        $this->logger = $logger;
        $this->bucketItemRepository = $bucketItemRepository;
        $this->entityManager = $entityManager;
    }

    public function __invoke(GenerateImageMessage $message)
    {
        $query = $message->getQuery();
        $this->logger->info('Starting image generation for query: ' . $query);

        try {
            $imageUrl = $this->imageService->processImageGeneration($query);
            $this->logger->info('Image generated successfully', ['imageUrl' => $imageUrl]);

            $bucketItem = $this->bucketItemRepository->findOneBy(['title' => $query]);
            if ($bucketItem) {
                $this->logger->info('Bucket item found', ['title' => $query]);

                $bucketItem->setImage($imageUrl);
                $bucketItem->setImageStatus('completed');
                $this->entityManager->persist($bucketItem);
                $this->entityManager->flush();

                $this->logger->info('Bucket item updated and persisted', ['title' => $query]);
            } else {
                $this->logger->warning('Bucket item not found', ['title' => $query]);
            }
        } catch (\Exception $e) {
            $this->logger->error('Error processing image generation', ['exception' => $e->getMessage()]);
        }
    }
}