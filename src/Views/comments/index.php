<?php
/** @var array $comments */
/** @var string $commentableType */
/** @var int $commentableId */
?>
<!-- Comments display stub - public frontend implementation pending -->
<div class="comments-list">
    <p>Comments for <?= htmlspecialchars($commentableType) ?> #<?= $commentableId ?> - <?= count($comments) ?> comment(s)</p>
</div>
