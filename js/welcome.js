document.addEventListener("DOMContentLoaded", () => {
    const track = document.querySelector('.review-track');
    const cards = document.querySelectorAll('.review-track .custom-review-card');
    const prevBtn = document.getElementById('prevReview');
    const nextBtn = document.getElementById('nextReview');

    if (!track || cards.length === 0) return; // safety check

    const gap = 20; // spacing in px
    let visibleCount = 3;
    let index = 0;

    // calculate visible cards based on screen width
    const updateVisibleCount = () => {
        const w = window.innerWidth;
        if (w < 576) visibleCount = 1;
        else if (w < 992) visibleCount = 2;
        else visibleCount = 3;
    };

    const getCardWidth = () => {
        if (cards.length === 0) return 0;
        return cards[0].getBoundingClientRect().width + gap;
    };

    const updateTrack = () => {
        track.style.transform = `translateX(-${getCardWidth() * index}px)`;
    };

    nextBtn.addEventListener('click', () => {
        if (index < cards.length - visibleCount) {
            index++;
            updateTrack();
        }
    });

    prevBtn.addEventListener('click', () => {
        if (index > 0) {
            index--;
            updateTrack();
        }
    });

    // recalc on resize
    window.addEventListener('resize', () => {
        updateVisibleCount();
        if (index > cards.length - visibleCount) index = Math.max(0, cards.length - visibleCount);
        updateTrack();
    });

    // initialize
    updateVisibleCount();
    updateTrack();
});
