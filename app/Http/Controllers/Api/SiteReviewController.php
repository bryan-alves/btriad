<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SiteReview;
use Illuminate\Http\Request;

class SiteReviewController extends Controller
{
    public function index()
    {
        return response()->json(
            SiteReview::query()
                ->orderBy('sort_order')
                ->orderByDesc('id')
                ->get(),
            200,
        );
    }

    public function store(Request $request)
    {
        $data = $this->validateReview($request);

        $review = SiteReview::query()->create($data);

        return response()->json($review, 201);
    }

    public function update(Request $request, SiteReview $siteReview)
    {
        $data = $this->validateReview($request);

        $siteReview->update($data);

        return response()->json($siteReview->fresh(), 200);
    }

    public function destroy(SiteReview $siteReview)
    {
        $siteReview->delete();

        return response()->json(null, 204);
    }

    private function validateReview(Request $request): array
    {
        return $request->validate([
            'author_name' => ['required', 'string', 'max:255'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['required', 'string'],
            'active' => ['required', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);
    }
}
