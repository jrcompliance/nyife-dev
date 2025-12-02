<?php

namespace Modules\FlowBuilder\Services;

use App\Models\Setting;
use Modules\FlowBuilder\Models\Flow;
use Modules\FlowBuilder\Models\FlowMedia;
use Modules\FlowBuilder\Models\FlowUserData;
use Modules\FlowBuilder\Resources\FlowResource;
use Modules\FlowBuilder\Validators\FlowValidator;
use Illuminate\Support\Facades\Log;

class FlowService
{
  public function getRows(object $request)
    {
        $organizationId = session()->get('current_organization');
        $model = new Flow;
        $searchTerm = $request->query('search');
        

        $flows = $model->listAll($organizationId, $searchTerm);
        

        $totalFlows = Flow::where('organization_id', $organizationId)->count();
        $activeFlows = Flow::where('organization_id', $organizationId)->where('status', 'active')->count();
        $inactiveFlows = Flow::where('organization_id', $organizationId)->where('status', 'inactive')->count();
        $totalRuns = Flow::where('organization_id', $organizationId)->withCount('flowLogs')->get()->sum('flow_logs_count');
        $analytics = [
            'totalFlows' => $totalFlows,
            'activeFlows' => $activeFlows,
            'inactiveFlows' => $inactiveFlows,
            'totalRuns' => $totalRuns,
        ];
        return [
            'flows' => FlowResource::collection($flows),
            $analytics
        ];
    }


    /**
     * Create a new flow.
     *
     * @param array $data
     * @return Flow
     */
    public function createFlow(array $data): Flow
    {
        $organizationId = session()->get('current_organization');

        return Flow::create([
            'organization_id' => $organizationId,
            'name' => $data['name'],
            'description' => $data['description'],
            'status' => 'inactive',
        ]);
    }

    /**
     * Update an existing flow.
     *
     * @param string $uuid
     * @param array $data
     * @return Flow
     */
public function updateFlow($uuid, array $data, $publish)
{
    // Log::info('[FlowService] updateFlow START', [
    //     'uuid' => $uuid,
    //     'incoming_data' => $data,
    //     'publish_flag' => $publish,
    // ]);

    $validator = new FlowValidator();
    
    // Log::info('[FlowService] Fetching Flow from DB', ['uuid' => $uuid]);
    $flow = Flow::where('uuid', $uuid)->firstOrFail();
    
    // Log::info('[FlowService] Flow fetched', [
    //     'id' => $flow->id,
    //     'current_status' => $flow->status
    // ]);

    if (!empty($flow->metadata)) {
        $metadataArray = json_decode($flow->metadata, true);
        
        // Log::info('[FlowService] Decoded metadata', [
        //     'metadata' => $metadataArray,
        //     'metadata_valid' => json_last_error() === JSON_ERROR_NONE
        // ]);

        $result = $validator->validateMessageNodes($metadataArray);

        // Log::info('[FlowService] Message node validation result before initial update', [
        //     'result' => $result
        // ]);

        if (is_array($result)) {
            Log::warning('[FlowService] Validation failed — setting status inactive (before update)');
            $data['status'] = 'inactive';
        }
    }

    // Log::info('[FlowService] Running first update() call on Flow', ['update_data' => $data]);
    $flow->update($data);

    // Log::info('[FlowService] Re-fetching Flow after first update');
    $flow = Flow::where('uuid', $uuid)->firstOrFail();

    if (!empty($flow->metadata)) {
        $metadataArray = json_decode($flow->metadata, true);

        $data2['trigger'] = \Arr::get($metadataArray, 'nodes.0.data.metadata.fields.type', null);
        $data2['keywords'] = \Arr::get($metadataArray, 'nodes.0.data.metadata.fields.keywords', null);

        // Log::info('[FlowService] Trigger/Keywords extracted', $data2);

        $result = $validator->validateMessageNodes($metadataArray);

        // Log::info('[FlowService] Message node validation result after first update', [
        //     'validation_result' => $result
        // ]);

        if (is_array($result)) {
            Log::warning('[FlowService] Validation failed — setting status inactive (second update)');
            $data2['status'] = 'inactive';
        }

        // Log::info('[FlowService] Running second update() call on Flow', ['update_data2' => $data2]);
        $flow->update($data2);
    }

    if (isset($publish)) {
        // Log::info('[FlowService] Publish flag detected', ['publish_flag' => $publish]);

        $validator = new FlowValidator();
        $metadataArray = json_decode($flow->metadata, true);
        $status = $publish == 1 ? 'active' : 'inactive';

        if ($publish == 1) {
            // Log::info('[FlowService] Validating before publishing...');
            $result = $validator->validateMessageNodes($metadataArray);

            if (!is_array($result)) {
                // Log::info('[FlowService] Validation passed — activating flow');
                $flow->update(['status' => $status]);

                // Log::info('[FlowService] Flow published successfully');
                return response()->json([
                    'success' => true,
                    'message' => __('Flow saved & published successfully!'),
                    'status' => 'active'
                ]);
            } else {
                Log::warning('[FlowService] Publishing blocked — validation errors', [
                    'errors' => $result
                ]);

                return response()->json([
                    'success' => false,
                    'errors' => $result,
                    'status' => 'inactive'
                ], 200);
            }
        } else {
            // Log::info('[FlowService] Setting flow status inactive (unpublish)');
            $flow->update(['status' => $status]);

            // Log::info('[FlowService] Flow unpublished successfully');
            return response()->json([
                'success' => true,
                'message' => __('Flow saved & unpublished successfully!'),
                'status' => 'inactive'
            ], 200);
        }
    }

    // Log::info('[FlowService] Final response returned', [
    //     'final_status' => $flow->status
    // ]);

    return response()->json([
        'success' => true,
        'message' => __('Flow saved successfully!'),
        'status' => $flow->status,
    ], 200);
}


    public function duplicateFlow($uuid): void
    {
        $flow = Flow::where('uuid', $uuid)->first();

        if (!$flow) {
            return; // Exit early if the flow doesn't exist
        }

        // Get the base name without any existing (number) suffix
        $baseName = preg_replace('/\(\d+\)$/', '', trim($flow->name));

        // Find existing duplicates
        $count = Flow::where('name', 'LIKE', "{$baseName} (%)")
            ->orWhere('name', $baseName)
            ->count();

        // Set the new name with an incremented number
        $newName = $count ? "{$baseName} ({$count})" : "{$baseName} (1)";

        // Duplicate the flow and assign the new name
        $duplicate = $flow->replicate(['uuid']);
        $duplicate->name = $newName;
        $duplicate->save();
    }

    public function uploadMedia($request, $uuid, $stepId)
    {
        $flow = Flow::where('uuid', $uuid)->first();
        $fileName = $request->file('file')->getClientOriginalName();
        $fileContent = $request->file('file');
        $storage = Setting::where('key', 'storage_system')->first()->value;

        // Get file extension or MIME type
        $fileExtension = strtolower($fileContent->getClientOriginalExtension());
        $fileMimeType = strtolower($fileContent->getMimeType());

        if($storage === 'local'){
            $location = 'local';
            $file = \Storage::disk('local')->put('public', $fileContent);
            $mediaFilePath = $file;
            $mediaUrl = rtrim(config('app.url'), '/') . '/media/' . ltrim($mediaFilePath, '/');
        } else if($storage === 'aws') {
            $organizationId = session()->get('current_organization');
            $location = 'amazon';
            $file = $request->file('file');
            $filePath = 'uploads/media/received/'  . $organizationId . '/' . $fileName;
            $uploadedFile = $file->store('uploads/media/sent/' . $organizationId, 's3');
            $mediaFilePath = \Storage::disk('s3')->url($uploadedFile);
            $mediaUrl = $mediaFilePath;
        }

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

        return $flowMedia;
    }

    /**
     * Delete a flow and its related steps.
     *
     * @param Flow $flow
     * @return void
     */
    public function deleteFlow($uuid): void
    {
        $flow = Flow::where('uuid', $uuid)->first();

        $flow->delete(); // Cascade will delete related steps
    }

    public function handleUserReply($contactId)
    {
        // Get user progress or start at step 1
        $userProgress = FlowUserData::firstOrCreate(
            ['contact_id' => $contactId],
            ['current_step' => 1]
        );

        $currentStep = 1;

        $flow = Flow::where('flow_id', $userProgress->flow_id)->first();

        $nextStep = $this->getNextStep($currentStep, $flow->metadata);

        if ($nextStep) {
            // Send message for the current step
            $this->sendMessage($userId, $nextStep);
    
            // Update user progress
            $userProgress->incrementStep();
        }
    }

    private function checkSteps($metadata){

    }

    function getNextStep($currentNodeId, $metadata)
    {
        $flowMetadata = json_decode($metadata, true);
        $nodes = $flowMetadata['nodes'];
        $edges = $flowMetadata['edges'];

        // Find the edge where the source is the current node
        foreach ($edges as $edge) {
            if ($edge['source'] === $currentNodeId) {
                $nextNodeId = $edge['target'];

                // Find and return the next node details
                foreach ($nodes as $node) {
                    if ($node['id'] === $nextNodeId) {
                        return $node;
                    }
                }
            }
        }

        // Return null if there are no more steps
        return null;
    }
}