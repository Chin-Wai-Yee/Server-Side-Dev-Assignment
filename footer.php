<footer>
    <p>&copy; Recipe and Culinary Web Application</p>
</footer>

<script>
    //Community Module JavaScript
    /*
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.vote-form').forEach(form => {
            form.addEventListener('submit', async (e) => {
                e.preventDefault();

                const formData = new FormData(form);
                const commentId = form.dataset.commentId;
                const voteValue = formData.get('vote_value');

                try {
                    const response = await fetch('/recipe%20culinary/community/includes/vote.php', {
                        method: 'POST',
                        body: new URLSearchParams({
                            comment_id: commentId,
                            vote_value: voteValue,
                            csrf_token: formData.get('csrf_token')
                        }),
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        }
                    });

                    const data = await response.json();

                    if (!data.success) {
                        alert('Error: ' + (data.error || 'Unknown error'));
                        return;
                    }

                    // Update UI
                    const container = form.closest('.rd-vote-controls');

                    // Update vote count
                    container.querySelector('.rd-vote-count').textContent = data.total_votes;

                    // Update active states
                    const upvoteBtn = container.querySelector('.rd-upvote');
                    const downvoteBtn = container.querySelector('.rd-downvote');

                    upvoteBtn.classList.remove('active');
                    downvoteBtn.classList.remove('active');

                    if (data.user_vote === 1) {
                        upvoteBtn.classList.add('active');
                    } else if (data.user_vote === -1) {
                        downvoteBtn.classList.add('active');
                    }

                } catch (error) {
                    console.error('Voting failed:', error);
                    alert('Voting failed. Please try again.');
                }
            });
        });
    });


    document.querySelectorAll('.rd-reply-form form').forEach(form => {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            const formData = new FormData(form);

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    body: formData
                });

                if (response.redirected) {
                    window.location.href = response.url;
                } else {
                    const result = await response.text();
                    if (!response.ok) throw new Error(result);
                    location.reload(); // Refresh to show new comment
                }
            } catch (error) {
                alert('Error posting reply: ' + error.message);
            }
        });
    });


    document.querySelectorAll('.vote-form[data-discussion-id]').forEach(form => {
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const formData = new FormData(form);
        
        try {
            const response = await fetch('/recipe%20culinary/community/discussions/discussion_vote.php', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: new URLSearchParams({
                    discussion_id: formData.get('discussion_id'),
                    vote_value: formData.get('vote_value'),
                    csrf_token: formData.get('csrf_token')
                })
            });

            // Handle non-JSON responses
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                const text = await response.text();
                throw new Error(`Server returned ${response.status}: ${text}`);
            }

            const data = await response.json();
            
            if (!response.ok || !data.success) {
                throw new Error(data.error || 'Unknown error');
            }

            // Update UI
            const container = form.closest('.rd-vote-controls');
            container.querySelector('.rd-vote-count').textContent = data.total_votes;
            
            const upvoteBtn = container.querySelector('.rd-upvote');
            const downvoteBtn = container.querySelector('.rd-downvote');
            
            upvoteBtn.classList.remove('active');
            downvoteBtn.classList.remove('active');
            
            if (data.user_vote === 1) {
                upvoteBtn.classList.add('active');
            } else if (data.user_vote === -1) {
                downvoteBtn.classList.add('active');
            }

        } catch (error) {
            console.error('Voting failed:', error);
            alert(error.message);
        }
    });
});

*/

/*
    document.querySelectorAll('.vote-form').forEach(form => {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            const formData = new FormData(form);
            const isComment = form.hasAttribute('data-comment-id');

            try {
                // Determine endpoint
                const endpoint = isComment ?
                    '/recipe%20culinary/community/includes/vote.php' :
                    '/recipe%20culinary/community/discussions/discussion_vote.php';

                // Send request
                const response = await fetch(endpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: new URLSearchParams(formData)
                });

                // Handle response
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json();

                if (!data.success) {
                    throw new Error(data.error || 'Vote failed');
                }

                // Update UI
                const container = form.closest('.rd-vote-controls');
                container.querySelector('.rd-vote-count').textContent = data.total_votes;

                const upBtn = container.querySelector('.rd-upvote');
                const downBtn = container.querySelector('.rd-downvote');

                upBtn.classList.toggle('active', data.user_vote === 1);
                downBtn.classList.toggle('active', data.user_vote === -1);

            } catch (error) {
                console.error('Vote error:', error);
                alert(error.message);
            }
        });
    });

    */

    document.querySelectorAll('.vote-form').forEach(form => {
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const formData = new FormData(form);
        const isComment = form.hasAttribute('data-comment-id');
        
        try {
            // Determine endpoint based on form type
            const endpoint = isComment 
                ? '/recipe%20culinary/community/includes/vote.php'
                : '/recipe%20culinary/community/discussions/discussion_vote.php';

            const response = await fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: new URLSearchParams(formData)
            });

            // Handle HTTP errors
            if (!response.ok) {
                const errorText = await response.text();
                throw new Error(`HTTP ${response.status}: ${errorText}`);
            }

            const data = await response.json();
            
            if (!data.success) {
                throw new Error(data.error || 'Vote failed');
            }

            // Update UI
            const container = form.closest('.rd-vote-controls');
            container.querySelector('.rd-vote-count').textContent = data.total_votes;
            
            const upBtn = container.querySelector('.rd-upvote');
            const downBtn = container.querySelector('.rd-downvote');
            
            upBtn.classList.toggle('active', data.user_vote === 1);
            downBtn.classList.toggle('active', data.user_vote === -1);

        } catch (error) {
            console.error('Vote error:', error);
            alert(error.message);
        }
    });
});
</script>