<section id="avaliacoes" class="page-section" aria-labelledby="avaliacoes-heading">
    <div class="page-section__inner page-section__inner--stack">
        <h2 id="avaliacoes-heading" class="page-section__heading">Avaliações</h2>
        @if ($reviews->isNotEmpty())
            <div class="reviews-carousel" data-reviews-carousel>
                <button
                    type="button"
                    class="reviews-carousel__control reviews-carousel__control--prev"
                    data-reviews-carousel-prev
                    aria-label="Avaliações anteriores"
                >&lsaquo;</button>

                <div class="reviews-carousel__viewport">
                    <div class="reviews-carousel__track">
                        @foreach ($reviews as $review)
                            <article class="review-card">
                                <div class="review-card__header">
                                    @if ($review->author_photo_url)
                                        <img
                                            class="review-card__photo"
                                            src="{{ $review->author_photo_url }}"
                                            alt="Foto de {{ $review->short_author_name }}"
                                            width="48"
                                            height="48"
                                            loading="lazy"
                                            decoding="async"
                                        >
                                    @endif
                                    <div class="review-card__identity">
                                        <strong>{{ $review->short_author_name }}</strong>
                                        <span class="review-card__rating" aria-label="{{ $review->rating }} de 5 estrelas">
                                            {{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}
                                        </span>
                                    </div>
                                </div>
                                <p class="review-card__comment">{{ \Illuminate\Support\Str::limit($review->comment, \App\Models\SiteReview::MAX_COMMENT_LENGTH) }}</p>
                            </article>
                        @endforeach
                    </div>
                </div>

                <button
                    type="button"
                    class="reviews-carousel__control reviews-carousel__control--next"
                    data-reviews-carousel-next
                    aria-label="Próximas avaliações"
                >&rsaquo;</button>
            </div>
        @else
            <p class="page-section__text">Depoimentos em breve.</p>
        @endif
    </div>
</section>
