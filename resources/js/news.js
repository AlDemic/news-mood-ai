document.addEventListener('DOMContentLoaded', function () {
    let currentMood = 'original';

    const moodButtons = document.querySelectorAll('.mood-btn');
    const cards = document.querySelectorAll('.news-card');
    const detail = document.getElementById('newsDetail');

    moodButtons.forEach(function (btn) {
        btn.addEventListener('click', function () {
            moodButtons.forEach(function (b) {
                b.classList.remove('active');
            });

            btn.classList.add('active');
            currentMood = btn.dataset.mood;
        });
    });

    cards.forEach(function (card) {
        card.addEventListener('click', function () {
            const title = card.dataset.title;
            const original = card.dataset.original;
            const source = card.dataset.source;
            const moodText = card.dataset[currentMood] || 'Нет текста для этого настроения';

            document.getElementById('detailTitle').textContent = title;
            document.getElementById('detailOriginal').textContent = original;
            document.getElementById('detailMood').textContent = moodText;
            document.getElementById('detailSource').href = source;

            detail.style.display = 'block';
        });
    });

    document.getElementById('closeDetail').addEventListener('click', function () {
        detail.style.display = 'none';
    });
});