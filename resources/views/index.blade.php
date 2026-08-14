<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Новости</title>
    @vite('resources/css/news.css')
</head>
<body>

<div class="mood-switcher">
    <button class="mood-btn active" data-mood="original">Оригинал</button>
    <button class="mood-btn" data-mood="positive">Позитив</button>
    <button class="mood-btn" data-mood="negative">Негатив</button>
    <button class="mood-btn" data-mood="neutral">Нейтрально</button>
    <button class="mood-btn" data-mood="ironic">Иронично</button>
</div>

<div class="news-grid">
    @foreach($news as $item)
        <div class="news-card"
             data-title="{{ $item->title }}"
             data-original="{{ $item->text }}"
             data-source="{{ $item->source }}"
             @foreach($item->moods as $mood)
                data-{{ $mood->mood }}="{{ $mood->text }}"
             @endforeach
        >
            <h3>{{ $item->title }}</h3>
            <p>{{ Str::limit($item->text, 100) }}</p>
        </div>
    @endforeach
</div>

<div class="news-detail" id="newsDetail">
    <h2 id="detailTitle"></h2>

    <div class="detail-columns">
        <div class="detail-column">
            <h4>Оригинал</h4>
            <p id="detailOriginal"></p>
        </div>
        <div class="detail-column">
            <h4>Переписанный текст</h4>
            <p id="detailMood"></p>
        </div>
    </div>

    <a id="detailSource" href="#" target="_blank">Читать источник</a>
    <button id="closeDetail">Закрыть</button>
</div>

    @vite('resources/js/news.js')
</body>
</html>