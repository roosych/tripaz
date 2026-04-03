<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Models\ListingMedia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MediaController extends Controller
{
    /**
     * Upload a photo (jpg/jpeg) for a listing.
     * Returns JSON: { id, url, path }
     */
    public function store(Request $request, Listing $listing)
    {
        $maxKb        = config('listing_media.max_kb', 5120);
        $allowedMimes = config('listing_media.allowed_mimes', 'jpeg,jpg');

        $request->validate([
            'file' => "required|file|mimes:{$allowedMimes}|max:{$maxKb}",
        ]);

        $disk = config('listing_media.disk', 'public');
        $file = $request->file('file');
        $path = $file->store("listings/{$listing->id}/gallery", $disk);

        $sortOrder = ($listing->media()->max('sort_order') ?? 0) + 1;

        $media = $listing->media()->create([
            'path'       => $path,
            'disk'       => $disk,
            'mime_type'  => $file->getMimeType(),
            'size'       => $file->getSize(),
            'collection' => 'gallery',
            'sort_order' => $sortOrder,
        ]);

        // If this is the first photo and no cover is set — auto-set as cover
        if (!$listing->featured_image) {
            $listing->update(['featured_image' => $path]);
        }

        return response()->json([
            'id'   => $media->id,
            'url'  => $media->url(),
            'path' => $path,
        ], 201);
    }

    /**
     * Delete a media record (photo or youtube) for a listing.
     */
    public function destroy(Listing $listing, ListingMedia $media)
    {
        abort_if($media->listing_id !== $listing->id, 404);

        // If this was the cover photo — clear it
        if ($listing->featured_image === $media->path) {
            // Try to find the next available photo
            $next = $listing->media()
                ->where('id', '!=', $media->id)
                ->where('mime_type', '!=', 'video/youtube')
                ->orderBy('sort_order')
                ->first();

            $listing->update(['featured_image' => $next?->path]);
        }

        // Remove the physical file (not for YouTube records)
        if ($media->mime_type !== 'video/youtube') {
            Storage::disk($media->disk ?: 'public')->delete($media->path);

            if (!empty($media->conversions)) {
                foreach ($media->conversions as $convPath) {
                    Storage::disk($media->disk ?: 'public')->delete($convPath);
                }
            }
        }

        $media->delete();

        return response()->json(['deleted' => true]);
    }

    /**
     * Set a photo as the cover (featured_image) of the listing.
     */
    public function setCover(Listing $listing, ListingMedia $media)
    {
        abort_if($media->listing_id !== $listing->id, 404);
        abort_if($media->mime_type === 'video/youtube', 422, 'YouTube videos cannot be set as cover.');

        $listing->update(['featured_image' => $media->path]);

        return response()->json(['cover' => $media->url()]);
    }

    /**
     * Store a YouTube video link for a listing.
     * Accepts: { youtube_url: "https://www.youtube.com/watch?v=..." }
     * Returns JSON: { id, youtube_url }
     */
    public function storeYoutube(Request $request, Listing $listing)
    {
        $request->validate([
            'youtube_url' => 'required|url|max:500',
        ]);

        $url = $request->input('youtube_url');

        // Validate it is actually a YouTube URL
        if (!preg_match('/youtube\.com\/watch\?v=|youtu\.be\//', $url)) {
            return response()->json(['error' => 'URL must be a valid YouTube link.'], 422);
        }

        $sortOrder = ($listing->media()->max('sort_order') ?? 0) + 1;

        $media = $listing->media()->create([
            'path'              => '', // no file path for YouTube
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
}
