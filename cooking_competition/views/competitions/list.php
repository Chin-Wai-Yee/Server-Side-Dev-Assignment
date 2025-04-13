<div class="table-responsive">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Title</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Voting Ends</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($competitions as $comp): ?>
                <tr>
                    <td><?= htmlspecialchars($comp['title']) ?></td>
                    <td><?= htmlspecialchars($comp['start_date']) ?></td>
                    <td><?= htmlspecialchars($comp['end_date']) ?></td>
                    <td><?= htmlspecialchars($comp['voting_end_date']) ?></td>
                    <td>
                        <?php
                        $now = new DateTime();
                        $start = new DateTime($comp['start_date']);
                        $end = new DateTime($comp['end_date']);
                        $voting_end = new DateTime($comp['voting_end_date']);
                        
                        if ($now < $start) {
                            echo '<span class="badge bg-info">Upcoming</span>';
                        } elseif ($now >= $start && $now <= $end) {
                            echo '<span class="badge bg-success">Active</span>';
                        } elseif ($now > $end && $now <= $voting_end) {
                            echo '<span class="badge bg-warning">Voting</span>';
                        } else {
                            echo '<span class="badge bg-secondary">Completed</span>';
                        }
                        ?>
                    </td>
                    <td>
                        <a href="index.php?page=competitions&action=view&id=<?= $comp['id'] ?>" class="btn btn-sm btn-info">View</a>
                        
                        <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
                            <a href="index.php?page=competitions&action=edit&id=<?= $comp['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                            <a href="index.php?page=competitions&action=delete&id=<?= $comp['id'] ?>" class="btn btn-sm btn-danger">Delete</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>