<?php

namespace Modules\FlowBuilder\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Log;
use Modules\FlowBuilder\Models\Flow;
use Modules\FlowBuilder\Models\FlowMedia;
use Modules\FlowBuilder\Models\FlowUserData;
use Modules\FlowBuilder\Resources\FlowResource;
use Modules\FlowBuilder\Validators\FlowValidator;

class FlowService
{
    public function getRows(object $request)
    {
        Log::info('[FlowService] Fetching flow rows', [
            'organization_id' => session()->get('current_organization'),
            'search' => $request->query('search')
        ]);

        $organizationId = session()->get('current_organization');
        $model = new Flow;
        $searchTerm = $request->query('search');
        $flows = $model->listAll($organizationId, $searchTerm);

        $totalFlows = Flow::where('organization_id', $organizationId)->count();
        $activeFlows = Flow::where('organization_id', $organizationId)->where('status', 'active')->count();
        $inactiveFlows = Flow::where('organization_id', $organizationId)->where('status', 'inactive')->count();
        $totalRuns = Flow::where('organization_id', $organizationId)->withCount('flowLogs')->get()->sum('flow_logs_count');

        Log::info('[FlowService] Analytics generated', [
            'totalFlows' => $totalFlows,
            'activeFlows' => $activeFlows,
            'inactiveFlows' => $inactiveFlows,
            'totalRuns' => $totalRuns
        ]);

        return [
            'flows' => FlowResource::collection($flows),
            compact('totalFlows', 'activeFlows', 'inactiveFlows', 'totalRuns')
        ];
    }

    public function createFlow(array $data): Flow
    {
        Log::info('[FlowService] Creating flow', $data);

        $organizationId = session()->get('current_organization');

        $flow = Flow::create([
            'organization_id' => $organizationId,
            'name' => $data['name'],
            'description' => $data['description'],
            'status' => 'inactive',
        ]);

        Log::info('[FlowService] Flow created', ['flow_id' => $flow->id]);

        return $flow;
    }

    public function updateFlow($uuid, array $data, $publish)
    {
        Log::info('[FlowService] Updating flow', ['uuid' => $uuid, 'publish' => $publish]);

        $validator = new FlowValidator();
        $flow = Flow::where('uuid', $uuid)->firstOrFail();

        Log::info('[FlowService] Existing Metadata Validation');

        if (isset($flow->metadata)) {
            $metadataArray = json_decode($flow->metadata, true);
            $result = $validator->validateMessageNodes($metadataArray);

            if (is_array($result)) {
                Log::warning('[FlowService] Validation failed. Forcing inactive.', $result);
                $data['status'] = 'inactive';
            }
        }

        $flow->update($data);
        Log::info('[FlowService] Flow updated partially.', $data);

        $flow = Flow::where('uuid', $uuid)->firstOrFail();

        if (isset($flow->metadata)) {
            $metadataArray = json_decode($flow->metadata, true);
            $data2['trigger'] = \Arr::get($metadataArray, 'nodes.0.data.metadata.fields.type', null);
            $data2['keywords'] = \Arr::get($metadataArray, 'nodes.0.data.metadata.fields.keywords', null);

            $result = $validator->validateMessageNodes($metadataArray);

            if (is_array($result)) {
                $data2['status'] = 'inactive';
            }

            $flow->update($data2);

            Log::info('[FlowService] Metadata validated & updated', $data2);
        }

        if (isset($publish)) {
            Log::info('[FlowService] Publishing Status Processing...');

            $metadataArray = json_decode($flow->metadata, true);
            $status = $publish == 1 ? 'active' : 'inactive';

            if ($publish == 1) {
                $result = $validator->validateMessageNodes($metadataArray);

                if (!is_array($result)) {
                    $flow->update(['status' => $status]);
                    Log::info('[FlowService] Flow Published Successfully');

                    return response()->json([
                        'success' => true,
                        'message' => __('Flow saved & published successfully!'),
                        'status' => 'active'
                    ]);
                } else {
                    Log::warning('[FlowService] Publish Failed - Validation Errors Occurred', $result);

                    return response()->json([
                        'success' => false,
                        'errors' => $result,
                        'status' => 'inactive'
                    ], 200);
                }
            } else {
                $flow->update(['status' => $status]);
                Log::info('[FlowService] Flow Unpublished Successfully');

                return response()->json([
                    'success' => true,
                    'message' => __('Flow saved & unpublished successfully!'),
                    'status' => 'inactive'
                ], 200);
            }
        }

        Log::info('[FlowService] Flow Saved Successfully', ['uuid' => $uuid]);

        return response()->json([
            'success' => true,
            'message' => __('Flow saved successfully!'),
            'status' => $flow->status,
        ], 200);
    }

    public function duplicateFlow($uuid): void
    {
        Log::info('[FlowService] Duplicating Flow', ['uuid' => $uuid]);

        $flow = Flow::where('uuid', $uuid)->first();

        if (!$flow) {
            Log::error('[FlowService] Flow not found for duplication', ['uuid' => $uuid]);
            return;
        }

        $baseName = preg_replace('/\(\d+\)$/', '', trim($flow->name));

        $count = Flow::where('name', 'LIKE', "{$baseName} (%)")
            ->orWhere('name', $baseName)
            ->count();

        $newName = $count ? "{$baseName} ({$count})" : "{$baseName} (1)";

        $duplicate = $flow->replicate(['uuid']);
        $duplicate->name = $newName;
        $duplicate->save();

        Log::info('[FlowService] Flow duplicated successfully', [
            'original_flow' => $flow->id,
            'duplicate_flow' => $duplicate->id,
        ]);
    }

    public function uploadMedia($request, $uuid, $stepId)
    {
        Log::info('[FlowService] Uploading media for flow', ['uuid' => $uuid, 'stepId' => $stepId]);

        $flow = Flow::where('uuid', $uuid)->first();

        $fileName = $request->file('file')->getClientOriginalName();
        $fileContent = $request->file('file');
        $storage = Setting::where('key', 'storage_system')->first()->value;

        if ($storage === 'local') {
            Log::info('[FlowService] Using Local Storage');
        } else {
            Log::info('[FlowService] Using S3 Storage');
        }

        $fileMimeType = strtolower($fileContent->getMimeType());

        if ($storage === 'local') {
            $file = \Storage::disk('local')->put('public', $fileContent);
            $mediaUrl = rtrim(config('app.url'), '/') . '/media/' . ltrim($file, '/');
        } else {
            $organizationId = session()->get('current_organization');
            $uploadedFile = $fileContent->store('uploads/media/sent/' . $organizationId, 's3');
            $mediaUrl = \Storage::disk('s3')->url($uploadedFile);
        }

        Log::info('[FlowService] Media File Stored', ['path' => $mediaUrl]);

        $flowMedia = FlowMedia::create([
            'flow_id' => $flow->id,
            'step_id' => $stepId,
            'path' => $mediaUrl,
            'location' => $storage,
            'metadata' => json_encode([
                'name' => $fileName,
                'extension' => $fileMimeType,
                'size' => $fileContent->getSize()
            ])
        ]);

        Log::info('[FlowService] Media record saved', ['media_id' => $flowMedia->id]);

        return $flowMedia;
    }

    public function deleteFlow($uuid): void
    {
        Log::info('[FlowService] Deleting flow', ['uuid' => $uuid]);

        $flow = Flow::where('uuid', $uuid)->first();

        if (!$flow) {
            Log::error('[FlowService] Flow not found for deletion', ['uuid' => $uuid]);
            return;
        }

        $flow->delete();

        Log::info('[FlowService] Flow deleted successfully', ['flow_id' => $flow->id]);
    }

    public function handleUserReply($contactId)
    {
        Log::info('[FlowService] Handling user reply', ['contact_id' => $contactId]);

        $userProgress = FlowUserData::firstOrCreate(
            ['contact_id' => $contactId],
            ['current_step' => 1]
        );

        Log::info('[FlowService] User progress fetched', ['current_step' => $userProgress->current_step]);

        $flow = Flow::find($userProgress->flow_id);

        if (!$flow) {
            Log::error('[FlowService] Flow not found for reply handling', ['flow_id' => $userProgress->flow_id]);
            return;
        }

        $nextStep = $this->getNextStep($userProgress->current_step, $flow->metadata);

        if ($nextStep) {
            Log::info('[FlowService] Sending message for next step', $nextStep);

            $this->sendMessage($contactId, $nextStep);

            $userProgress->incrementStep();
        } else {
            Log::info('[FlowService] No more steps available - flow completed.');
        }
    }

    function getNextStep($currentNodeId, $metadata)
    {
        Log::info('[FlowService] Calculating next step', ['current_node' => $currentNodeId]);

        $flowMetadata = json_decode($metadata, true);
        $nodes = $flowMetadata['nodes'];
        $edges = $flowMetadata['edges'];

        foreach ($edges as $edge) {
            if ($edge['source'] === $currentNodeId) {
                Log::info('[FlowService] Edge found', $edge);
                foreach ($nodes as $node) {
                    if ($node['id'] === $edge['target']) {
                        Log::info('[FlowService] Next step found', $node);
                        return $node;
                    }
                }
            }
        }

        Log::info('[FlowService] No next step - end of flow reached');
        return null;
    }
}
