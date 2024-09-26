<?php
// src/Controller/Admin/WorkerTaskController.php

namespace App\Controller\Admin;

use App\Repository\WorkerTaskRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class WorkerTaskController extends AbstractController
{
    #[Route('/admin/worker-tasks', name: 'admin_worker_tasks')]
    public function index(WorkerTaskRepository $workerTaskRepository): Response
    {
        $tasks = $workerTaskRepository->findAll();

        return $this->render('admin/worker_tasks/index.html.twig', [
            'tasks' => $tasks,
        ]);
    }
}