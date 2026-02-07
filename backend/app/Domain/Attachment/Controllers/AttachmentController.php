<?php

declare(strict_types=1);

namespace App\Domain\Attachment\Controllers;

use App\Domain\Attachment\Actions\Attachments\CreateAttachment;
use App\Domain\Attachment\Actions\Attachments\DeleteAttachment;
use App\Domain\Attachment\Actions\Attachments\UpdateAttachment;
use App\Domain\Attachment\Models\Attachment;
use App\Domain\Attachment\Queries\AttachmentQueryService;
use App\Domain\Attachment\Requests\StoreAttachmentRequest;
use App\Domain\Attachment\Requests\UpdateAttachmentRequest;
use App\Domain\Attachment\Resources\AttachmentCollection;
use App\Domain\Attachment\Resources\AttachmentResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AttachmentController extends Controller
{
    public function __construct(
        protected AttachmentQueryService $queryService,
        protected CreateAttachment $createAttachment,
        protected UpdateAttachment $updateAttachment,
        protected DeleteAttachment $deleteAttachment
    ) {
        $this->authorizeResource(Attachment::class, 'attachment');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): AttachmentCollection
    {
        return new AttachmentCollection(
            $this->queryService->getAttachments($request)
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAttachmentRequest $request): AttachmentResource
    {
        $user = $request->user();
        $data = $request->validated();

        // Strictly use the user's organization_id to prevent cross-org uploads
        $data['organization_id'] = $user->organization_id;

        $attachment = $this->createAttachment->handle(
            $data,
            $request->file('file'),
            (string) $user->id
        );

        return new AttachmentResource($attachment);
    }

    /**
     * Display the specified resource.
     */
    public function show(Attachment $attachment): AttachmentResource
    {
        $attachment = $this->queryService->getAttachmentById($attachment->id);

        return new AttachmentResource($attachment);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAttachmentRequest $request, Attachment $attachment): AttachmentResource
    {
        $attachment = $this->updateAttachment->handle(
            $attachment,
            $request->validated(),
            (string) $request->user()?->id
        );

        return new AttachmentResource($attachment);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Attachment $attachment): JsonResponse
    {
        $this->deleteAttachment->handle($attachment);

        return response()->json(['message' => 'Attachment deleted']);
    }

    /**
     * Download the attachment.
     */
    public function download(Attachment $attachment)
    {
        $this->authorize('view', $attachment);

        if (Storage::disk($attachment->disk)->exists($attachment->path, $attachment->name)) {
            return Storage::download($attachment->path, $attachment->name);
        }

        return response()->json(['message' => 'Attachment not found']);
    }
}
