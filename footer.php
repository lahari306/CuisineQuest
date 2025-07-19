        </div> <!-- End of container div -->

        <!-- Footer -->
        <footer class="bg-dark text-white py-4 mt-5">
            <div class="container">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <h5>Delicious Recipe System</h5>
                        <p class="text-muted">Find and discover amazing recipes from around the world using our modern recipe database powered by TheMealDB API.</p>
                    </div>
                    <div class="col-md-4 mb-3">
                        <h5>Quick Links</h5>
                        <ul class="list-unstyled">
                            <li><a href="index.php" class="text-decoration-none text-muted">Home</a></li>
                            <li><a href="categories.php" class="text-decoration-none text-muted">Categories</a></li>
                            <li><a href="areas.php" class="text-decoration-none text-muted">Cuisines</a></li>
                            <li><a href="random.php" class="text-decoration-none text-muted">Random Recipe</a></li>
                        </ul>
                    </div>
                    <div class="col-md-4 mb-3">
                        <h5>Popular Searches</h5>
                        <div id="popular-searches">
                            <!-- Will be filled dynamically with PHP -->
                        </div>
                    </div>
                </div>
                <hr>
                <div class="text-center">
                    <p class="mb-0">&copy; <?php echo date('Y'); ?> Delicious Recipe System. Powered by <a href="https://www.themealdb.com/" class="text-decoration-none text-muted" target="_blank">TheMealDB</a>.</p>
                </div>
            </div>
        </footer>

        <!-- Bootstrap 5 JS with Popper -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
        <!-- jQuery -->
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <!-- Custom JS -->
        <script src="js/script.js"></script>
    </body>
</html> 