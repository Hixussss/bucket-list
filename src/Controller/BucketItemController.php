<?php
namespace App\Controller;

use App\Entity\BucketItem;
use App\Form\BucketItemType;
use App\Service\ImageService;
use App\Repository\BucketItemRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BucketItemController extends AbstractController
{
    #[Route('/bucket/reorder-items', name: 'bucket_reorder_items', methods: ['POST'], priority: 10)]
    public function reorder(Request $request, BucketItemRepository $bucketItemRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $order = $data['order'];

        foreach ($order as $position => $id) {
            $item = $bucketItemRepository->find($id);
            if ($item) {
                $item->setPosition($position);
                $entityManager->persist($item);
            }
        }

        $entityManager->flush();

        return new JsonResponse(['status' => 'success']);
    }

    #[Route('/', name: 'bucket')]
    public function index2(BucketItemRepository $bucketItemRepository): Response
    {
        $bucketItems = $bucketItemRepository->findBy([], ['position' => 'ASC']);
        return $this->render('bucket_item/index.html.twig', [
            'bucket_items' => $bucketItems,
        ]);
    }

    #[Route('/bucket', name: 'bucket_index')]
    public function index(BucketItemRepository $bucketItemRepository): Response
    {
        $bucketItems = $bucketItemRepository->findBy([], ['position' => 'ASC']);
        return $this->render('bucket_item/index.html.twig', [
            'bucket_items' => $bucketItems,
        ]);
    }

    #[Route('/bucket/user', name: 'bucket_user_index')]
    public function userBuckets(BucketItemRepository $bucketItemRepository): Response
    {
        $user = $this->getUser();
        $bucketItems = $bucketItemRepository->findBy(['user' => $user], ['position' => 'ASC']);
        return $this->render('bucket_item/user_index.html.twig', [
            'bucket_items' => $bucketItems,
        ]);
    }

    #[Route('/bucket/new', name: 'bucket_new')]
    public function new(Request $request, ImageService $imageService, EntityManagerInterface $entityManager): Response
    {
        $bucketItem = new BucketItem();
        $form = $this->createForm(BucketItemType::class, $bucketItem);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $bucketItem->setCreatedAt(new \DateTimeImmutable());
            $bucketItem->setUser($this->getUser());
    
            $imageService->getImageUrl($bucketItem->getTitle());
            $bucketItem->setImageStatus('in_progress');
            $bucketItem->setPosition(0);
    
            // Set the location
            $bucketItem->setLocation($form->get('location')->getData());
    
            $entityManager->persist($bucketItem);
            $entityManager->flush();
    
            return $this->redirectToRoute('bucket_index');
        }
    
        return $this->render('bucket_item/new.html.twig', [
            'bucketItem' => $bucketItem,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/bucket/{id}', name: 'bucket_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(BucketItem $bucketItem): Response
    {
        return $this->render('bucket_item/show.html.twig', [
            'bucket_item' => $bucketItem,
        ]);
    }

    #[Route('/bucket/{id}/edit', name: 'bucket_edit')]
    public function edit(Request $request, BucketItem $bucketItem, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(BucketItemType::class, $bucketItem);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('bucket_index');
        }

        return $this->render('bucket_item/edit.html.twig', [
            'form' => $form->createView(),
            'bucket_item' => $bucketItem,
        ]);
    }

    #[Route('/bucket/{id}/delete', name: 'bucket_delete')]
    public function delete(Request $request, BucketItem $bucketItem, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$bucketItem->getId(), $request->request->get('_token'))) {
            $entityManager->remove($bucketItem);
            $entityManager->flush();
        }

        return $this->redirectToRoute('bucket_index');
    }

    #[Route('/bucket/{id}/complete', name: 'bucket_complete', methods: ['POST'])]
    public function complete(Request $request, BucketItem $bucketItem, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('complete'.$bucketItem->getId(), $request->request->get('_token'))) {
            $bucketItem->setCompleted(true);
            $entityManager->flush();
        }

        return $this->redirectToRoute('bucket_index');
    }
}