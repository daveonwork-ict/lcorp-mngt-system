<?php

namespace App\Http\Controllers;

use App\Services\PWAService;
use App\Services\PushNotificationPreparationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PWAController extends Controller
{
    public function __construct(
        private readonly PWAService $pwaService,
        private readonly PushNotificationPreparationService $pushService,
    ) {
    }

    public function offline(): View
    {
        return view('offline');
    }

    public function status(): JsonResponse
    {
        return response()->json([
            'status' => 'ok',
            'push_catalog' => $this->pushService->readinessCatalog(),
        ]);
    }

    public function installed(Request $request): JsonResponse
    {
        $user = $request->user();

        $this->pwaService->logInstall(
            $user?->id,
            $user?->primary_branch_id,
            $request->userAgent()
        );

        return response()->json(['status' => 'logged']);
    }
}
