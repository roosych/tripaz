<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Models\ListingMedia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class MediaController extends Controller
{
    /**
     * Upload a media file for a listing owned by the authenticated user.
     */
    public function store(Request $request, string $slug)
    {
        $listing = Auth::user()
            ->listings()
            ->where('slug', $slug)
            ->firstOrFail();

        $this->authorize('update', $listing);

        $request->validate([
            'file'       => 'required|file|mimes:jpeg,jpg,png,webp,gif|max:10240',
            'collection' => 'nullable|string|max:50',
        ]);

        $file       = $request->file('file');
        $collection = $request->input('collection', 'gallery');
        $disk       = 'public';

        $path = $file->store("listings/{$listing->id}/{$collection}", $disk);

        $sortOrder = $listing->media()->max('sort_order') + 1;

        $media = $listing->media()->create([
            'path'       => $path,
            'disk'       => $disk,
            'mime_type'  => $file->getMimeType(),
            'size'       => $file->getSize(),
            'collection' => $collection,
            'sort_order' => $sortOrder,
        ]);

        return response()->json([
            'id'  => $media->id,
            'url' => $media->url(),
        ], 201);
    }

    /**
     * Set a photo as the cover (featured_image) of the listing.
     */
    public function setCover(string $slug, int $media)
    {
        $listing = Auth::user()
            ->listings()
            ->where('slug', $slug)
            ->firstOrFail();

        $this->authorize('update', $listing);

        $mediaItem = $listing->media()->findOrFail($media);

        abort_if($mediaItem->mime_type === 'video/youtube', 422, 'YouTube videos cannot be set as cover.');

        $listing->update(['featured_image' => $mediaItem->path]);

        return response()->json(['cover' => $mediaItem->url()]);
    }

    /**
     * Store a YouTube video link for a listing owned by the authenticated user.
     */
    public function storeYoutube(Request $request, string $slug)
    {
        $listing = Auth::user()
            ->listings()
            ->where('slug', $slug)
            ->firstOrFail();

        $this->authorize('update', $listing);

        $request->validate([
            'youtube_url' => 'required|url|max:500',
        ]);

        $url = $request->input('youtube_url');

        if (!preg_match('/youtube\.com\/watch\?v=|youtu\.be\//', $url)) {
            return response()->json(['error' => 'URL должен быть корректной ссылкой на YouTube.'], 422);
        }

        $sortOrder = ($listing->media()->max('sort_order') ?? 0) + 1;

        $media = $listing->media()->create([
            'path'              => '',
            'disk'              => '',
            'mime_type'         => 'video/youtube',
            'collection'        => 'gallery',
            'sort_order'        => $sortOrder,
            'custom_properties' => ['youtube_url' => $url],
        ]);

        return response()->json([
            'id'          => $media->id,
            'youtube_url' => $url,
        ], 201);
    }

    /**
     * Delete a media record belonging to a listing owned by the authenticated user.
     */
    public function destroy(string $slug, int $mediaId)
    {
        $listing = Auth::user()
            ->listings()
            ->where('slug', $slug)
            ->firstOrFail();

        $this->authorize('update', $listing);

        /** @var ListingMedia $media */
        $media = $listing->media()->findOrFail($mediaId);

        // Remove the physical file from disk
        Storage::disk($media->disk)->delete($media->path);

        // Remove any conversion files
        if (!empty($media->conversions)) {
            foreach ($media->conversions as $conversionPath) {
                Storage::disk($media->disk)->delete($conversionPath);
            }
        }

        $media->delete();

        return response()->json(['deleted' => true]);
    }
}
