        </div> <!-- end container -->

        <!-- Footer -->
        <footer class="bg-light text-center py-3 border-top">
            <small>&copy; <?= date('Y'); ?> Blog System. All rights reserved.</small>
        </footer>

        <!-- Bootstrap JS -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

        <!-- Live Search Script -->
        <script>
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
