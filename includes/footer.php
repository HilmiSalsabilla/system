        </div> <!-- end container -->

        <!-- Footer -->
        <footer class="bg-light text-center py-3 border-top">
            <small>&copy; <?= date('Y'); ?> Blog System. All rights reserved.</small>
        </footer>

        <!-- Bootstrap JS -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
        
        <!-- Custom Script -->
        <script>
            // Submit Comment via AJAX
            $(document).ready(function() {
                $('#comment_data').on('submit', function(e) {
                    e.preventDefault();
                    var formdata = $(this).serialize();

                    $.ajax({
                        type: "POST",
                        url: "<?= $baseUrl ?>comment/insert.php",
                        data: formdata,
                        dataType: "json",
                        success: function(response) {
                            if (response.status === "success") {
                                Swal.fire("Success", response.message, "success");
                                $('#comment_data')[0].reset();
                                setTimeout(() => location.reload(), 1000);
                            } else {
                                Swal.fire("Error", response.message, "error");
                            }
                        },
                        error: function() {
                            Swal.fire("Error", "Something went wrong. Please try again.", "error");
                        }
                    });
                });
            });

            // Delete Comment
            $(document).on('click', '.delete-comment', function(e) {
                e.preventDefault();
                let commentId = $(this).data('id');
                let postId = $(this).data('post');

                Swal.fire({
                    title: "Are you sure?",
                    text: "This comment will be deleted permanently.",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonText: "Yes, delete it!",
                    cancelButtonText: "Cancel",
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            type: "POST",
                            url: "<?= $baseUrl ?>comment/delete.php",
                            data: { id: commentId, post_id: postId },
                            dataType: "json",
                            success: function(response) {
                                Swal.fire(response.status, response.message, response.status);
                                if (response.status === "success") {
                                    setTimeout(() => location.reload(), 1000);
                                }
                            },
                            error: function() {
                                Swal.fire("Error", "Something went wrong.", "error");
                            }
                        });
                    }
                });
            });

            // Star Rating
            $(document).ready(function() {
                $(".my-rating").starRating({
                    initialRating: <?= $avgRating ?: 0 ?>,
                    starSize: 30,
                    disableAfterRate: <?= empty($_SESSION['username']) ? 'true' : 'false' ?>,
                    callback: function(currentRating, $el){
                        <?php if (!empty($_SESSION['username'])): ?>
                        $.ajax({
                            type: "POST",
                            url: "<?= $baseUrl ?>ratings/save.php",
                            data: {
                                post_id: <?= $post['id']; ?>,
                                rating: currentRating
                            },
                            dataType: "json",
                            success: function(response) {
                                if (response.status === "success") {
                                    Swal.fire("Thank you!", "Your rating has been saved.", "success");
                                    setTimeout(() => location.reload(), 1000);
                                } else {
                                    Swal.fire("Error", response.message, "error");
                                }
                            }
                        });
                        <?php endif; ?>
                    }
                });
            });

            // Like & Dislike
            $(document).on("click", ".like-btn, .dislike-btn", function() {
                let postId = $(this).data("id");
                let action = $(this).hasClass("like-btn") ? "like" : "dislike";

                $.ajax({
                    type: "POST",
                    url: "<?= $baseUrl ?>likes/save.php",
                    data: { post_id: postId, action: action },
                    dataType: "json",
                    success: function(response) {
                        if (response.status === "success") {
                            $(".like-btn[data-id='"+postId+"'] .like-count").text(response.likes);
                            $(".dislike-btn[data-id='"+postId+"'] .dislike-count").text(response.dislikes);

                            if (action === "like") {
                                $(".like-btn[data-id='"+postId+"']")
                                    .removeClass("btn-outline-success")
                                    .addClass("btn-success");
                                $(".dislike-btn[data-id='"+postId+"']")
                                    .removeClass("btn-danger")
                                    .addClass("btn-outline-danger");
                            } else {
                                $(".dislike-btn[data-id='"+postId+"']")
                                    .removeClass("btn-outline-danger")
                                    .addClass("btn-danger");
                                $(".like-btn[data-id='"+postId+"']")
                                    .removeClass("btn-success")
                                    .addClass("btn-outline-success");
                            }
                        } else {
                            alert(response.message);
                        }
                    },
                    error: function() {
                        alert("Request failed. Please try again.");
                    }
                });
            });

            // Live Search Script
            $(document).ready(function(){
                $("#search").on("keyup", function(){
                    var query = $(this).val();
                    if(query.length > 0){
                        $.ajax({
                            url: "<?= $baseUrl ?>search/search.php",
                            method: "POST",
                            data: {query: query},
                            success: function(data){
                                $("#search-results").html(data);
                                $("#post-list").hide();
                            }
                        });
                    } else {
                        $("#search-results").html("");
                        $("#post-list").show();
                    }
                });
            });
        </script>
    </body>

</html>
