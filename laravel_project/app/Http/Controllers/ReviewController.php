<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\Reservation;
use App\Models\Accommodation;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReviewController extends Controller
{
    /**
     * Display a listing of the reviews.
     */
    public function index(Request $request)
    {
        $query = Review::with(['customer', 'accommodation', 'reservation'])
            ->published();

        // 宿泊施設でフィルタ
        if ($request->has('accommodation_id')) {
            $query->where('accommodation_id', $request->input('accommodation_id'));
        }

        // 評価でフィルタ
        if ($request->has('rating')) {
            $query->withRating($request->input('rating'));
        }

        // 並び替え
        $sortBy = $request->input('sort', 'recent');
        switch ($sortBy) {
            case 'helpful':
                $query->orderBy('helpful_count', 'desc');
                break;
            case 'rating_high':
                $query->orderBy('overall_rating', 'desc');
                break;
            case 'rating_low':
                $query->orderBy('overall_rating', 'asc');
                break;
            default:
                $query->orderBy('created_at', 'desc');
        }

        $reviews = $query->paginate(20);

        return Inertia::render('Reviews/Index', [
            'reviews' => $reviews,
        ]);
    }

    /**
     * Show the form for creating a new review.
     */
    public function create(Request $request)
    {
        $reservationId = $request->input('reservation_id');
        $reservation = null;

        if ($reservationId) {
            $reservation = Reservation::with(['room.accommodation', 'customer'])
                ->findOrFail($reservationId);

            // チェックアウト済みかチェック
            if ($reservation->status !== Reservation::STATUS_CHECKED_OUT) {
                return redirect()->back()
                    ->withErrors(['error' => 'チェックアウト後にレビューを投稿できます。']);
            }

            // 既にレビュー済みかチェック
            if ($reservation->review()->exists()) {
                return redirect()->route('reviews.show', $reservation->review)
                    ->with('info', '既にこの予約のレビューを投稿済みです。');
            }
        }

        return view('reviews.create', compact('reservation'));
    }

    /**
     * Store a newly created review in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'reservation_id' => 'required|exists:reservations,id',
            'overall_rating' => 'required|integer|min:1|max:5',
            'cleanliness_rating' => 'nullable|integer|min:1|max:5',
            'service_rating' => 'nullable|integer|min:1|max:5',
            'location_rating' => 'nullable|integer|min:1|max:5',
            'value_rating' => 'nullable|integer|min:1|max:5',
            'amenities_rating' => 'nullable|integer|min:1|max:5',
            'title' => 'nullable|string|max:255',
            'comment' => 'nullable|string|max:2000',
        ]);

        $reservation = Reservation::with(['room.accommodation', 'customer'])->findOrFail($validated['reservation_id']);

        // 既にレビュー済みかチェック
        if ($reservation->review()->exists()) {
            return redirect()->back()
                ->withErrors(['error' => '既にこの予約のレビューを投稿済みです。']);
        }

        $review = Review::create([
            'reservation_id' => $reservation->id,
            'customer_id' => $reservation->customer_id,
            'accommodation_id' => $reservation->room->accommodation_id,
            'overall_rating' => $validated['overall_rating'],
            'cleanliness_rating' => $validated['cleanliness_rating'] ?? null,
            'service_rating' => $validated['service_rating'] ?? null,
            'location_rating' => $validated['location_rating'] ?? null,
            'value_rating' => $validated['value_rating'] ?? null,
            'amenities_rating' => $validated['amenities_rating'] ?? null,
            'title' => $validated['title'] ?? null,
            'comment' => $validated['comment'] ?? null,
        ]);
        // 実際の予約からのレビューなので自動的に認証済みにする
        $review->is_verified = true;
        $review->save();

        return redirect()->route('reviews.show', $review)
            ->with('success', 'レビューを投稿しました。ありがとうございます！');
    }

    /**
     * Display the specified review.
     */
    public function show(Review $review)
    {
        $review->load(['customer', 'accommodation', 'reservation']);
        return Inertia::render('Reviews/Show', [
            'review' => $review,
        ]);
    }

    /**
     * Show the form for editing the specified review.
     */
    public function edit(Review $review)
    {
        $review->load('reservation.room.accommodation');
        return view('reviews.edit', compact('review'));
    }

    /**
     * Update the specified review in storage.
     */
    public function update(Request $request, Review $review)
    {
        if ($review->customer_id !== auth()->id()) {
            abort(403, '自分のレビューのみ編集できます。');
        }

        $validated = $request->validate([
            'overall_rating' => 'required|integer|min:1|max:5',
            'cleanliness_rating' => 'nullable|integer|min:1|max:5',
            'service_rating' => 'nullable|integer|min:1|max:5',
            'location_rating' => 'nullable|integer|min:1|max:5',
            'value_rating' => 'nullable|integer|min:1|max:5',
            'amenities_rating' => 'nullable|integer|min:1|max:5',
            'title' => 'nullable|string|max:255',
            'comment' => 'nullable|string|max:2000',
        ]);

        $review->update($validated);

        return redirect()->route('reviews.show', $review)
            ->with('success', 'レビューを更新しました。');
    }

    /**
     * Remove the specified review from storage.
     */
    public function destroy(Review $review)
    {
        if ($review->customer_id !== auth()->id()) {
            abort(403, '自分のレビューのみ削除できます。');
        }

        $review->delete();

        return redirect()->route('reviews.index')
            ->with('success', 'レビューを削除しました。');
    }

    /**
     * Add helpful vote to a review
     */
    public function addHelpfulVote(Review $review, Request $request)
    {
        $customer = auth()->user();
        if (!$customer) {
            return redirect()->back()->withErrors(['error' => 'ログインが必要です。']);
        }

        $review->addHelpfulVote($customer);

        return redirect()->back()
            ->with('success', '役に立ったと投票しました。');
    }

    /**
     * Add admin response to a review
     */
    public function addAdminResponse(Review $review, Request $request)
    {
        abort_unless(auth()->user()?->is_admin, 403, '管理者権限が必要です。');

        $validated = $request->validate([
            'admin_response' => 'required|string|max:1000',
        ]);

        $review->addAdminResponse($validated['admin_response']);

        return redirect()->route('reviews.show', $review)
            ->with('success', '返信を投稿しました。');
    }
}
