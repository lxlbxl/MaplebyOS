<?php

namespace Modules\Operations\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Modules\Operations\Entities\Container;
use Modules\Operations\Entities\Document;
use Modules\Operations\Entities\Shipment;
use Modules\Operations\Services\DocumentCatalog;

/**
 * Scan (phone camera) or upload papers against a car or a container.
 * Files live on the private disk and are only served through show(), which
 * checks the viewer's role.
 */
class DocumentController extends Controller
{
    public const DISK = 'local';

    public function store(Request $request)
    {
        $isContainer = $request->input('documentable') === 'container';

        $request->validate([
            'documentable' => 'required|in:vehicle,cargo,container',
            'documentable_id' => ['required', 'integer', Rule::exists($isContainer ? 'containers' : 'shipments', 'id')],
        ]);
        $owner = $isContainer
            ? Container::findOrFail($request->input('documentable_id'))
            : Shipment::findOrFail($request->input('documentable_id'));
        $kind = DocumentCatalog::kindOf($owner);
        abort_unless($request->user()->can(self::viewPermission($kind)), 403);
        $types = DocumentCatalog::typesFor($kind);

        $validated = $request->validate([
            'type' => ['required', Rule::in(array_keys($types))],
            'notes' => 'nullable|string|max:1000',
            'files' => 'required|array|min:1|max:20',
            'files.*' => 'file|max:15360|mimes:jpg,jpeg,png,webp,heic,heif,pdf',
        ], [
            'files.*.mimes' => 'Upload a photo (JPG, PNG, HEIC) or a PDF.',
            'files.*.max' => 'Each file can be at most 15 MB.',
        ]);

        [$label, $stage, $sensitive] = $types[$validated['type']];
        abort_if($sensitive && !$request->user()->can('money.view'), 403, 'This document type shows prices and is only for the CEO and Head of Business.');


        foreach ($validated['files'] as $file) {
            $path = $file->storeAs(
                'documents/' . now()->format('Y/m'),
                Str::uuid() . '.' . strtolower($file->getClientOriginalExtension() ?: $file->extension()),
                self::DISK
            );
            $owner->documents()->create([
                'type' => $validated['type'],
                'stage' => $stage,
                'sensitive' => $sensitive,
                'original_name' => Str::limit($file->getClientOriginalName(), 250, ''),
                'path' => $path,
                'mime' => $file->getMimeType(),
                'size' => $file->getSize(),
                'notes' => $validated['notes'] ?? null,
                'uploaded_by' => $request->user()->id,
            ]);
        }

        $count = count($validated['files']);

        return redirect()->back()->with('success', $count === 1 ? "{$label} saved." : "{$count} pages of {$label} saved.");
    }

    public function show(Request $request, Document $document)
    {
        abort_unless($document->visibleTo($request->user()), 403);
        $owner = $document->documentable;
        abort_unless($owner && $request->user()->can(self::viewPermission(DocumentCatalog::kindOf($owner))), 403);
        abort_unless(Storage::disk(self::DISK)->exists($document->path), 404);

        $disposition = $request->boolean('download') ? 'attachment' : 'inline';

        return Storage::disk(self::DISK)->response($document->path, $document->original_name, [
            'Content-Type' => $document->mime ?: 'application/octet-stream',
            'Cache-Control' => 'private, max-age=3600',
        ], $disposition);
    }

    public function destroy(Document $document)
    {
        $document->delete();

        return redirect()->back()->with('success', "{$document->type_label} removed.");
    }

    private static function viewPermission(string $kind): string
    {
        return match ($kind) {
            'container' => 'containers.view',
            'cargo' => 'cargo.view',
            default => 'vehicles.view',
        };
    }

    /** Props for the documents panel on a car, clearing job or container page. */
    public static function panel(Shipment|Container $owner, ?\App\Models\User $user): array
    {
        $kind = DocumentCatalog::kindOf($owner);
        $all = $owner->documents()->with('uploader:id,name')->get();

        $panel = [
            'kind' => $kind,
            'id' => $owner->id,
            'documents' => DocumentCatalog::visible($all, $user),
            'types' => DocumentCatalog::options($kind, $user),
            'missing' => DocumentCatalog::missing($kind, $owner->status, $all, $user),
            'container_documents' => [],
        ];

        // A car also shows its container's papers (one Bill of Lading covers all four cars).
        if ($owner instanceof Shipment && $owner->container) {
            $panel['container_documents'] = DocumentCatalog::visible(
                $owner->container->documents()->with('uploader:id,name')->get(), $user
            );
            $panel['container_number'] = $owner->container->container_number;
        }

        return $panel;
    }
}
