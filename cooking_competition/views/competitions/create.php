<div class="create-competition">
    <h1>Create a New Competition</h1>

    <form method="POST" class="col-md-6 mx-auto">
        <div class="form-floating mb-3">
            <input type="text" class="form-control" id="title" name="title" placeholder="Title" required>
            <label for="title">Title</label>
        </div>

        <div class="form-floating mb-3">
            <textarea class="form-control" id="description" name="description" placeholder="Description" style="height: 100px" required></textarea>
            <label for="description">Description</label>
        </div>

        <div class="row mb-3">
            <div class="col">
                <div class="form-floating">
                    <input type="date" class="form-control" id="start_date" name="start_date" placeholder="Start Date" required>
                    <label for="start_date">Start Date</label>
                </div>
            </div>
            <div class="col">
                <div class="form-floating">
                    <input type="time" class="form-control" id="start_time" name="start_time" placeholder="Start Time" value="00:00" required>
                    <label for="start_time">Start Time</label>
                </div>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col">
                <div class="form-floating">
                    <input type="date" class="form-control" id="end_date" name="end_date" placeholder="Submit Recipe End Date" required>
                    <label for="end_date">Submit Recipe End Date</label>
                </div>
            </div>
            <div class="col">
                <div class="form-floating">
                    <input type="time" class="form-control" id="end_time" name="end_time" placeholder="Submit Recipe End Time" value="23:59" required>
                    <label for="end_time">Submit Recipe End Time</label>
                </div>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col">
                <div class="form-floating">
                    <input type="date" class="form-control" id="voting_end_date" name="voting_end_date" placeholder="Voting End Date" required>
                    <label for="voting_end_date">Voting End Date</label>
                </div>
            </div>
            <div class="col">
                <div class="form-floating">
                    <input type="time" class="form-control" id="voting_end_time" name="voting_end_time" placeholder="Voting End Time" value="23:59" required>
                    <label for="voting_end_time">Voting End Time</label>
                </div>
            </div>
        </div>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <button type="submit" class="btn btn-primary">Create Competition</button>
    </form>
</div>