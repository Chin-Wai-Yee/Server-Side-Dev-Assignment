<?php
// views/competitions/edit.php - Form for editing competitions
// Get competition data from the controller
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header bg-light">
                    <h2 class="mb-0"><i class="fas fa-edit"></i> Edit Competition</h2>
                </div>
                <div class="card-body">
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger">
                            <?= $_SESSION['error']; ?>
                            <?php unset($_SESSION['error']); ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="index.php?page=competitions&action=edit&id=<?= $competition['id'] ?>">
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" id="title" name="title" value="<?= htmlspecialchars($competition['title']) ?>" placeholder="Title" required>
                            <label for="title">Title</label>
                        </div>
                        
                        <div class="form-floating mb-3">
                            <textarea class="form-control" id="description" name="description" rows="4" placeholder="Description" style="height: 100px;" required><?= htmlspecialchars($competition['description']) ?></textarea>
                            <label for="description">Description</label>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col">
                                <div class="form-floating">
                                    <input type="date" class="form-control" id="start_date" name="start_date" placeholder="Start Date" value="<?= date('Y-m-d', strtotime($competition['start_date'])) ?>" required>
                                    <label for="start_date">Start Date</label>
                                </div>
                            </div>
                            <div class="col">
                                <div class="form-floating">
                                    <input type="time" class="form-control" id="start_time" name="start_time" placeholder="Start Time" value="<?= date('H:i', strtotime($competition['start_date'])) ?>" required>
                                    <label for="start_time">Start Time</label>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col">
                                <div class="form-floating">
                                    <input type="date" class="form-control" id="end_date" name="end_date" placeholder="Submit Recipe End Date" value="<?= date('Y-m-d', strtotime($competition['end_date'])) ?>" required>
                                    <label for="end_date">Submit Recipe End Date</label>
                                </div>
                            </div>
                            <div class="col">
                                <div class="form-floating">
                                    <input type="time" class="form-control" id="end_time" name="end_time" placeholder="Submit Recipe End Time" value="<?= date('H:i', strtotime($competition['end_date'])) ?>" required>
                                    <label for="end_time">Submit Recipe End Time</label>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col">
                                <div class="form-floating">
                                    <input type="date" class="form-control" id="voting_end_date" name="voting_end_date" placeholder="Voting End Date" value="<?= date('Y-m-d', strtotime($competition['voting_end_date'])) ?>" required>
                                    <label for="voting_end_date">Voting End Date</label>
                                </div>
                            </div>
                            <div class="col">
                                <div class="form-floating">
                                    <input type="time" class="form-control" id="voting_end_time" name="voting_end_time" placeholder="Voting End Time" value="<?= date('H:i', strtotime($competition['voting_end_date'])) ?>" required>
                                    <label for="voting_end_time">Voting End Time</label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <a href="index.php?page=competitions" class="btn btn-secondary" role="button">Cancel</a>
                            <button type="submit" class="btn btn-primary">Update Competition</button>
                        </div>
                    </form>
                </div>
                <div class="card-footer text-muted">
                    <small>Last updated: <?= date('Y-m-d H:i', strtotime($competition['updated_at'] ?? $competition['created_at'])) ?></small>
                </div>
            </div>
        </div>
    </div>
</div>