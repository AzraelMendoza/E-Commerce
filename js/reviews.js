 document.addEventListener("DOMContentLoaded", () => {
    const track = document.querySelector('.review-track');
    const cards = document.querySelectorAll('.review-track .custom-review-card');
    const prevBtn = document.getElementById('prevReview');
    const nextBtn = document.getElementById('nextReview');

    if (!track || cards.length === 0) return;

    const gap = 20; 
    let index = 0;

    const getVisibleCount = () => {
        const w = window.innerWidth;
        if (w < 576) return 1;
        if (w < 992) return 2;
        return 3;
    };

    const updateTrack = () => {
        // Gets width of 300px from your CSS + gap
        const cardWidth = cards[0].offsetWidth + gap;
        track.style.transform = `translateX(-${cardWidth * index}px)`;
    };

    nextBtn.addEventListener('click', () => {
        if (index < cards.length - getVisibleCount()) {
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

    window.addEventListener('resize', updateTrack);
    updateTrack();
});