<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreDeviceRequest;
use App\Models\Device;
use Illuminate\Http\Response;

class DeviceController extends Controller
{
    /** Saves the phone's push token. A token moves to whoever logged in last on that phone. */
    public function store(StoreDeviceRequest $request): Response
    {
        $data = $request->validated();

        Device::updateOrCreate(
            ['fcm_token' => $data['fcm_token']],
            [
                'user_id' => $request->user()->id,
                'platform' => $data['platform'],
                'app_version' => $data['app_version'] ?? null,
                'last_seen_at' => now(),
            ],
        );

        return response()->noContent();
    }
}
