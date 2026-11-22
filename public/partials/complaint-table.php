<?php // $items contains only complaints the current account is allowed to view. ?>
<?php if (!$items): ?>
    <p class="empty">No complaints match this view.</p>
<?php else: ?>
<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>Reference / concern</th>
                <th>Department</th>
                <th>Priority</th>
                <th>Status</th><th>Submission review</th>
                <th>Deadline</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $complaint): ?>
                <tr>
                    <td>
                        <a href="details.php?id=<?= htmlspecialchars($complaint->id) ?>"><?= htmlspecialchars($complaint->reference) ?></a><br>
                        <?= htmlspecialchars($complaint->title) ?>
                    </td>
                    <td><?= htmlspecialchars(departmentName($complaint->departmentId)) ?></td>
                    <td><?= htmlspecialchars(ucfirst($complaint->severity)) ?></td>
                    <td><?php badge($complaint); ?></td><td><?= e(ucfirst($complaint->approvalStatus ?? "approved")) ?></td>
                    <td class="<?= overdue($complaint) ? 'late' : '' ?>">
                        <?= htmlspecialchars($complaint->deadline ?: 'Not set') ?>
                        <?php if (overdue($complaint)): ?> · overdue<?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>
