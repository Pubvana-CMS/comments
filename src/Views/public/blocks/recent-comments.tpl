<div class="block">
    <div class="block-recent-comments">
        {% if title %}
        <h6 class="block-title">{{ title }}</h6>
        {% endif %}
        {% if comments %}
        <ul class="block-ul">
            {% for comment in comments %}
            <li class="block-li">
                <span class="block-comment-author">{{ comment.author }}</span>
                <span class="block-comment-on">on</span>
                <a class="block-a" href="{{ comment.url }}">{{ comment.post_title }}</a>
                <small class="block-meta">{{ comment.date | date('M j, Y') }}</small>
            </li>
            {% endfor %}
        </ul>
        {% endif %}
    </div>
</div>
