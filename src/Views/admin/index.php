<?php
/** @var array $comments */
/** @var array $counts */
/** @var string|null $status */
/** @var int $page */
/** @var int $totalPages */

$csrfToken = $this->app->csrf()->getToken();
$currentFilter = $status ?? 'all';
?>

<div class="mb-3">
    <div class="btn-group">
        <a href="/admin/comments" class="btn btn-outline-secondary <?= $currentFilter === 'all' ? 'active' : '' ?>">
            All <span class="badge bg-secondary ms-1"><?= $counts['all'] ?></span>
        </a>
        <a href="/admin/comments?status=pending" class="btn btn-outline-warning <?= $currentFilter === 'pending' ? 'active' : '' ?>">
            Pending <span class="badge bg-warning ms-1"><?= $counts['pending'] ?></span>
        </a>
        <a href="/admin/comments?status=approved" class="btn btn-outline-success <?= $currentFilter === 'approved' ? 'active' : '' ?>">
            Approved <span class="badge bg-success ms-1"><?= $counts['approved'] ?></span>
        </a>
        <a href="/admin/comments?status=rejected" class="btn btn-outline-danger <?= $currentFilter === 'rejected' ? 'active' : '' ?>">
            Rejected <span class="badge bg-danger ms-1"><?= $counts['rejected'] ?></span>
        </a>
    </div>
</div>

<?php if (empty($comments)): ?>
    <div class="card">
        <div class="card-body">
            <p class="text-secondary mb-0">No comments found.</p>
        </div>
    </div>
<?php else: ?>
    <div class="card">
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>Author</th>
                        <th>Comment</th>
                        <th>On</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th class="w-1">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($comments as $comment): ?>
                        <tr>
                            <td>
                                <?php if ($comment->user_id): ?>
                                    <span class="text-secondary" title="Registered user #<?= $comment->user_id ?>">
                                        <i class="ti ti-user me-1"></i>User #<?= $comment->user_id ?>
                                    </span>
                                <?php else: ?>
                                    <span title="<?= htmlspecialchars($comment->guest_email ?? '') ?>">
                                        <i class="ti ti-user-question me-1"></i><?= htmlspecialchars($comment->guest_name ?? 'Anonymous') ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="/admin/comments/<?= $comment->id ?>">
                                    <?= htmlspecialchars(mb_strimwidth(strip_tags($comment->body), 0, 80, '...')) ?>
                                </a>
                            </td>
                            <td>
                                <span class="text-secondary">
                                    <?= htmlspecialchars($comment->commentable_type) ?> #<?= $comment->commentable_id ?>
                                </span>
                            </td>
                            <td>
                                <?php
                                $statusBadge = match ($comment->status) {
                                    'approved' => 'bg-success',
                                    'pending'  => 'bg-warning',
                                    'rejected' => 'bg-danger',
                                    default    => 'bg-secondary',
                                };
                                ?>
                                <span class="badge <?= $statusBadge ?>"><?= htmlspecialchars($comment->status) ?></span>
                            </td>
                            <td class="text-secondary">
                                <?= $comment->created_at ? date('M j, Y g:ia', strtotime($comment->created_at)) : '-' ?>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <?php if ($comment->status !== 'approved'): ?>
                                        <form method="post" action="/admin/comments/<?= $comment->id ?>/approve" class="d-inline">
                                            <input type="hidden" name="_csrf_token" value="<?= $csrfToken ?>">
                                            <button type="submit" class="btn btn-sm btn-success" title="Approve">
                                                <i class="ti ti-check"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                    <?php if ($comment->status !== 'rejected'): ?>
                                        <form method="post" action="/admin/comments/<?= $comment->id ?>/reject" class="d-inline">
                                            <input type="hidden" name="_csrf_token" value="<?= $csrfToken ?>">
                                            <button type="submit" class="btn btn-sm btn-warning" title="Reject">
                                                <i class="ti ti-x"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                    <form method="post" action="/admin/comments/<?= $comment->id ?>/delete" class="d-inline" onsubmit="return confirm('Delete this comment permanently?')">
                                        <input type="hidden" name="_csrf_token" value="<?= $csrfToken ?>">
                                        <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                            <i class="ti ti-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php if ($totalPages > 1): ?>
        <div class="mt-3 d-flex justify-content-center">
            <ul class="pagination">
                <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="/admin/comments?page=<?= $page - 1 ?><?= $status ? '&status=' . $status : '' ?>">
                            <i class="ti ti-chevron-left"></i>
                        </a>
                    </li>
                <?php endif; ?>
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                        <a class="page-link" href="/admin/comments?page=<?= $i ?><?= $status ? '&status=' . $status : '' ?>">
                            <?= $i ?>
                        </a>
                    </li>
                <?php endfor; ?>
                <?php if ($page < $totalPages): ?>
                    <li class="page-item">
                        <a class="page-link" href="/admin/comments?page=<?= $page + 1 ?><?= $status ? '&status=' . $status : '' ?>">
                            <i class="ti ti-chevron-right"></i>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    <?php endif; ?>
<?php endif; ?>
