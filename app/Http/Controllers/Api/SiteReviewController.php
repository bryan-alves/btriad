<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SiteReview;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class SiteReviewController extends Controller
{
    public function index()
    {
        return response()->json(
            SiteReview::query()
                ->orderByRaw("CASE WHEN status = 'pending' THEN 0 ELSE 1 END")
                ->orderBy('sort_order')
                ->orderByDesc('id')
                ->get(),
            200,
        );
    }

    public function store(Request $request)
    {
        $data = $this->validateReview($request);

        $data['author_photo_path'] = $this->resolveAuthorPhotoPath(
            $request,
            $data['author_photo_path'] ?? null,
            null,
        );

        $data['status'] = SiteReview::STATUS_APPROVED;

        $review = SiteReview::query()->create($data);

        return response()->json($review, 201);
    }

    public function update(Request $request, SiteReview $siteReview)
    {
        $data = $this->validateReview($request);

        $data['author_photo_path'] = $this->resolveAuthorPhotoPath(
            $request,
            array_key_exists('author_photo_path', $data) ? $data['author_photo_path'] : null,
            $siteReview->author_photo_path,
        );

        $siteReview->update($data);

        return response()->json($siteReview->fresh(), 200);
    }

    public function approve(SiteReview $siteReview)
    {
        $siteReview->load('student');

        $updates = [
            'status' => SiteReview::STATUS_APPROVED,
            'active' => true,
        ];

        if ($siteReview->student) {
            $updates['author_name'] = $siteReview->student->name;
            $updates['author_photo_path'] = $siteReview->student->photo;
        }

        $siteReview->update($updates);

        return response()->json($siteReview->fresh(), 200);
    }

    public function reject(SiteReview $siteReview)
    {
        $siteReview->update([
            'status' => SiteReview::STATUS_REJECTED,
            'active' => false,
        ]);

        return response()->json($siteReview->fresh(), 200);
    }

    public function destroy(SiteReview $siteReview)
    {
        $this->deleteStoredPhoto($siteReview->author_photo_path);
        $siteReview->delete();

        return response()->json(null, 204);
    }

    private function validateReview(Request $request): array
    {
        $data = $request->validate([
            'author_name' => ['required', 'string', 'max:255'],
            'author_photo_path' => ['nullable', 'string', 'max:255'],
            'author_photo' => ['nullable', 'image', 'max:2048'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['required', 'string', 'min:10', 'max:'.SiteReview::MAX_COMMENT_LENGTH],
            'active' => ['required', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        if (! array_key_exists('sort_order', $data) || $data['sort_order'] === null) {
            $data['sort_order'] = 0;
        }

        return $data;
    }

    private function resolveAuthorPhotoPath(
        Request $request,
        ?string $submittedPath,
        ?string $existingPath,
    ): ?string {
        if ($request->hasFile('author_photo')) {
            $this->deleteStoredPhoto($existingPath);

            /** @var UploadedFile $file */
            $file = $request->file('author_photo');

            return $file->store('site-review-photos', 'public');
        }

        if ($request->has('author_photo_path') && $submittedPath === '') {
            $this->deleteStoredPhoto($existingPath);

            return null;
        }

        return $submittedPath ?? $existingPath;
    }

    private function deleteStoredPhoto(?string $path): void
    {
        if ($path && str_starts_with($path, 'site-review-photos/')) {
            Storage::disk('public')->delete($path);
        }
    }
}
