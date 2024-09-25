<?php
namespace App\Service;

use GuzzleHttp\Client;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Messenger\MessageBusInterface;
use App\Message\GenerateImageMessage;

class ImageService
{
    private $client;
    private $apiKey;
    private $filesystem;
    private $bus;
    private $logger;

    public function __construct(Client $client, string $apiKey, MessageBusInterface $bus, LoggerInterface $logger)
    {
        $this->client = $client;
        $this->apiKey = $apiKey;
        $this->bus = $bus;
        $this->filesystem = new Filesystem();
        $this->logger = $logger;
    }

    public function getImageUrl(string $query): ?string
    {
        $this->bus->dispatch(new GenerateImageMessage($query));
        $this->logger->info('Dispatched GenerateImageMessage', ['query' => $query]);
        return 'Image generation in progress';
    }

    public function processImageGeneration(string $query): ?string
    {
        $novitaUrl = 'https://api.novita.ai/v3/async/txt2img';
        $statusUrl = 'https://api.novita.ai/v3/async/task-result';
        $placeholderUrl = 'https://via.placeholder.com/1024x1024?text=Image+Not+Found';
        $predefinedPrompt = "Realistic image of";

        try {
            $this->logger->info('Sending request to Novita API', ['query' => $query]);
            $response = $this->client->post($novitaUrl, [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $this->apiKey
                ],
                'json' => [
                    'extra' => [
                        'response_image_type' => 'jpeg'
                    ],
                    'request' => [
                        'prompt' => $predefinedPrompt . $query,
                        'model_name' => 'sd_xl_base_1.0.safetensors',
                        'negative_prompt' => 'nsfw, bottle, bad face',
                        'width' => 1024,
                        'height' => 1024,
                        'image_num' => 2,
                        'steps' => 20,
                        'seed' => -1,
                        'clip_skip' => 1,
                        'sampler_name' => 'Euler a',
                        'guidance_scale' => 7.5
                    ],
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            $this->logger->info('Received response from Novita API', ['response' => $data]);

            if (!isset($data['task_id'])) {
                throw new \Exception('Task ID not found in response');
            }

            $taskId = $data['task_id'];
            $this->logger->info('Task ID received', ['taskId' => $taskId]);

            $maxAttempts = 10;
            $attempt = 0;
            $delay = 5;

            while ($attempt < $maxAttempts) {
                sleep($delay);
                $this->logger->info('Checking task status', ['attempt' => $attempt, 'taskId' => $taskId]);
                $statusResponse = $this->client->get($statusUrl, [
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Authorization' => 'Bearer ' . $this->apiKey
                    ],
                    'query' => [
                        'task_id' => $taskId
                    ],
                ]);

                $statusData = json_decode($statusResponse->getBody()->getContents(), true);
                $this->logger->info('Received task status', ['statusData' => $statusData]);

                if (isset($statusData['task']['status']) && $statusData['task']['status'] === 'TASK_STATUS_SUCCEED') {
                    if (isset($statusData['images'][1]['image_url']) && filter_var($statusData['images'][1]['image_url'], FILTER_VALIDATE_URL)) {
                        $imageUrl = $statusData['images'][1]['image_url'];
                        $this->logger->info('Image URL found', ['imageUrl' => $imageUrl]);

                        $imageContent = file_get_contents($imageUrl);
                        if ($imageContent === false) {
                            throw new \Exception('Failed to download image');
                        }

                        $imageFilename = uniqid() . '.jpeg';
                        $imagePath = __DIR__ . '/../../public/img/' . $imageFilename;

                        if (!$this->filesystem->exists(dirname($imagePath))) {
                            $this->filesystem->mkdir(dirname($imagePath), 0777);
                        }

                        $this->filesystem->dumpFile($imagePath, $imageContent);

                        if ($this->filesystem->exists($imagePath)) {
                            $this->logger->info('Image saved successfully', ['imagePath' => $imagePath]);
                            return '/img/' . $imageFilename;
                        } else {
                            throw new \Exception('Failed to save image');
                        }
                    }
                }

                $attempt++;
            }
        } catch (\Exception $e) {
            $this->logger->error('Error during image generation', ['exception' => $e->getMessage()]);
        }

        return $placeholderUrl;
    }
}